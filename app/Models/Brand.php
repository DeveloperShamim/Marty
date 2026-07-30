<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Brand extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_active'   => 'boolean',
        'is_featured' => 'boolean',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function logoUrl(): string
    {
        if ($this->logo) {
            if (str_starts_with($this->logo, 'http://') || str_starts_with($this->logo, 'https://')) {
                return $this->logo;
            }
            return asset('storage/' . $this->logo);
        }
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&color=4f46e5&background=e0e7ff&bold=true';
    }

    public function bannerUrl(): ?string
    {
        if ($this->banner) {
            if (str_starts_with($this->banner, 'http://') || str_starts_with($this->banner, 'https://')) {
                return $this->banner;
            }
            return asset('storage/' . $this->banner);
        }
        return null;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
