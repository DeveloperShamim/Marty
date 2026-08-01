<?php

namespace App\Services;

use App\Models\StaffActivityLog;
use Illuminate\Support\Facades\Auth;

class ActivityLogger
{
    public static function log(string $action, ?string $description = null): void
    {
        try {
            $user = Auth::user();
            
            StaffActivityLog::create([
                'user_id'     => $user?->id,
                'staff_name'  => $user?->name ?? 'System',
                'staff_role'  => $user?->role ?? 'admin',
                'action'      => $action,
                'description' => $description,
                'ip_address'  => request()->ip(),
            ]);
        } catch (\Exception $e) {
            // Silently swallow logger exceptions so it never breaks main application execution
        }
    }
}
