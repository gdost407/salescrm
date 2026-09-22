<?php

use App\Jobs\PruneWebhookLogs;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;

test('cleanup deletes only logs created more than seven days ago across batches', function () {
    $this->freezeSecond();

    foreach (range(1, 3) as $batch) {
        DB::table('webhook_logs')->insert(array_fill(0, 500, [
            'status' => 'failed',
            'created_at' => now()->subWeek()->subSecond(),
            'updated_at' => now(),
        ]));
    }

    $boundaryId = DB::table('webhook_logs')->insertGetId([
        'created_at' => now()->subWeek(),
        'status' => 'success',
    ]);
    $recentId = DB::table('webhook_logs')->insertGetId([
        'created_at' => now(),
        'received_at' => now()->subWeeks(2),
        'status' => 'processing',
    ]);

    (new PruneWebhookLogs)->handle();

    expect(DB::table('webhook_logs')->pluck('id')->all())->toBe([$boundaryId, $recentId]);

    (new PruneWebhookLogs)->handle();

    $this->assertDatabaseCount('webhook_logs', 2);
});

test('cleanup handles an empty log table', function () {
    (new PruneWebhookLogs)->handle();

    $this->assertDatabaseCount('webhook_logs', 0);
});

test('cleanup is scheduled hourly and prevents duplicate queued jobs', function () {
    Queue::fake();

    $event = collect(app(Schedule::class)->events())
        ->first(fn ($event): bool => $event->description === PruneWebhookLogs::class);

    expect($event)->not->toBeNull()
        ->and($event->expression)->toBe('0 * * * *');

    $event->run(app());
    $event->run(app());

    Queue::assertPushed(PruneWebhookLogs::class, 1);
});
