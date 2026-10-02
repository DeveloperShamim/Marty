<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerCourierCheck extends Model
{
    protected $guarded = [];

    protected $casts = [
        'couriers'      => 'array',
        'reports'       => 'array',
        'success_ratio' => 'float',
        'checked_at'    => 'datetime',
    ];

    /** new | low | medium | high, from the combined delivery history and fraud reports. */
    public function riskLevel(): string
    {
        if (count($this->reports ?? []) > 0) {
            return 'high';
        }
        if ($this->total_parcels === 0) {
            return 'new';
        }
        if ($this->total_parcels >= 3 && $this->success_ratio < 50) {
            return 'high';
        }

        return $this->success_ratio < 80 ? 'medium' : 'low';
    }

    public function riskLabel(): string
    {
        return match ($this->riskLevel()) {
            'new'    => 'No courier history',
            'low'    => 'Reliable customer',
            'medium' => 'Some returns: confirm by phone',
            'high'   => count($this->reports ?? []) ? 'Reported for fraud' : 'Mostly returns: high risk',
        };
    }
}
