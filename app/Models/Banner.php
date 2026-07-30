<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    public const PLACEMENTS = [
        'hero'      => 'Hero — homepage slider slides (add multiple; ordered by position)',
        'hero_side' => 'Hero side — stacked cards beside discounts (up to 4)',
    ];

    public const STYLES = [
        'brand'  => 'Blue (brand)',
        'amber'  => 'Amber / gold',
        'rose'   => 'Rose / pink',
        'accent' => 'Accent gradient (deals sidebar)',
    ];

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopePlacement(Builder $query, string $placement): Builder
    {
        return $query->where('placement', $placement);
    }

    public function placementLabel(): string
    {
        return explode(' — ', self::PLACEMENTS[$this->placement] ?? $this->placement)[0];
    }

    public function styleLabel(): string
    {
        return self::STYLES[$this->style] ?? $this->style;
    }

    /** Tailwind classes for promo / hero-side cards. */
    public function cardClasses(): array
    {
        return match ($this->style) {
            'amber' => [
                'bg'    => 'bg-amber-50',
                'btn'   => 'bg-accent-500 hover:bg-accent-600',
                'side'  => 'bg-amber-100',
                'badge' => 'text-accent-600',
            ],
            'rose' => [
                'bg'    => 'bg-rose-50',
                'btn'   => 'bg-rose-500 hover:bg-rose-600',
                'side'  => 'bg-rose-100',
                'badge' => 'text-rose-600',
            ],
            'accent' => [
                'bg'    => 'bg-gradient-to-b from-accent-400 to-accent-500',
                'btn'   => 'bg-ink hover:bg-black',
                'side'  => 'bg-gradient-to-b from-accent-400 to-accent-500',
                'badge' => 'text-ink',
            ],
            default => [
                'bg'    => 'bg-brand-50',
                'btn'   => 'bg-brand-600 hover:bg-brand-700',
                'side'  => 'bg-brand-100',
                'badge' => 'text-brand-700',
            ],
        };
    }

    public function linkHref(): string
    {
        if (! $this->link_url) {
            return route('shop');
        }

        return str_starts_with($this->link_url, 'http') ? $this->link_url : url($this->link_url);
    }

    public function imageUrl(): string
    {
        return image_url($this->image, $this->title ?? 'banner');
    }
}
