<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    public const PLACEMENTS = [
        'hero'      => 'Hero Slider — Main homepage carousel slides (70% width on desktop)',
        'hero_side' => 'Promo Cards — Featured side / bottom promo banners (30% width on desktop, 2 cards)',
    ];

    /** Which screens a promo card shows on. Desktop is the wide layout with the cards beside the slider. */
    public const DEVICES = [
        'both'    => 'Phone and desktop',
        'desktop' => 'Desktop only',
        'mobile'  => 'Phone only',
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
        'starts_at' => 'datetime',
        'ends_at'   => 'datetime',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /** Switched on, and inside its start / end dates when it has them. */
    public function scopeLive(Builder $query): Builder
    {
        return $query->active()
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>', now()));
    }

    /** live, scheduled (starts later), ended or hidden, for the admin badges. */
    public function status(): string
    {
        return match (true) {
            ! $this->is_active => 'hidden',
            $this->starts_at && $this->starts_at->isFuture() => 'scheduled',
            $this->ends_at && ! $this->ends_at->isFuture() => 'ended',
            default => 'live',
        };
    }

    public function showsOnDesktop(): bool
    {
        return ($this->show_on ?? 'both') !== 'mobile';
    }

    public function showsOnPhone(): bool
    {
        return ($this->show_on ?? 'both') !== 'desktop';
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
