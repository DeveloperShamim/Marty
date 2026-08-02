<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StaffActivityLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(): View
    {
        return view('admin.profile.index', [
            'user' => auth()->user(),
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:50'],
        ]);

        $oldEmail = $user->email;
        $user->update($validated);

        StaffActivityLog::create([
            'user_id' => $user->id,
            'staff_name' => $user->name,
            'staff_role' => $user->role ?? 'admin',
            'action' => 'Updated Profile',
            'description' => "Updated account profile details (Email: {$user->email}).",
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('admin.profile.edit')->with('status', 'Profile details updated successfully.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (! Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'The provided current password does not match our records.']);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        StaffActivityLog::create([
            'user_id' => $user->id,
            'staff_name' => $user->name,
            'staff_role' => $user->role ?? 'admin',
            'action' => 'Updated Password',
            'description' => 'Successfully changed account password.',
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('admin.profile.edit')->with('status', 'Account password updated successfully.');
    }
}
