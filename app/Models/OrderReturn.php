<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderReturn extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'order_id',
        'order_item_id',
        'quantity',
        'refund_amount',
        'status',
        'reason',
        'admin_notes',
        'admin_id',
        'inventory_restored',
        'processed_at',
    ];

    protected $casts = [
        'refund_amount' => 'decimal:2',
        'inventory_restored' => 'boolean',
        'processed_at' => 'datetime',
    ];

    /** الحالات المتاحة للمرتجع */
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_COMPLETED = 'completed';

    /**
     * Get the order that this return belongs to.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the order item that this return belongs to.
     */
    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    /**
     * Get the admin who processed this return.
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }
}
