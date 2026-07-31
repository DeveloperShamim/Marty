<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AbandonedCart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class AbandonedCartController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status', 'all');
        $search = trim((string) $request->input('q'));

        $query = AbandonedCart::with('user')->latest();

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

        $totalCartsCount    = AbandonedCart::count();
        $abandonedCount     = AbandonedCart::abandoned()->count();
        $reminderSentCount  = AbandonedCart::reminderSent()->count();
        $recoveredCount     = AbandonedCart::recovered()->count();

        $potentialRevenue   = (float) AbandonedCart::where('status', '!=', 'recovered')->sum('total');
        $recoveredRevenue   = (float) AbandonedCart::recovered()->sum('total');

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

    public function destroy(AbandonedCart $cart)
    {
        $cart->delete();

        return back()->with('status', 'Abandoned cart record deleted.');
    }
}
