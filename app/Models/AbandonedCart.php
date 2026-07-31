<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class AbandonedCart extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'cart_data'        => 'array',
        'subtotal'         => 'decimal:2',
        'total'            => 'decimal:2',
        'reminder_sent_at' => 'datetime',
        'recovered_at'     => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function generateToken(): string
    {
        return Str::random(32);
    }

    public function recoveryUrl(): string
    {
        return route('cart.recover', $this->recovery_token);
    }

    public function whatsAppUrl(): string
    {
        $phone = preg_replace('/\D/', '', (string) $this->customer_phone);
        if (str_starts_with($phone, '01')) {
            $phone = '880' . substr($phone, 1);
        }

        $site = site_name();
        $link = $this->recoveryUrl();
        $message = urlencode("Hello {$this->customer_name}, you left items in your cart at {$site}! Click here to complete your order in 1-click: {$link}");

        return "https://wa.me/{$phone}?text={$message}";
    }

    public function scopeAbandoned($query)
    {
        return $query->where('status', 'abandoned');
    }

    public function scopeReminderSent($query)
    {
        return $query->where('status', 'reminder_sent');
    }

    public function scopeRecovered($query)
    {
        return $query->where('status', 'recovered');
    }

    public function isBlacklisted(): bool
    {
        return Blacklist::isBlacklisted('phone', $this->customer_phone) ||
               Blacklist::isBlacklisted('email', $this->customer_email);
    }

    public function cancelledOrdersCount(): int
    {
        if (empty($this->customer_phone)) {
            return 0;
        }

        return Order::where('customer_phone', $this->customer_phone)
            ->where('status', 'cancelled')
            ->count();
    }

    public function fraudBadgeInfo(): array
    {
        if ($this->isBlacklisted()) {
            return [
                'level' => 'blacklisted',
                'label' => '🔴 Blacklisted',
                'class' => 'bg-rose-100 text-rose-800 border border-rose-200 font-extrabold',
            ];
        }

        $cancelled = $this->cancelledOrdersCount();
        if ($cancelled > 0) {
            return [
                'level' => 'warning',
                'label' => "🟡 {$cancelled} Cancelled",
                'class' => 'bg-amber-100 text-amber-800 border border-amber-200 font-bold',
            ];
        }

        return [
            'level' => 'clean',
            'label' => '🟢 Clean Visitor',
            'class' => 'bg-emerald-100 text-emerald-800 border border-emerald-200 font-bold',
        ];
    }
}
