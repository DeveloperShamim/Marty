<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class StaffController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('q'));
        $role = $request->input('role');

        $query = User::whereIn('role', ['admin', 'store_manager', 'order_manager', 'inventory_manager'])->latest();

        if ($role && in_array($role, ['admin', 'store_manager', 'order_manager', 'inventory_manager'], true)) {
            $query->where('role', $role);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $staffMembers = $query->paginate(15)->withQueryString();

        $counts = [
            'all'                => User::whereIn('role', ['admin', 'store_manager', 'order_manager', 'inventory_manager'])->count(),
            'admin'              => User::where('role', 'admin')->count(),
            'store_manager'      => User::where('role', 'store_manager')->count(),
            'order_manager'      => User::where('role', 'order_manager')->count(),
            'inventory_manager'  => User::where('role', 'inventory_manager')->count(),
            'suspended'          => User::whereIn('role', ['admin', 'store_manager', 'order_manager', 'inventory_manager'])->where('is_suspended', true)->count(),
        ];

        return view('admin.staff.index', compact('staffMembers', 'counts', 'search', 'role'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'phone'    => 'nullable|string|max:25',
            'role'     => ['required', Rule::in(['admin', 'store_manager', 'order_manager', 'inventory_manager'])],
            'password' => 'required|string|min:6',
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'phone'    => $validated['phone'] ?? null,
            'role'     => $validated['role'],
            'password' => Hash::make($validated['password']),
        ]);

        ActivityLogger::log(
            'Created Staff Member',
            "Created staff account for '{$user->name}' ({$user->email}) assigned as role [" . ucfirst(str_replace('_', ' ', $user->role)) . "]"
        );

        return redirect()->route('admin.staff.index')->with('status', "Staff account for {$user->name} created successfully!");
    }

    public function toggleStatus(User $staff)
    {
        // Protect main admin from being suspended
        if ($staff->email === 'admin@freshkart.test' || $staff->id === auth()->id()) {
            return back()->withErrors(['staff' => 'You cannot suspend your own account or the master admin.']);
        }

        $staff->is_suspended = !$staff->is_suspended;
        $staff->save();

        $statusLabel = $staff->is_suspended ? 'suspended' : 'activated';
        
        ActivityLogger::log(
            "{$statusLabel} Staff Member",
            "Staff account '{$staff->name}' ({$staff->email}) was {$statusLabel}"
        );

        return back()->with('status', "Staff account for {$staff->name} has been {$statusLabel}.");
    }

    public function destroy(User $staff)
    {
        if ($staff->email === 'admin@freshkart.test' || $staff->id === auth()->id()) {
            return back()->withErrors(['staff' => 'You cannot delete your own account or the master admin.']);
        }

        $name = $staff->name;
        $email = $staff->email;
        $staff->delete();

        ActivityLogger::log(
            'Deleted Staff Member',
            "Deleted staff account '{$name}' ({$email})"
        );

        return redirect()->route('admin.staff.index')->with('status', "Staff member {$name} deleted.");
    }
}
