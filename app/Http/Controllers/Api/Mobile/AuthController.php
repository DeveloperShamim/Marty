<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\MobileApiToken;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $data = $request->validate([
            'email'       => ['required', 'email'],
            'password'    => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ]);

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            return response()->json(['message' => 'These credentials do not match our records.'], 422);
        }

        if (! $user->isStaff()) {
            return response()->json(['message' => $user->is_suspended
                ? 'Your staff account has been suspended. Please contact the administrator.'
                : 'This account does not have staff permissions.'], 403);
        }

        $token = MobileApiToken::issue($user, $data['device_name'] ?? 'Mobile app');

        Auth::setUser($user);
        ActivityLogger::log('Staff Login', 'Logged into Admin Mobile App');

        return response()->json([
            'token' => $token,
            'user'  => $this->userPayload($user),
        ]);
    }

    public function me(Request $request)
    {
        return response()->json(['user' => $this->userPayload($request->user())]);
    }

    public function logout(Request $request)
    {
        $request->attributes->get('mobile_token')?->delete();

        return response()->json(['message' => 'Logged out.']);
    }

    private function userPayload(User $user): array
    {
        return [
            'id'            => $user->id,
            'name'          => $user->name,
            'email'         => $user->email,
            'role'          => $user->role,
            'can_manage_orders' => $user->isOrderManager(),
            'site_name'     => site_name(),
            'currency'      => currency_symbol(),
        ];
    }
}
