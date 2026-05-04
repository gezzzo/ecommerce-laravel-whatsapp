<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id', 'name', 'slug', 'description',
        'price_before_discount', 'selling_price',
        'thumbnail', 'image', 'has_variants',
        'is_active', 'is_featured',
    ];

    protected $casts = [
        'is_active'    => 'boolean',
        'is_featured'  => 'boolean',
        'has_variants' => 'boolean',
        'price_before_discount' => 'decimal:2',
        'selling_price'         => 'decimal:2',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(Variant::class);
    }

    public function skuCode(): MorphOne
    {
        return $this->morphOne(SkuCode::class, 'skuable');
    }

    /**
     * Get the inventory for this product (only when has_variants is false).
     */
    public function inventory(): MorphOne
    {
        return $this->morphOne(Inventory::class, 'inventoriable');
    }
}
