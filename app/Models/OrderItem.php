<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderItem extends Model
{
    use SoftDeletes;

    protected $fillable = ['order_id', 'sku_code', 'price', 'quantity'];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }


    /**
     * Scope to filter by SKU code.
     */
    public function scopeBySkuCode($query, $sku_code)
    {
        return $query->where('sku_code', $sku_code);
    }

    public function skuCode(): belongsTo
    {
        return $this->belongsTo(SkuCode::class, 'sku_code'); // Explicitly set the foreign key
    }

    public function returns(): HasMany
    {
        return $this->hasMany(OrderReturn::class);
    }

    public function getSubtotalAttribute(): float
    {
        return $this->price * $this->quantity;
    }
}
