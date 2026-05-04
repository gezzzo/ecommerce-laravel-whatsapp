<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrackingParcel extends Model
{
    protected $fillable = [
        'order_id',
        'parcel_code',
        'statut_name',
        'statut_color',
        'situation_name',
        'situation_color',
        'livreur',
        'commentaire',
        'time',
    ];

    protected $casts = [
        'time' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
