<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductAttributeType extends Model
{
    protected $table = 'product_attribute_types';
    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
        'position'  => 'integer',
    ];

    public function values(): HasMany
    {
        return $this->hasMany(ProductAttributeValue::class, 'product_attribute_type_id')->orderBy('position')->orderBy('id');
    }
}
