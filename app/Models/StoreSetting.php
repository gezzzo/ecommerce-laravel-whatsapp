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

    public const DEFAULT_ANNOUNCEMENT_BAR_TEXT = '🎉 شحن مجاني على الطلبات فوق 200 درهم! استخدم كود: FREESHIP';

    public const DEFAULT_HOME_FEATURES = [
        ['icon' => '🚚', 'title' => 'شحن مجاني', 'subtitle' => 'على طلبات +200 درهم'],
        ['icon' => '🔄', 'title' => 'إرجاع مجاني', 'subtitle' => 'خلال 30 يوم'],
        ['icon' => '🔒', 'title' => 'دفع آمن', 'subtitle' => '100% مضمون'],
        ['icon' => '📞', 'title' => 'دعم 24/7', 'subtitle' => 'خدمة متواصلة'],
    ];

    public const DEFAULT_PROMO_BANNER = [
        'badge' => 'عرض محدود الوقت ⏰',
        'title' => 'خصم حتى 70%',
        'subtitle' => 'على منتجات الأزياء المغربية بمناسبة العيد الاضحي المبارك',
    ];

    public const DEFAULT_CONTACT_INFO = [
        'phone' => '01000000000',
        'email' => 'info@mystore.com',
        'working_hours' => 'يومياً 9ص - 10م',
        'facebook_url' => '#',
        'instagram_url' => '#',
        'twitter_url' => '#',
    ];

    protected $fillable = ['key', 'value', 'type', 'group'];

    /**
     * Get a setting value by key, with optional default.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::remember("store_setting_{$key}", 3600, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();

            if (! $setting) {
                return $default;
            }

            return match ($setting->type) {
                'boolean' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
                'integer' => (int) $setting->value,
                'json' => json_decode($setting->value ?? '', true) ?? $default,
                default => $setting->value ?? $default,
            };
        });
    }

    /**
     * Set a setting value by key (creates or updates).
     */
    public static function set(string $key, mixed $value, string $type = 'string', string $group = 'general'): void
    {
        $storedValue = match ($type) {
            'boolean' => $value ? '1' : '0',
            'json' => json_encode($value, JSON_UNESCAPED_UNICODE) ?: '[]',
            default => $value,
        };

        static::updateOrCreate(
            ['key' => $key],
            ['value' => $storedValue, 'type' => $type, 'group' => $group]
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

    public static function announcementBarText(): string
    {
        return (string) static::get('announcement_bar_text', static::DEFAULT_ANNOUNCEMENT_BAR_TEXT);
    }

    /**
     * @return array<int, array{icon: string, title: string, subtitle: string}>
     */
    public static function homeFeatures(): array
    {
        $features = static::get('home_features', static::DEFAULT_HOME_FEATURES);

        if (! is_array($features)) {
            return static::DEFAULT_HOME_FEATURES;
        }

        $normalizedFeatures = [];

        foreach ($features as $feature) {
            if (! is_array($feature)) {
                continue;
            }

            $normalizedFeature = [
                'icon' => (string) ($feature['icon'] ?? ''),
                'title' => (string) ($feature['title'] ?? ''),
                'subtitle' => (string) ($feature['subtitle'] ?? ''),
            ];

            if (filled($normalizedFeature['title']) || filled($normalizedFeature['subtitle'])) {
                $normalizedFeatures[] = $normalizedFeature;
            }
        }

        return $normalizedFeatures;
    }

    /**
     * @return array{badge: string, title: string, subtitle: string}
     */
    public static function promoBanner(): array
    {
        return [
            'badge' => (string) static::get('promo_banner_badge', static::DEFAULT_PROMO_BANNER['badge']),
            'title' => (string) static::get('promo_banner_title', static::DEFAULT_PROMO_BANNER['title']),
            'subtitle' => (string) static::get('promo_banner_subtitle', static::DEFAULT_PROMO_BANNER['subtitle']),
        ];
    }

    public static function storeName(): string
    {
        return (string) static::get('store_name', 'متجري');
    }

    public static function metaPixelId(): string
    {
        $pixelId = trim((string) static::get('meta_pixel_id', ''));

        return preg_match('/^\d{5,32}$/', $pixelId) ? $pixelId : '';
    }

    /**
     * @return array{phone: string, email: string, working_hours: string, facebook_url: string, instagram_url: string, twitter_url: string}
     */
    public static function contactInfo(): array
    {
        return [
            'phone' => (string) static::get('contact_phone', static::DEFAULT_CONTACT_INFO['phone']),
            'email' => (string) static::get('contact_email', static::DEFAULT_CONTACT_INFO['email']),
            'working_hours' => (string) static::get('contact_working_hours', static::DEFAULT_CONTACT_INFO['working_hours']),
            'facebook_url' => (string) static::get('contact_facebook_url', static::DEFAULT_CONTACT_INFO['facebook_url']),
            'instagram_url' => (string) static::get('contact_instagram_url', static::DEFAULT_CONTACT_INFO['instagram_url']),
            'twitter_url' => (string) static::get('contact_twitter_url', static::DEFAULT_CONTACT_INFO['twitter_url']),
        ];
    }
}
