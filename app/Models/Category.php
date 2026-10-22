<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_active'   => 'boolean',
        'is_featured' => 'boolean',
        // Some MySQL drivers return this as a string; nest() compares it strictly with the integer ids
        'parent_id'   => 'integer',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('position');
    }

    /** Main categories only (sub-categories are one level deep). */
    public function scopeTopLevel(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    public function isSubcategory(): bool
    {
        return $this->parent_id !== null;
    }

    /** This category plus its sub-categories: a main category's page lists all of their products. */
    public function familyIds(): array
    {
        return [$this->id, ...$this->children()->pluck('id')->all()];
    }

    /**
     * Turns a flat list (each with products_count) into main categories with their sub-categories as
     * `children`, each main count including its sub-categories. A sub-category whose main category is
     * not in the list (hidden) is shown as a main one so its products stay reachable.
     */
    public static function nest(Collection $all): Collection
    {
        $ids = $all->pluck('id')->all();
        $isChild = fn (Category $c) => $c->parent_id !== null && in_array($c->parent_id, $ids, true);
        $children = $all->filter($isChild)->groupBy('parent_id');

        return $all->reject($isChild)
            ->each(function (Category $c) use ($children) {
                $kids = ($children[$c->id] ?? collect())->sortBy([['products_count', 'desc'], ['position', 'asc']])->values();
                $c->setRelation('children', $kids);
                $c->products_count = (int) $c->products_count + (int) $kids->sum('products_count');
            })
            ->sortBy([['products_count', 'desc'], ['position', 'asc']])
            ->values();
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function imageUrl(): string
    {
        return image_url($this->image, $this->slug);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
