<?php

namespace App\Http\Controllers;

use App\Models\AbandonedCart;
use Illuminate\Http\Request;

class CartRecoveryController extends Controller
{
    public function recover(Request $request, string $token)
    {
        $abandonedCart = AbandonedCart::where('recovery_token', $token)->first();

        if (! $abandonedCart) {
            return redirect()->route('cart.index')->with('error', 'Invalid or expired cart recovery link.');
        }

        $cartData = $abandonedCart->cart_data;
        if (! is_array($cartData) || empty($cartData)) {
            return redirect()->route('cart.index')->with('error', 'Unable to restore empty cart.');
        }

        // Restore items to current session cart
        session(['cart' => $cartData]);

        // Link the abandoned cart to current session so when order is placed, it gets marked as recovered
        $abandonedCart->update([
            'session_id' => session()->getId(),
        ]);

        return redirect()->route('checkout.show')->with('status', '🎉 Welcome back! Your cart has been restored. Complete your order below.');
    }
}
