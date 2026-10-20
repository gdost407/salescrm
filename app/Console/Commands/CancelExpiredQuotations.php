<?php

namespace App\Console\Commands;

use App\Models\TxnQuotation;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class CancelExpiredQuotations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:cancel-expired-quotations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cancel pending quotations after their validity date';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $today = today();
        $legacyExpiryDate = today()->subDays(2);

        $cancelledCount = 0;

        TxnQuotation::query()
            ->whereIn('status', ['draft', 'sent'])
            ->whereDoesntHave('jobs')
            ->where(function (Builder $query) use ($today, $legacyExpiryDate): void {
                $query->whereDate('valid_until', '<', $today)
                    ->orWhere(function (Builder $legacyQuery) use ($legacyExpiryDate): void {
                        $legacyQuery->whereNull('valid_until')
                            ->whereDate('quotation_date', '<', $legacyExpiryDate);
                    });
            })
            ->select('id')
            ->chunkById(100, function ($quotations) use ($today, $legacyExpiryDate, &$cancelledCount): void {
                foreach ($quotations as $quotation) {
                    $wasCancelled = DB::transaction(function () use ($quotation, $today, $legacyExpiryDate): bool {
                        $lockedQuotation = TxnQuotation::query()->lockForUpdate()->find($quotation->id);
                        if (! $lockedQuotation || ! in_array($lockedQuotation->status, ['draft', 'sent'], true) || $lockedQuotation->jobs()->exists()) {
                            return false;
                        }

                        $isExpired = $lockedQuotation->valid_until
                            ? $lockedQuotation->valid_until->lt($today)
                            : $lockedQuotation->quotation_date->lt($legacyExpiryDate);
                        if (! $isExpired) {
                            return false;
                        }

                        $lockedQuotation->appendActivity([
                            'action' => 'status_changed',
                            'user_id' => null,
                            'user_name' => 'System',
                            'occurred_at' => now()->toIso8601String(),
                            'summary' => 'Quotation expired and was cancelled',
                            'changes' => ['status' => [
                                'label' => 'Status',
                                'from' => $lockedQuotation->status,
                                'to' => 'cancelled',
                            ]],
                        ]);
                        $lockedQuotation->status = 'cancelled';
                        $lockedQuotation->save();

                        return true;
                    });

                    $cancelledCount += (int) $wasCancelled;
                }
            });

        $this->info("Cancelled {$cancelledCount} expired pending quotation(s).");

        return self::SUCCESS;
    }
}
