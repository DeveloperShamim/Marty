<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductImage extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    /** A row whose file is one of these is a product video, not a photo. */
    public const VIDEO_EXTENSIONS = ['mp4', 'webm', 'mov'];

    /** Photos only: cards, carts, feeds and SEO tags all expect an image. */
    public function scopeOnlyImages($query)
    {
        foreach (self::VIDEO_EXTENSIONS as $ext) {
            $query->where('path', 'not like', '%.' . $ext);
        }

        return $query;
    }

    public function scopeOnlyVideos($query)
    {
        return $query->where(function ($q) {
            foreach (self::VIDEO_EXTENSIONS as $ext) {
                $q->orWhere('path', 'like', '%.' . $ext);
            }
        });
    }

    public function isVideo(): bool
    {
        return in_array(strtolower(pathinfo((string) $this->path, PATHINFO_EXTENSION)), self::VIDEO_EXTENSIONS, true);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function url(): string
    {
        if ($this->isVideo()) {
            return asset(ltrim((string) $this->path, '/'));
        }

        return image_url($this->path, $this->alt ?? 'product');
    }
}
