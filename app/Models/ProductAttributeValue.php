<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductAttributeValue extends Model
{
    protected $table = 'product_attribute_values';
    protected $guarded = [];

    protected $casts = [
        'position' => 'integer',
    ];

    public function type(): BelongsTo
    {
        return $this->belongsTo(ProductAttributeType::class, 'product_attribute_type_id');
    }
}
