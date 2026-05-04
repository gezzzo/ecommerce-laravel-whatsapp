<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Inventory extends Model
{
    protected $fillable = [
        'inventoriable_type',
        'inventoriable_id',
        'quantity',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    /**
     * Get the owning inventoriable model (Product or Variant).
     */
    public function inventoriable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get all movements for this inventory.
     */
    public function movements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class)->latest();
    }

    /**
     * Recalculate quantity from all movements.
     */
    public function recalculateQuantity(): void
    {
        $this->update([
            'quantity' => $this->movements()->sum('quantity'),
        ]);
    }
}
