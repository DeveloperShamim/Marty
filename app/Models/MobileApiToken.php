<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class MobileApiToken extends Model
{
    protected $guarded = [];

    protected $casts = [
        'last_used_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Create a token for the user and return the plain-text value (shown to the app only once). */
    public static function issue(User $user, string $deviceName): string
    {
        $plain = Str::random(60);

        static::create([
            'user_id' => $user->id,
            'name'    => $deviceName,
            'token'   => hash('sha256', $plain),
        ]);

        return $plain;
    }

    public static function findByPlain(string $plain): ?self
    {
        return static::where('token', hash('sha256', $plain))->first();
    }
}
