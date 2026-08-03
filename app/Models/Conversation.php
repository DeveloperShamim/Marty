<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Conversation extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'last_message_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ConversationMessage::class)->orderBy('id', 'asc');
    }

    public function lastMessage(): HasOne
    {
        return $this->hasOne(ConversationMessage::class)->latestOfMany();
    }

    public function markAsReadForAdmin(): void
    {
        $this->update(['unread_admin_count' => 0]);
        $this->messages()->where('sender_type', 'customer')->where('is_read', false)->update(['is_read' => true]);
    }

    public function markAsReadForCustomer(): void
    {
        $this->update(['unread_customer_count' => 0]);
        $this->messages()->where('sender_type', 'admin')->where('is_read', false)->update(['is_read' => true]);
    }

    public function getCustomerOrders()
    {
        $userId = $this->user_id;
        $phone = $this->customer_phone ? preg_replace('/[^0-9]/', '', $this->customer_phone) : null;
        $email = $this->customer_email;

        if (!$userId && !$phone && !$email) {
            return collect();
        }

        return Order::query()
            ->where(function ($q) use ($userId, $phone, $email) {
                if ($userId) {
                    $q->orWhere('user_id', $userId);
                }
                if ($phone && strlen($phone) >= 6) {
                    $q->orWhere('customer_phone', 'like', '%' . substr($phone, -8) . '%');
                }
                if ($email) {
                    $q->orWhere('customer_email', $email);
                }
            })
            ->with(['items.product.images'])
            ->orderBy('id', 'desc')
            ->take(10)
            ->get();
    }
}
