<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class TrackOrderController extends Controller
{
    public function show()
    {
        return view('storefront.track', ['order' => null]);
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
