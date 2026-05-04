<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeliveryZone extends Model
{
    use SoftDeletes;

    protected $fillable = ['delivery_company_id', 'city', 'delivery_fee', 'external_city_id', 'visible'];

    protected $casts = [
        'delivery_fee' => 'decimal:2',
        'visible' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(DeliveryCompany::class, 'delivery_company_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
