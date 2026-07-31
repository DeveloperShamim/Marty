<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Blacklist extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'value',
        'reason',
    ];

    public static function isBlacklisted(string $type, ?string $value): bool
    {
        if (empty($value)) {
            return false;
        }

        $cleanValue = strtolower(trim($value));

        return static::where('type', $type)
            ->where('value', $cleanValue)
            ->exists();
    }

    public static function add(string $type, string $value, ?string $reason = null): static
    {
        $cleanValue = strtolower(trim($value));

        return static::updateOrCreate(
            ['type' => $type, 'value' => $cleanValue],
            ['reason' => $reason]
        );
    }
}
