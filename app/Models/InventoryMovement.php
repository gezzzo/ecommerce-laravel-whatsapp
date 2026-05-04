<?php

namespace App\Models;

use App\Enums\InventoryMovementType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class InventoryMovement extends Model
{
    protected $fillable = [
        'inventory_id',
        'type',
        'quantity',
        'reference_type',
        'reference_id',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'type' => InventoryMovementType::class,
        'quantity' => 'integer',
    ];

    /**
     * Get the inventory record this movement belongs to.
     */
    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class);
    }

    /**
     * Get the reference model (Order, OrderReturn, etc.).
     */
    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the admin who created this movement.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    /**
     * Determine if this movement adds stock.
     */
    public function isAddition(): bool
    {
        return $this->quantity > 0;
    }

    /**
     * Determine if this movement deducts stock.
     */
    public function isDeduction(): bool
    {
        return $this->quantity < 0;
    }
}
