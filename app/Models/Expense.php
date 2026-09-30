<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    protected $guarded = [];

    protected $casts = [
        'amount'          => 'decimal:2',
        'currency_amount' => 'decimal:2',
        'currency_rate'   => 'decimal:2',
        'expense_date'    => 'date',
    ];

    public const CATEGORIES = [
        'marketing'       => 'Marketing & Facebook Ads',
        'sourcing_travel' => 'Sourcing & Travel Trips',
        'packaging'       => 'Packaging & Shipping Supplies',
        'rent_utilities'  => 'Showroom / Office Rent & Bills',
        'salaries'        => 'Staff Wages & Labor',
        'bank_fees'       => 'bKash / Nagad / Bank Cashout Fees',
        'other'           => 'General & Maintenance',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function categoryLabel(): string
    {
        return self::CATEGORIES[$this->category] ?? ucfirst(str_replace('_', ' ', $this->category));
    }

    public function categoryBadge(): string
    {
        return match ($this->category) {
            'marketing'       => 'bg-blue-50 text-blue-700 border-blue-200',
            'sourcing_travel' => 'bg-amber-50 text-amber-700 border-amber-200',
            'packaging'       => 'bg-purple-50 text-purple-700 border-purple-200',
            'rent_utilities'  => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'salaries'        => 'bg-rose-50 text-rose-700 border-rose-200',
            'bank_fees'       => 'bg-orange-50 text-orange-700 border-orange-200',
            default           => 'bg-gray-50 text-gray-700 border-gray-200',
        };
    }

    public function categoryIcon(): string
    {
        return match ($this->category) {
            'marketing'       => '📣',
            'sourcing_travel' => '🚗',
            'packaging'       => '📦',
            'rent_utilities'  => '🏢',
            'salaries'        => '👥',
            'bank_fees'       => '💳',
            default           => '📝',
        };
    }

    public function receiptUrl(): ?string
    {
        if (!$this->receipt_attachment) {
            return null;
        }

        if (str_starts_with($this->receipt_attachment, 'http://') || str_starts_with($this->receipt_attachment, 'https://')) {
            return $this->receipt_attachment;
        }

        return asset('storage/' . ltrim($this->receipt_attachment, '/'));
    }
}
