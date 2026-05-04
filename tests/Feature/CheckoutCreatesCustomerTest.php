<?php

namespace Tests\Feature;

use App\Enums\WhatsappSessionStatus;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\DeliveryCompany;
use App\Models\DeliveryProvider;
use App\Models\DeliveryZone;
use App\Models\Order;
use App\Models\Product;
use App\Models\SkuCode;
use App\Models\User;
use App\Models\WhatsappSession;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class CheckoutCreatesCustomerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_guest_checkout_creates_guest_customer_and_links_order(): void
    {
        $zone = $this->createDeliveryZone();
        $cart = $this->createCartWithProduct();

        $response = $this
            ->withSession(['cart_id' => $cart->id])
            ->post(route('checkout.store'), [
                'name' => 'Ahmed Ali',
                'phone' => '06 12 34 56 78',
                'address' => '123 Main St',
                'delivery_zone_id' => $zone->id,
                'payment_method' => 'cod',
            ]);

        $response->assertRedirect();
        $this->assertStringContainsString('/checkout/confirmation/', $response->headers->get('Location'));
        $this->assertStringContainsString('signature=', $response->headers->get('Location'));

        $customer = User::where('phone', '0612345678')->first();

        $this->assertNotNull($customer);
        $this->assertTrue($customer->is_guest);
        $this->assertDatabaseHas('orders', [
            'user_id' => $customer->id,
            'phone' => '0612345678',
            'name' => 'Ahmed Ali',
        ]);
    }

    public function test_repeat_guest_checkout_reuses_customer_by_phone(): void
    {
        $zone = $this->createDeliveryZone();

        $firstCart = $this->createCartWithProduct();
        $this
            ->withSession(['cart_id' => $firstCart->id])
            ->post(route('checkout.store'), [
                'name' => 'Ahmed Ali',
                'phone' => '0612345678',
                'address' => '123 Main St',
                'delivery_zone_id' => $zone->id,
                'payment_method' => 'cod',
            ]);

        $customer = User::where('phone', '0612345678')->firstOrFail();

        $secondCart = $this->createCartWithProduct();
        $this
            ->withSession(['cart_id' => $secondCart->id])
            ->post(route('checkout.store'), [
                'name' => 'Ahmed Ali',
                'phone' => '0612345678',
                'address' => '456 Second St',
                'delivery_zone_id' => $zone->id,
                'payment_method' => 'cod',
            ]);

        $this->assertSame(1, User::where('phone', '0612345678')->count());
        $this->assertSame(2, Order::where('user_id', $customer->id)->count());
    }

    public function test_registration_can_claim_a_guest_checkout_customer(): void
    {
        $customer = User::factory()->guest()->create([
            'name' => 'Guest Customer',
            'phone' => '0612345678',
        ]);

        $response = $this->post(route('register.post'), [
            'name' => 'Ahmed Ali',
            'phone' => '06 12 34 56 78',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('home'));

        $customer->refresh();

        $this->assertFalse($customer->is_guest);
        $this->assertSame('Ahmed Ali', $customer->name);
        $this->assertTrue(Hash::check('password123', $customer->password));
        $this->assertAuthenticatedAs($customer);
    }

    public function test_confirmation_page_shows_whatsapp_link_and_ready_message(): void
    {
        WhatsappSession::create([
            'name' => 'Main WhatsApp',
            'instance_id' => 'instance-1',
            'api_token' => 'token',
            'phone_number' => '+201000000000',
            'status' => WhatsappSessionStatus::Connected,
            'connected_at' => now(),
        ]);

        $zone = $this->createDeliveryZone();
        $cart = $this->createCartWithProduct();

        $response = $this
            ->withSession(['cart_id' => $cart->id])
            ->post(route('checkout.store'), [
                'name' => 'Ahmed Ali',
                'phone' => '0612345678',
                'address' => '123 Main St',
                'delivery_zone_id' => $zone->id,
                'payment_method' => 'cod',
            ]);

        $order = Order::firstOrFail();

        $this->get($response->headers->get('Location'))
            ->assertOk()
            ->assertSee("لتأكيد الاوردر رقم: {$order->order_number}")
            ->assertSee('https://wa.me/201000000000', false);
    }

    private function createDeliveryZone(): DeliveryZone
    {
        $provider = DeliveryProvider::create([
            'name' => 'Ozone Express',
            'slug' => 'ozone-' . Str::random(8),
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

    private function createCartWithProduct(): Cart
    {
        $category = Category::create([
            'name' => 'Dresses',
            'slug' => 'dresses-' . Str::random(8),
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Abaya',
            'slug' => 'abaya-' . Str::random(8),
            'description' => 'Test product',
            'selling_price' => 100,
            'thumbnail' => 'thumb.jpg',
            'image' => 'image.jpg',
            'has_variants' => false,
            'is_active' => true,
        ]);

        $skuCode = SkuCode::create([
            'skuable_type' => Product::class,
            'skuable_id' => $product->id,
            'sku_code' => 'SKU-' . Str::upper(Str::random(8)),
        ]);

        $cart = Cart::create([
            'session_id' => Str::uuid()->toString(),
        ]);

        CartItem::create([
            'cart_id' => $cart->id,
            'sku_code' => $skuCode->id,
            'quantity' => 1,
        ]);

        return $cart;
    }
}
