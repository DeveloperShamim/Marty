<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StaffActivityLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('q'));

        $query = StaffActivityLog::with('user')->latest();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('staff_name', 'like', "%{$search}%")
                  ->orWhere('action', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%");
            });
        }

        $logs = $query->paginate(20)->withQueryString();

        return view('admin.activity-logs.index', compact('logs', 'search'));
    }

    public function clearLogs(Request $request): RedirectResponse
    {
        StaffActivityLog::query()->delete();

        // Create new log entry recording the clear action
        $user = auth()->user();
        StaffActivityLog::create([
            'user_id' => $user->id,
            'staff_name' => $user->name,
            'staff_role' => $user->role ?? 'admin',
            'action' => 'Cleared Audit Logs',
            'description' => 'Super Admin cleared all historical staff activity audit log records.',
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('admin.activity-logs.index')->with('status', 'All staff activity audit logs cleared successfully.');
    }
}
