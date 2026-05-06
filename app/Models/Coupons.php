<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Coupons extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code', 'type', 'value', 'max_uses',
        'used_count', 'expires_at', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'max_uses' => 'integer',
        'used_count' => 'integer',
        'value' => 'decimal:2',
        'expires_at' => 'datetime',
    ];

    public function scopeAvailable(Builder $query): Builder
    {
        return $query
            ->where('is_active', true)
            ->where(function (Builder $query): void {
                $query
                    ->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->where(function (Builder $query): void {
                $query
                    ->whereNull('max_uses')
                    ->orWhereColumn('used_count', '<', 'max_uses');
            });
    }

    public function isAvailable(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->expires_at !== null && $this->expires_at->isPast()) {
            return false;
        }

        if ($this->max_uses !== null && $this->used_count >= $this->max_uses) {
            return false;
        }

        return true;
    }

    public function discountFor(float $subtotal): float
    {
        $subtotal = max(0, $subtotal);
        $value = max(0, (float) $this->value);

        $discount = match ($this->type) {
            'percent' => $subtotal * ($value / 100),
            default => $value,
        };

        return round(min($discount, $subtotal), 2);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'coupon_id');
    }
}
