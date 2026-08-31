<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'is_suspended', 'phone', 'address', 'city', 'postal_code', 'google_id', 'avatar'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_suspended' => 'boolean',
        ];
    }

    public function isStaff(): bool
    {
        return in_array($this->role, ['admin', 'store_manager', 'order_manager', 'inventory_manager'], true) && !$this->is_suspended;
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin' && !$this->is_suspended;
    }

    public function isStoreManager(): bool
    {
        return in_array($this->role, ['admin', 'store_manager'], true) && !$this->is_suspended;
    }

    public function isOrderManager(): bool
    {
        return in_array($this->role, ['admin', 'store_manager', 'order_manager'], true) && !$this->is_suspended;
    }

    public function isInventoryManager(): bool
    {
        return in_array($this->role, ['admin', 'store_manager', 'inventory_manager'], true) && !$this->is_suspended;
    }

    public function isCustomer(): bool
    {
        return $this->role === 'customer';
    }

    public function hasVerifiedEmail(): bool
    {
        return $this->email_verified_at !== null;
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function avatarUrl(): ?string
    {
        if (!empty($this->avatar)) {
            if (str_starts_with($this->avatar, 'http://') || str_starts_with($this->avatar, 'https://')) {
                return $this->avatar;
            }
            return asset('storage/' . ltrim($this->avatar, '/'));
        }
        return null;
    }
}
