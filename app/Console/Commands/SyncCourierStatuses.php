<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\Setting;
use App\Services\Courier\CourierStatusUpdater;
use Illuminate\Console\Command;

/** Asks Steadfast / Pathao / RedX for the status of every parcel still out for delivery. */
class SyncCourierStatuses extends Command
{
    protected $signature = 'couriers:sync
        {--order= : Only this order number}
        {--limit=200 : Most parcels to check in one run}';

    protected $description = 'Update shipped orders from the courier APIs (delivered, returning, on hold...)';

    public function handle(CourierStatusUpdater $updater): int
    {
        $query = Order::query()
            ->whereIn('courier_name', CourierStatusUpdater::PROVIDERS)
            ->whereNotNull('courier_tracking_code');

        if ($number = $this->option('order')) {
            $query->where('order_number', $number);
        } else {
            $query->where('status', 'shipped')
                ->where('courier_sent_at', '>=', now()->subDays(45))
                ->where(fn ($q) => $q->whereNull('courier_status')->orWhereNotIn('courier_status', ['returned', 'cancelled']))
                ->where(fn ($q) => $q->whereNull('courier_synced_at')->orWhere('courier_synced_at', '<', now()->subMinutes(20)))
                ->orderByRaw('courier_synced_at IS NOT NULL')->orderBy('courier_synced_at')
                ->limit((int) $this->option('limit'));
        }

        $counts = ['checked' => 0, 'delivered' => 0, 'attention' => 0, 'failed' => 0];
        foreach ($query->get() as $order) {
            $counts['checked']++;
            $result = $updater->refresh($order);
            if (! $result['success']) {
                $counts['failed']++;
                $this->warn("{$order->order_number}: {$result['message']}");
                continue;
            }
            if ($result['status'] === 'delivered') {
                $counts['delivered']++;
            } elseif (in_array($result['status'], CourierStatusUpdater::ATTENTION, true)) {
                $counts['attention']++;
            }
            $this->line("{$order->order_number}: {$result['message']}");
            usleep(150_000); // be gentle with the courier APIs
        }

        Setting::put('courier_last_sync', json_encode($counts + ['at' => now()->toIso8601String()]));
        $this->info("Checked {$counts['checked']} parcels: {$counts['delivered']} delivered, {$counts['attention']} need attention, {$counts['failed']} failed.");

        return self::SUCCESS;
    }
}
