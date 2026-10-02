<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffActivityLog extends Model
{
    use HasFactory, MassPrunable;

    protected $fillable = [
        'user_id',
        'staff_name',
        'staff_role',
        'action',
        'description',
        'ip_address',
    ];

    /** Entries older than a year are deleted by the daily model:prune run. */
    public function prunable()
    {
        return static::where('created_at', '<', now()->subYear());
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
