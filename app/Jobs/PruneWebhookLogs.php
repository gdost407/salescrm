<?php

namespace App\Jobs;

use App\Models\WebhookLog;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Throwable;

class PruneWebhookLogs implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 60;

    /** @var array<int, int> */
    public array $backoff = [10, 30, 60];

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $cutoff = now()->subWeek();

        WebhookLog::query()
            ->select('id')
            ->where('created_at', '<', $cutoff)
            ->toBase()
            ->chunkById(1000, function (Collection $logs) use ($cutoff): void {
                WebhookLog::query()
                    ->whereIn('id', $logs->pluck('id'))
                    ->where('created_at', '<', $cutoff)
                    ->delete();
            });
    }

    public function failed(?Throwable $exception): void
    {
        Log::error('Webhook log cleanup failed.', ['exception' => $exception]);
    }
}
