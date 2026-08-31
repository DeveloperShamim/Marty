<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AbandonedCart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductSku;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AbandonedCartController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status', 'all');
        $search = trim((string) $request->input('q'));

        // Feature: Only count and display abandoned carts where the customer provided a mobile phone number
        $query = AbandonedCart::with('user')
            ->whereNotNull('customer_phone')
            ->where('customer_phone', '!=', '')
            ->latest();

        if ($status !== 'all' && in_array($status, ['abandoned', 'reminder_sent', 'recovered'], true)) {
            $query->where('status', $status);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_phone', 'like', "%{$search}%")
                  ->orWhere('customer_email', 'like', "%{$search}%");
            });
        }

        $carts = $query->paginate(15)->withQueryString();

        // Performance Optimization: Batch preload blacklist and cancelled order counts to prevent N+1 queries (reduces ~45 queries to 2)
        $phones = $carts->getCollection()->pluck('customer_phone')->filter()->unique()->values()->all();
        $emails = $carts->getCollection()->pluck('customer_email')->filter()->unique()->map(fn($e) => strtolower(trim($e)))->values()->all();

        $blacklistedPhones = !empty($phones) 
            ? \App\Models\Blacklist::where('type', 'phone')->whereIn('value', array_map('strtolower', $phones))->pluck('value')->all()
            : [];
        $blacklistedEmails = !empty($emails)
            ? \App\Models\Blacklist::where('type', 'email')->whereIn('value', $emails)->pluck('value')->all()
            : [];

        $cancelledCounts = !empty($phones)
            ? \App\Models\Order::whereIn('customer_phone', $phones)
                ->where('status', 'cancelled')
                ->selectRaw('customer_phone, count(*) as total')
                ->groupBy('customer_phone')
                ->pluck('total', 'customer_phone')
                ->all()
            : [];

        foreach ($carts as $cart) {
            $isPhoneBlocked = $cart->customer_phone && in_array(strtolower(trim($cart->customer_phone)), $blacklistedPhones, true);
            $isEmailBlocked = $cart->customer_email && in_array(strtolower(trim($cart->customer_email)), $blacklistedEmails, true);
            $cart->setRelation('is_blacklisted_cached', $isPhoneBlocked || $isEmailBlocked);
            $cart->setRelation('cancelled_orders_count_cached', $cancelledCounts[$cart->customer_phone] ?? 0);
        }

        $phoneCartsQuery    = AbandonedCart::whereNotNull('customer_phone')->where('customer_phone', '!=', '');
        $totalCartsCount    = (clone $phoneCartsQuery)->count();
        $abandonedCount     = (clone $phoneCartsQuery)->where('status', 'abandoned')->count();
        $reminderSentCount  = (clone $phoneCartsQuery)->where('status', 'reminder_sent')->count();
        $recoveredCount     = (clone $phoneCartsQuery)->where('status', 'recovered')->count();

        $potentialRevenue   = (float) (clone $phoneCartsQuery)->where('status', '!=', 'recovered')->sum('total');
        $recoveredRevenue   = (float) (clone $phoneCartsQuery)->where('status', 'recovered')->sum('total');

        $recoveryRate = $totalCartsCount > 0 ? round(($recoveredCount / $totalCartsCount) * 100, 1) : 0;

        return view('admin.abandoned-carts.index', [
            'carts'              => $carts,
            'status'             => $status,
            'search'             => $search,
            'totalCartsCount'    => $totalCartsCount,
            'abandonedCount'     => $abandonedCount,
            'reminderSentCount'  => $reminderSentCount,
            'recoveredCount'     => $recoveredCount,
            'potentialRevenue'   => $potentialRevenue,
            'recoveredRevenue'   => $recoveredRevenue,
            'recoveryRate'       => $recoveryRate,
        ]);
    }

    public function sendReminder(AbandonedCart $cart)
    {
        if (! $cart->customer_email) {
            return back()->with('error', 'Customer email is missing for this cart record.');
        }

        try {
            configure_mail_from_settings();
            $link = $cart->recoveryUrl();
            $customerName = $cart->customer_name ?: 'Valued Customer';
            $site = site_name();

            Mail::raw(
                "Hello {$customerName},\n\nYou left items in your shopping cart at {$site}.\n\nClick the link below to complete your order in 1-click:\n{$link}\n\nThank you for shopping with {$site}!",
                function ($m) use ($cart, $site) {
                    $m->to($cart->customer_email)
                      ->subject("You left items in your cart — 1-Click Complete Order at {$site}");
                }
            );

            $cart->update([
                'status'           => 'reminder_sent',
                'reminder_sent_at' => now(),
            ]);

            return back()->with('status', "Reminder email sent successfully to {$cart->customer_email}!");
        } catch (\Throwable $e) {
            return back()->with('error', 'Failed to send email reminder: ' . $e->getMessage());
        }
    }

    public function markRecovered(AbandonedCart $cart)
    {
        $cart->update([
            'status'       => 'recovered',
            'recovered_at' => now(),
        ]);

        return back()->with('status', 'Cart record manually marked as recovered.');
    }

    public function pruneRecovered(Request $request)
    {
        $days = (int) $request->input('days', 0);
        $query = AbandonedCart::where('status', 'recovered');
        if ($days > 0) {
            $query->where('recovered_at', '<=', now()->subDays($days));
        }
        $count = $query->delete();

        return back()->with('status', "Cleaned {$count} recovered cart record(s) from database!");
    }

    public function pruneOld(Request $request)
    {
        $days = max(1, (int) $request->input('days', 30));
        $count = AbandonedCart::where('created_at', '<=', now()->subDays($days))->delete();

        return back()->with('status', "Cleaned {$count} abandoned cart record(s) older than {$days} days!");
    }

    public function destroy(AbandonedCart $cart)
    {
        $cart->delete();

        return back()->with('status', 'Abandoned cart record deleted.');
    }
}
