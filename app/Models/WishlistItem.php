<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WishlistItem extends Model
{
    protected $fillable = ['wishlist_id', 'sku_code'];

    public function wishlist(): BelongsTo
    {
        return $this->belongsTo(Wishlist::class);
    }

    public function skuCode(): BelongsTo
    {
        return $this->belongsTo(SkuCode::class, 'sku_code');
    }
}
