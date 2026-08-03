<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class TrackOrderController extends Controller
{
    public function show(Request $request)
    {
        $order = null;
        $orderQuery = trim((string) ($request->query('order_number') ?: ($request->query('order_id') ?: $request->query('order', ''))));
        $token = trim((string) $request->query('token', ''));

        if ($orderQuery !== '') {
            $foundOrder = Order::with('items')
                ->where('order_number', $orderQuery)
                ->orWhere('id', $orderQuery)
                ->first();

            if ($foundOrder) {
                // Security verification: Token match OR authenticated owner match
                $tokenValid = $token !== '' && hash_equals($foundOrder->secureTrackingToken(), $token);
                $isOwner = auth()->check() && auth()->id() === $foundOrder->user_id;

                if ($tokenValid || $isOwner) {
                    $order = $foundOrder;
                } else {
                    return redirect()->route('track')
                        ->withInput(['order_number' => $foundOrder->order_number])
                        ->withErrors(['order_number' => 'Security verification required: Please enter the phone number used at checkout to view order details.']);
                }
            }
        }

        return view('storefront.track', ['order' => $order]);
    }

    public function find(Request $request)
    {
        $data = $request->validate([
            'order_number' => ['required', 'string', 'max:40'],
            'phone'        => ['required', 'string', 'max:40'],
        ]);

        $orderNumber = trim($data['order_number']);
        $rawPhone = trim($data['phone']);

        // Strip non-digits from input
        $inputDigits = preg_replace('/\D+/', '', $rawPhone);

        $order = Order::with('items')
            ->where('order_number', $orderNumber)
            ->first();

        $matched = false;
        if ($order && $order->customer_phone) {
            $dbDigits = preg_replace('/\D+/', '', $order->customer_phone);

            if ($dbDigits === $inputDigits) {
                $matched = true;
            } elseif ($inputDigits !== '' && $dbDigits !== '') {
                // Match last 10 digits to ignore country codes (+880/880), hyphens (-), spaces, or leading zeros
                $dbSuffix = strlen($dbDigits) >= 10 ? substr($dbDigits, -10) : $dbDigits;
                $inputSuffix = strlen($inputDigits) >= 10 ? substr($inputDigits, -10) : $inputDigits;

                if ($dbSuffix === $inputSuffix) {
                    $matched = true;
                }
            }
        }

        if (! $order || ! $matched) {
            return back()->withErrors(['order_number' => 'No order found with that order number and phone number.'])->withInput();
        }

        return view('storefront.track', compact('order'));
    }
}
