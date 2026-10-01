<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderPrint extends Model
{
    public const UPDATED_AT = null;

    public const TYPES = ['invoice', 'label'];

    protected $guarded = [];

    protected $casts = ['created_at' => 'datetime'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** "Rahim, today 3:15 PM (Half page)" */
    public function summary(): string
    {
        $when = $this->created_at->isToday() ? 'today ' . $this->created_at->format('g:i A') : $this->created_at->format('d M, g:i A');
        $format = $this->format ? (\App\Http\Controllers\Admin\OrderController::INVOICE_FORMATS[$this->format] ?? null) : null;

        return trim(($this->user?->name ?? 'Someone') . ', ' . $when . ($format ? " ({$format})" : ''));
    }
}
