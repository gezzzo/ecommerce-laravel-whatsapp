<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class StoreSetting extends Model
{
    /** Checkout mode options */
    public const CHECKOUT_REQUIRED = 'required';  // يجب تسجيل الدخول

    public const CHECKOUT_OPTIONAL = 'optional';  // تسجيل الدخول اختياري (guest مسموح)

    public const CHECKOUT_GUEST = 'guest';         // بيانات الشحن فقط، بدون حساب

    /** Shipping mode options */
    public const SHIPPING_FREE = 'free';                       // شحن مجاني دائماً

    public const SHIPPING_PAID = 'paid';                       // شحن مدفوع بالكامل (حسب delivery zone)

    public const SHIPPING_FREE_AFTER_AMOUNT = 'free_after_amount';   // مجاني بعد مبلغ معين

    public const SHIPPING_FREE_AFTER_ITEMS = 'free_after_items';     // مجاني بعد عدد منتجات معين

    protected $fillable = ['key', 'value', 'type', 'group'];

    /**
     * Get a setting value by key, with optional default.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::remember("store_setting_{$key}", 3600, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();

            return $setting?->value ?? $default;
        });
    }

    /**
     * Set a setting value by key (creates or updates).
     */
    public static function set(string $key, mixed $value, string $type = 'string', string $group = 'general'): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'type' => $type, 'group' => $group]
        );

        Cache::forget("store_setting_{$key}");
    }

    /**
     * Get the current checkout mode.
     */
    public static function checkoutMode(): string
    {
        return static::get('checkout_mode', static::CHECKOUT_OPTIONAL);
    }

    /**
     * Get the current shipping mode.
     */
    public static function shippingMode(): string
    {
        return static::get('shipping_mode', static::SHIPPING_PAID);
    }

    /**
     * Calculate shipping cost based on the current mode and order details.
     *
     * @param  array{subtotal: float, item_count: int, zone_fee: float}  $orderDetails
     */
    public static function calculateShipping(float $subtotal, int $itemCount, float $zoneFee): float
    {
        $mode = static::shippingMode();

        return match ($mode) {
            static::SHIPPING_FREE => 0,
            static::SHIPPING_PAID => $zoneFee,
            static::SHIPPING_FREE_AFTER_AMOUNT => $subtotal >= (float) static::get('free_shipping_threshold', 200)
                ? 0
                : $zoneFee,
            static::SHIPPING_FREE_AFTER_ITEMS => $itemCount >= (int) static::get('free_shipping_item_count', 3)
                ? 0
                : $zoneFee,
            default => $zoneFee,
        };
    }
}
