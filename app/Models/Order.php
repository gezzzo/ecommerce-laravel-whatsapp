<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'user_id', 'order_number', 'name', 'phone', 'address', 'city',
    'comment', 'status', 'delivery_status', 'payment_status', 'payment_method',
    'manual_delivery_status', 'coupon_id', 'subtotal', 'shipping',
    'discount', 'total', 'tracking_number', 'coupon_code',
    'delivery_zone_id', 'delivery_company_id', 'whatsapp_phone',
    'whatsapp_confirmed_at', 'whatsapp_confirmation_message_id',
])]
class Order extends Model
{
    use SoftDeletes;

    protected $casts = [
        'manual_delivery_status' => 'boolean',
        'subtotal' => 'decimal:2',
        'shipping' => 'decimal:2',
        'discount' => 'decimal:2',
        'total'    => 'decimal:2',
        'whatsapp_confirmed_at' => 'datetime',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupons::class, 'coupon_id');
    }

    public function deliveryZone(): BelongsTo
    {
        return $this->belongsTo(DeliveryZone::class);
    }

    public function deliveryCompany(): BelongsTo
    {
        return $this->belongsTo(DeliveryCompany::class);
    }

    public function trackingParcels(): HasMany
    {
        return $this->hasMany(TrackingParcel::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->delivery_status) {
            'pending'    => '⏳ قيد المراجعة',
            'processing' => '🔄 جاري التحضير',
            'shipped'    => '🚚 في الشحن',
            'delivered'  => '✅ تم التسليم',
            'cancelled'  => '❌ ملغي',
            default      => $this->delivery_status ?? '',
        };
    }
}
