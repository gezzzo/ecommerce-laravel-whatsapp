<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Variant extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'product_id', 'size_id', 'color_id',
        'cost_price', 'price_before_discount', 'selling_price', 'image',
    ];

    protected $casts = [
        'cost_price'            => 'decimal:2',
        'price_before_discount' => 'decimal:2',
        'selling_price'         => 'decimal:2',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function size(): BelongsTo
    {
        return $this->belongsTo(Size::class);
    }

    public function color(): BelongsTo
    {
        return $this->belongsTo(Color::class);
    }

    public function skuCode(): MorphOne
    {
        return $this->morphOne(SkuCode::class, 'skuable');
    }

    /**
     * Get the inventory for this variant.
     */
    public function inventory(): MorphOne
    {
        return $this->morphOne(Inventory::class, 'inventoriable');
    }
}
