<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Blacklist;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CustomerController extends Controller
{
    public static function getTagsMap(): array
    {
        $raw = Setting::get('customer_segment_tags', '{}');
        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : [];
    }

    public static function setTagsMap(array $map): void
    {
        Setting::put('customer_segment_tags', json_encode($map));
    }

    public function index(Request $request)
    {
        $tab = $request->input('tab', 'all');
        $sort = $request->input('sort', 'latest');
        $term = trim((string) $request->input('q'));

        // Load Admin Customer Tags Map
        $tagsMap = static::getTagsMap();

        // Base Query grouped by Phone Number
        $query = Order::query()
            ->select('customer_phone')
            ->selectRaw('MAX(customer_name) as customer_name')
            ->selectRaw('MAX(customer_email) as customer_email')
            ->selectRaw('MAX(city) as city')
            ->selectRaw('MAX(shipping_address) as shipping_address')
            ->selectRaw('COUNT(*) as orders_count')
            ->selectRaw('SUM(CASE WHEN status = "delivered" THEN 1 ELSE 0 END) as delivered_count')
            ->selectRaw('SUM(CASE WHEN status = "cancelled" THEN 1 ELSE 0 END) as cancelled_count')
            ->selectRaw('SUM(total) as total_spent')
            ->selectRaw('MIN(created_at) as first_order_at')
            ->selectRaw('MAX(created_at) as last_order_at')
            ->whereNotNull('customer_phone')
            ->where('customer_phone', '!=', '')
            ->groupBy('customer_phone');

        // Global Search
        if ($term !== '') {
            $query->where(function ($q) use ($term) {
                $q->where('customer_name', 'like', "%{$term}%")
                    ->orWhere('customer_phone', 'like', "%{$term}%")
                    ->orWhere('customer_email', 'like', "%{$term}%")
                    ->orWhere('city', 'like', "%{$term}%")
                    ->orWhere('shipping_address', 'like', "%{$term}%");
            });
        }

        // Fetch Blacklisted Phone numbers array for fast lookup
        $blacklistedPhones = Blacklist::where('type', 'phone')->pluck('value')->map(fn($v) => strtolower(trim($v)))->toArray();

        // Segmentation Tab Filtering
        if ($tab === 'vip') {
            // Filter phones explicitly tagged VIP by admin
            $vipPhones = array_keys(array_filter($tagsMap, fn($t) => $t === 'VIP'));
            $query->whereIn('customer_phone', $vipPhones);
        } elseif ($tab === 'repeat') {
            $query->havingRaw('orders_count >= 2');
        } elseif ($tab === 'new') {
            $query->havingRaw('orders_count = 1');
        } elseif ($tab === 'blacklisted') {
            $query->whereIn('customer_phone', $blacklistedPhones);
        }

        // Sorting
        match ($sort) {
            'spent_desc'  => $query->orderByDesc('total_spent'),
            'orders_desc' => $query->orderByDesc('orders_count'),
            'name_asc'    => $query->orderBy('customer_name'),
            default       => $query->orderByDesc('last_order_at'),
        };

        $customers = $query->paginate(20)->withQueryString();

        // Attach dynamic attributes & determine active segment tag
        $customers->getCollection()->transform(function ($c) use ($blacklistedPhones, $tagsMap) {
            $phoneClean = strtolower(trim($c->customer_phone));
            $c->is_blacklisted = in_array($phoneClean, $blacklistedPhones, true);
            $c->avg_order_value = $c->orders_count > 0 ? ($c->total_spent / $c->orders_count) : 0;
            
            // Admin Selected Tag vs Auto Tag
            $c->admin_tag = $tagsMap[$phoneClean] ?? null;
            if (!empty($c->admin_tag) && $c->admin_tag !== 'auto') {
                $c->tag = $c->admin_tag;
                $c->is_manual_tag = true;
            } else {
                $c->tag = $c->orders_count >= 2 ? 'Repeat Buyer' : 'New Customer';
                $c->is_manual_tag = false;
            }

            return $c;
        });

        // Overview Ribbon Metrics
        $totalCustomersCount = Order::whereNotNull('customer_phone')->where('customer_phone', '!=', '')->distinct('customer_phone')->count('customer_phone');
        $totalLifetimeRevenue = (float) Order::where('status', '!=', 'cancelled')->sum('total');
        $repeatCustomersCount = Order::select('customer_phone')->whereNotNull('customer_phone')->where('customer_phone', '!=', '')->groupBy('customer_phone')->havingRaw('COUNT(*) >= 2')->get()->count();
        $vipCount = count(array_filter($tagsMap, fn($t) => $t === 'VIP'));

        return view('admin.customers.index', compact(
            'customers',
            'tab',
            'sort',
            'term',
            'totalCustomersCount',
            'totalLifetimeRevenue',
            'repeatCustomersCount',
            'vipCount',
            'blacklistedPhones',
            'tagsMap'
        ));
    }

    public function show(string $phone)
    {
        $orders = Order::with('items.product')
            ->where('customer_phone', $phone)
            ->latest()
            ->get();

        abort_if($orders->isEmpty(), 404);

        $customer = $orders->first();
        $totalSpent = (float) $orders->sum('total');
        $ordersCount = $orders->count();
        $deliveredCount = $orders->where('status', 'delivered')->count();
        $cancelledCount = $orders->where('status', 'cancelled')->count();
        $avgOrderValue = $ordersCount > 0 ? ($totalSpent / $ordersCount) : 0;
        $successRate = $ordersCount > 0 ? round(($deliveredCount / $ordersCount) * 100) : 0;

        $phoneClean = strtolower(trim($phone));
        $isBlacklisted = Blacklist::isBlacklisted('phone', $phone);

        $tagsMap = static::getTagsMap();
        $adminTag = $tagsMap[$phoneClean] ?? null;

        if (!empty($adminTag) && $adminTag !== 'auto') {
            $tag = $adminTag;
            $isManualTag = true;
        } else {
            $tag = $ordersCount >= 2 ? 'Repeat Buyer' : 'New Customer';
            $isManualTag = false;
        }

        // Top purchased items by this customer
        $orderIds = $orders->pluck('id')->toArray();
        $topPurchasedItems = OrderItem::query()
            ->select('product_name', 'image', DB::raw('SUM(quantity) as units_bought'), DB::raw('SUM(line_total) as spent'))
            ->whereIn('order_id', $orderIds)
            ->groupBy('product_name', 'image')
            ->orderByDesc('units_bought')
            ->take(5)
            ->get();

        return view('admin.customers.show', compact(
            'orders',
            'customer',
            'phone',
            'totalSpent',
            'ordersCount',
            'deliveredCount',
            'cancelledCount',
            'avgOrderValue',
            'successRate',
            'isBlacklisted',
            'adminTag',
            'tag',
            'isManualTag',
            'topPurchasedItems'
        ));
    }

    public function updateSegmentTag(Request $request, string $phone)
    {
        $data = $request->validate([
            'segment_tag' => ['required', 'string', 'in:auto,VIP,Wholesale,Loyal,Risk,Influencer'],
        ]);

        $cleanPhone = strtolower(trim($phone));
        $tagsMap = static::getTagsMap();

        if ($data['segment_tag'] === 'auto') {
            unset($tagsMap[$cleanPhone]);
        } else {
            $tagsMap[$cleanPhone] = $data['segment_tag'];
        }

        static::setTagsMap($tagsMap);

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Segment tag updated successfully.',
                'tag' => $data['segment_tag'],
            ]);
        }

        return back()->with('status', "Segment tag for customer {$phone} updated successfully.");
    }

    public function toggleBlacklist(Request $request, string $phone)
    {
        $cleanPhone = strtolower(trim($phone));
        $existing = Blacklist::where('type', 'phone')->where('value', $cleanPhone)->first();

        if ($existing) {
            $existing->delete();
            return back()->with('status', "Phone number {$phone} unblocked and removed from Blacklist.");
        }

        Blacklist::create([
            'type'   => 'phone',
            'value'  => $cleanPhone,
            'reason' => 'Manually blacklisted from Customer CRM',
        ]);

        return back()->with('status', "Phone number {$phone} added to Fraud Blacklist.");
    }

    public function export(Request $request): StreamedResponse
    {
        $fileName = 'customers_export_' . date('Y-m-d_His') . '.csv';

        $customers = Order::query()
            ->select('customer_phone')
            ->selectRaw('MAX(customer_name) as customer_name')
            ->selectRaw('MAX(customer_email) as customer_email')
            ->selectRaw('MAX(city) as city')
            ->selectRaw('MAX(shipping_address) as shipping_address')
            ->selectRaw('COUNT(*) as orders_count')
            ->selectRaw('SUM(total) as total_spent')
            ->selectRaw('MIN(created_at) as first_order_at')
            ->selectRaw('MAX(created_at) as last_order_at')
            ->whereNotNull('customer_phone')
            ->where('customer_phone', '!=', '')
            ->groupBy('customer_phone')
            ->orderByDesc('last_order_at')
            ->get();

        $blacklistedPhones = Blacklist::where('type', 'phone')->pluck('value')->map(fn($v) => strtolower(trim($v)))->toArray();
        $tagsMap = static::getTagsMap();

        return response()->streamDownload(function () use ($customers, $blacklistedPhones, $tagsMap) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Customer Name',
                'Phone Number',
                'Email Address',
                'City / District',
                'Shipping Address',
                'Total Orders',
                'Total Spent (BDT)',
                'First Order Date',
                'Last Order Date',
                'Segment Tag',
                'Blacklist Status',
            ]);

            foreach ($customers as $c) {
                $phoneClean = strtolower(trim($c->customer_phone));
                $isBlacklisted = in_array($phoneClean, $blacklistedPhones, true);
                
                $adminTag = $tagsMap[$phoneClean] ?? null;
                if (!empty($adminTag) && $adminTag !== 'auto') {
                    $tag = $adminTag . ' (Admin Selected)';
                } else {
                    $tag = $c->orders_count >= 2 ? 'Repeat Buyer (Auto)' : 'New Customer (Auto)';
                }

                fputcsv($handle, [
                    $c->customer_name ?: 'N/A',
                    $c->customer_phone,
                    $c->customer_email ?: '',
                    $c->city ?: '',
                    $c->shipping_address ?: '',
                    $c->orders_count,
                    number_format((float) $c->total_spent, 2, '.', ''),
                    Carbon::parse($c->first_order_at)->format('Y-m-d'),
                    Carbon::parse($c->last_order_at)->format('Y-m-d'),
                    $tag,
                    $isBlacklisted ? 'Blacklisted' : 'Active',
                ]);
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ]);
    }
}
