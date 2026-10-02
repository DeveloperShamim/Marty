<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Services\ActivityLogger;
use App\Services\FreeDelivery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** Marketing → Free Delivery: the offer rules, the free-delivery products and what the offer costs. */
class FreeDeliveryController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->input('q'));

        $freeProducts = Product::with('images')->where('free_delivery', true)->orderBy('name')->get();
        $available = $q === '' ? collect() : Product::with('images')
            ->where('free_delivery', false)
            ->where(fn ($w) => $w->where('name', 'like', "%{$q}%")->orWhere('sku', 'like', "%{$q}%"))
            ->orderBy('name')->limit(20)->get();

        // This month's free-delivery orders that count towards profit (same filter as Profit & Analytics).
        $stats = Order::where('shipping_waived', '>', 0)
            ->where('created_at', '>=', now()->startOfMonth())
            ->whereNotIn('status', ['cancelled', 'returned'])
            ->where(fn ($w) => $w->where('payment_status', 'verified')->orWhere('status', 'delivered'))
            ->select('free_delivery_reason', DB::raw('COUNT(*) as orders'), DB::raw('SUM(shipping_waived) as cost'))
            ->groupBy('free_delivery_reason')
            ->get()
            ->keyBy('free_delivery_reason');

        return view('admin.free-delivery.index', [
            'config'       => FreeDelivery::config(),
            'freeProducts' => $freeProducts,
            'available'    => $available,
            'q'            => $q,
            'stats'        => $stats,
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'free_delivery_online_min'   => ['required', 'numeric', 'min:0', 'max:1000000'],
            'free_delivery_online_zones' => ['required', 'in:' . implode(',', FreeDelivery::ZONES)],
            'free_delivery_over_amount'  => ['nullable', 'numeric', 'min:0', 'max:10000000'],
        ]);

        Setting::put('free_delivery_online', $request->boolean('free_delivery_online') ? '1' : '0');
        Setting::put('free_delivery_online_min', (string) (float) $data['free_delivery_online_min']);
        Setting::put('free_delivery_online_zones', $data['free_delivery_online_zones']);
        Setting::put('free_delivery_over_amount', (string) (float) ($data['free_delivery_over_amount'] ?? 0));
        Setting::forgetCache();

        ActivityLogger::log('Free Delivery Updated', 'Changed the free delivery offer settings.');

        return back()->with('status', 'Free delivery settings saved.');
    }

    public function toggle(Request $request, Product $product)
    {
        $on = $request->boolean('free_delivery');
        $product->forceFill(['free_delivery' => $on])->save();

        ActivityLogger::log('Free Delivery Product', ($on ? 'Added' : 'Removed') . " free delivery for \"{$product->name}\".");

        return back()->with('status', $on ? "\"{$product->name}\" now ships free." : "Free delivery removed from \"{$product->name}\".");
    }
}
