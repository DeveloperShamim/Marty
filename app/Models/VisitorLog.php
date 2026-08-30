<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisitorLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'ip_address',
        'visit_date',
        'user_agent',
        'page_url',
        'referer',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'device_type',
        'browser',
        'user_id',
        'is_bot',
        'hits',
    ];

    protected $casts = [
        'visit_date' => 'date',
        'is_bot' => 'boolean',
        'hits' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
