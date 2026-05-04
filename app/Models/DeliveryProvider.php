<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeliveryProvider extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'slug', 'base_url', 'is_active', 'logo'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function companies(): HasMany
    {
        return $this->hasMany(DeliveryCompany::class);
    }
}
