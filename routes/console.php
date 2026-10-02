<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Daily clean-up: old/recovered abandoned carts and audit-log entries older than a year (see each model's prunable()).
Schedule::command('model:prune')->daily();

// BD Courier per-key search counters are only needed for today; keep 90 days for reference.
Schedule::call(fn () => \Illuminate\Support\Facades\DB::table('bdcourier_key_usage')->where('day', '<', now()->subDays(90)->toDateString())->delete())
    ->daily()
    ->name('prune-bdcourier-key-usage');


// Courier delivery statuses (Steadfast / Pathao / RedX). Needs the server cron:
//   * * * * * cd /path/to/site && php artisan schedule:run >> /dev/null 2>&1
// Once a day at 9 PM (Dhaka time), after most of the day's deliveries are done.
Schedule::command('couriers:sync', ['--limit' => 1000])
    ->dailyAt('21:00')
    ->withoutOverlapping()
    ->when(fn () => setting('courier_auto_sync', '1') === '1');

// Live chat messages and attachments older than 90 days (stated in the privacy policy).
Schedule::command('chat:prune --days=90')->daily();

// Lets `php artisan app:launch-check` confirm the server's cron job is running.
// A plain timestamp: the cache refuses to unserialize objects such as Carbon (cache.serializable_classes = false).
Schedule::call(fn () => \Illuminate\Support\Facades\Cache::put('scheduler_heartbeat', now()->getTimestamp(), now()->addHour()))
    ->everyMinute()
    ->name('scheduler-heartbeat');
