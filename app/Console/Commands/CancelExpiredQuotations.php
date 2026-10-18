<?php

namespace App\Console\Commands;

use App\Models\TxnQuotation;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

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

        $cancelledCount = TxnQuotation::query()
            ->whereIn('status', ['draft', 'sent'])
            ->whereDoesntHave('jobs')
            ->where(function (Builder $query) use ($today, $legacyExpiryDate): void {
                $query->whereDate('valid_until', '<', $today)
                    ->orWhere(function (Builder $legacyQuery) use ($legacyExpiryDate): void {
                        $legacyQuery->whereNull('valid_until')
                            ->whereDate('quotation_date', '<', $legacyExpiryDate);
                    });
            })
            ->update(['status' => 'cancelled', 'updated_at' => now()]);

        $this->info("Cancelled {$cancelledCount} expired pending quotation(s).");

        return self::SUCCESS;
    }
}
