<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Coupons;
use App\Models\DeliveryCompany;
use App\Models\DeliveryProvider;
use App\Models\DeliveryZone;
use App\Models\Order;
use App\Models\Product;
use App\Models\SkuCode;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CouponTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_valid_coupon_applies_to_cart_summary(): void
    {
        $cart = $this->createCartWithProduct(price: 100, quantity: 2);

        Coupons::create([
            'code' => 'SAVE10',
            'type' => 'percent',
            'value' => 10,
            'is_active' => true,
        ]);

        $response = $this
            ->withSession(['cart_id' => $cart->id])
            ->post(route('coupon.apply'), [
                'coupon' => ' save10 ',
            ]);

        $response
            ->assertRedirect()
            ->assertSessionHas('coupon_code', 'SAVE10')
            ->assertSessionHas('success');

        $this
            ->withSession([
                'cart_id' => $cart->id,
                'coupon_code' => 'SAVE10',
            ])
            ->get(route('cart'))
            ->assertOk()
            ->assertSee('SAVE10')
            ->assertSee('-20 درهم', false);
    }

    public function test_unavailable_coupon_is_rejected(): void
    {
        $cart = $this->createCartWithProduct();

        Coupons::create([
            'code' => 'OLD',
            'type' => 'fixed',
            'value' => 20,
            'is_active' => false,
        ]);

        $response = $this
            ->withSession(['cart_id' => $cart->id])
            ->post(route('coupon.apply'), [
                'coupon' => 'OLD',
            ]);

        $response
            ->assertRedirect()
            ->assertSessionMissing('coupon_code')
            ->assertSessionHas('error', 'كود الخصم غير صالح.');
    }

    public function test_checkout_persists_coupon_discount_and_usage_count(): void
    {
        $zone = $this->createDeliveryZone();
        $cart = $this->createCartWithProduct(price: 100, quantity: 2);

        $coupon = Coupons::create([
            'code' => 'SAVE50',
            'type' => 'fixed',
            'value' => 50,
            'max_uses' => 2,
            'is_active' => true,
        ]);

        $response = $this
            ->withSession([
                'cart_id' => $cart->id,
                'coupon_code' => 'SAVE50',
            ])
            ->post(route('checkout.store'), [
                'name' => 'Ahmed Ali',
                'phone' => '0612345678',
                'address' => '123 Main St',
                'delivery_zone_id' => $zone->id,
                'payment_method' => 'cod',
            ]);

        $response
            ->assertRedirect()
            ->assertSessionMissing('coupon_code');

        $order = Order::firstOrFail();
        $coupon->refresh();

        $this->assertSame($coupon->id, $order->coupon_id);
        $this->assertSame('SAVE50', $order->coupon_code);
        $this->assertSame('200.00', (string) $order->subtotal);
        $this->assertSame('30.00', (string) $order->shipping);
        $this->assertSame('50.00', (string) $order->discount);
        $this->assertSame('180.00', (string) $order->total);
        $this->assertSame(1, $coupon->used_count);
    }

    private function createDeliveryZone(): DeliveryZone
    {
        $provider = DeliveryProvider::create([
            'name' => 'Ozone Express',
            'slug' => 'ozone-'.Str::random(8),
            'base_url' => 'https://api.ozonexpress.ma/customers',
            'is_active' => true,
        ]);

        $company = DeliveryCompany::create([
            'delivery_provider_id' => $provider->id,
            'is_active' => true,
        ]);

        return DeliveryZone::create([
            'delivery_company_id' => $company->id,
            'city' => 'Casablanca',
            'delivery_fee' => 30,
            'visible' => true,
        ]);
    }

    private function createCartWithProduct(float $price = 100, int $quantity = 1): Cart
    {
        $category = Category::create([
            'name' => 'Dresses',
            'slug' => 'dresses-'.Str::random(8),
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Abaya',
            'slug' => 'abaya-'.Str::random(8),
            'description' => 'Test product',
            'selling_price' => $price,
            'thumbnail' => 'thumb.jpg',
            'image' => 'image.jpg',
            'has_variants' => false,
            'is_active' => true,
        ]);

        $skuCode = SkuCode::create([
            'skuable_type' => Product::class,
            'skuable_id' => $product->id,
            'sku_code' => 'SKU-'.Str::upper(Str::random(8)),
        ]);

        $cart = Cart::create([
            'session_id' => Str::uuid()->toString(),
        ]);

        CartItem::create([
            'cart_id' => $cart->id,
            'sku_code' => $skuCode->id,
            'quantity' => $quantity,
        ]);

        return $cart;
    }
}
