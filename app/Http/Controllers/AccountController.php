<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AccountController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $orders = Order::query()
            ->with(['items.product'])
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id);
                if ($user->phone) {
                    $q->orWhere('customer_phone', $user->phone);
                }
            })
            ->latest()
            ->get();

        return view('storefront.account.index', compact('user', 'orders'));
    }

    public function showOrder(Order $order)
    {
        $this->authorizeOrder($order);
        $order->load(['items.product']);

        return view('storefront.account.order', compact('order'));
    }

    private function authorizeOrder(Order $order): void
    {
        $user = Auth::user();

        $owns = $order->user_id === $user->id
            || ($user->phone && $order->customer_phone === $user->phone);

        abort_unless($owns, 403);
    }

    public function updateProfile(Request $request)
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:120'],
            'phone'       => ['nullable', 'string', 'max:40'],
            'address'     => ['nullable', 'string', 'max:255'],
            'city'        => ['nullable', 'string', 'max:80'],
            'postal_code' => ['nullable', 'string', 'max:20'],
        ]);

        Auth::user()->update($data);

        return back()->with('status', 'Profile updated.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required'],
            'password'         => ['required', 'confirmed', 'min:8'],
        ]);

        if (! Hash::check($request->input('current_password'), Auth::user()->password)) {
            return back()->withErrors(['current_password' => 'Your current password is incorrect.']);
        }

        Auth::user()->update(['password' => $request->input('password')]);

        return back()->with('status', 'Password changed.');
    }
}
