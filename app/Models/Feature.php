<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feature extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function renderIconHtml(string $sizeClass = 'h-8 w-8', string $colorClass = 'text-brand-600'): string
    {
        $icon = trim((string) $this->icon);
        if ($icon === '') {
            return '';
        }

        // If icon is an image URL or file path
        if (str_starts_with($icon, 'http') || str_starts_with($icon, '/') || str_contains($icon, '.png') || str_contains($icon, '.jpg') || str_contains($icon, '.svg') || str_contains($icon, '.webp')) {
            $src = str_starts_with($icon, 'http') ? $icon : asset($icon);
            return '<img src="' . e($src) . '" alt="' . e($this->title) . '" class="' . e($sizeClass) . ' object-contain mx-auto" />';
        }

        // If icon is full SVG string
        if (str_contains($icon, '<svg')) {
            return $icon;
        }

        // If icon is SVG path data (starts with M or m)
        if (!preg_match('/[\x{1F300}-\x{1F6FF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}]/u', $icon) && (str_starts_with($icon, 'M') || str_starts_with($icon, 'm') || str_starts_with($icon, 'm') || str_starts_with($icon, 'M'))) {
            return '<svg class="' . e($sizeClass) . ' ' . e($colorClass) . ' mx-auto" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="' . e($icon) . '"/></svg>';
        }

        // Fallback: Emoji or text symbol
        return '<span class="text-3xl inline-block leading-none select-none">' . e($icon) . '</span>';
    }
}
