<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    /** @var string|null No single-column primary key — composite (cart_id, sku_code). */
    protected $primaryKey = null;

    /** @var bool No auto-incrementing id column exists on this table. */
    public $incrementing = false;

    protected $fillable = ['cart_id', 'sku_code', 'quantity'];

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function skuCode(): BelongsTo
    {
        return $this->belongsTo(SkuCode::class, 'sku_code');
    }
}

