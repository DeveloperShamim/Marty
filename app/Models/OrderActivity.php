<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/** One line in an order's history: a phone call, a private staff note, or a status/payment/courier change. */
class OrderActivity extends Model
{
    protected $guarded = [];

    /** Results a staff member can pick after calling the customer: key => [label, icon, badge classes, short label]. */
    public const CALL_RESULTS = [
        'confirmed'    => ['Confirmed the order', 'check-circle', 'bg-emerald-50 text-emerald-700 ring-emerald-200', 'Confirmed'],
        'no_answer'    => ['No answer', 'phone-x', 'bg-amber-50 text-amber-800 ring-amber-200', 'No answer'],
        'phone_off'    => ['Phone off / unreachable', 'ban', 'bg-amber-50 text-amber-800 ring-amber-200', 'Phone off'],
        'call_back'    => ['Asked to call back', 'undo', 'bg-sky-50 text-sky-700 ring-sky-200', 'Call back'],
        'wants_cancel' => ['Wants to cancel', 'x-circle', 'bg-rose-50 text-rose-700 ring-rose-200', 'Cancel'],
        'wrong_number' => ['Wrong number / fake', 'alert', 'bg-rose-50 text-rose-700 ring-rose-200', 'Wrong no.'],
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** Record an entry as the signed-in staff member ("System" for automatic changes). */
    public static function record(Order $order, string $type, ?string $body = null, ?string $callResult = null): self
    {
        $user = Auth::user();

        return self::create([
            'order_id'    => $order->id,
            'user_id'     => $user?->id,
            'staff_name'  => $user?->name ?? 'System',
            'type'        => $type,
            'call_result' => $callResult,
            'body'        => $body,
        ]);
    }

    public function callLabel(): ?string
    {
        return self::CALL_RESULTS[$this->call_result][0] ?? null;
    }

    /** Icon name for <x-oi>. */
    public function icon(): string
    {
        return match ($this->type) {
            'call'    => self::CALL_RESULTS[$this->call_result][1] ?? 'phone',
            'note'    => 'note',
            'payment' => 'card',
            'courier' => 'truck',
            default   => 'refresh',
        };
    }

    /** Dot colour on the timeline. */
    public function dotClass(): string
    {
        return match ($this->type) {
            'call'  => str_contains(self::CALL_RESULTS[$this->call_result][2] ?? '', 'emerald') ? 'bg-emerald-100 text-emerald-700'
                : (str_contains(self::CALL_RESULTS[$this->call_result][2] ?? '', 'rose') ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-700'),
            'note'  => 'bg-brand-50 text-brand-700',
            default => 'bg-slate-100 text-slate-500',
        };
    }

    public function badgeClass(): string
    {
        return self::CALL_RESULTS[$this->call_result][2] ?? 'bg-slate-50 text-slate-700 ring-slate-200';
    }
}
