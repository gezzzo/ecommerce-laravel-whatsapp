<?php

namespace Tests\Feature;

use App\Enums\WhatsappSessionStatus;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\SkuCode;
use App\Models\TrackingParcel;
use App\Models\WhatsappSession;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class WhatsappOrderConfirmationWebhookTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_webhook_confirms_order_and_replies_with_order_details(): void
    {
        Http::fake([
            'megamsg.app/*' => Http::response(['ok' => true], 200),
        ]);

        $session = WhatsappSession::create([
            'name' => 'Main WhatsApp',
            'instance_id' => 'instance-1',
            'api_token' => 'test-token',
            'phone_number' => '+201000000000',
            'status' => WhatsappSessionStatus::Connected,
            'connected_at' => now(),
        ]);

        $order = $this->createOrder();

        $response = $this->postJson(route('api.whatsapp.webhook', ['instanceId' => $session->instance_id]), [
            'type' => 'message',
            'message' => [
                'id' => 'message-1',
                'from' => '201234567890@s.whatsapp.net',
                'push_name' => 'Ahmed',
                'text' => "لتأكيد الاوردر رقم: {$order->order_number}",
                'type' => 'text',
                'is_group' => false,
            ],
        ]);

        $response->assertOk();

        $order->refresh();

        $this->assertSame('201234567890', $order->whatsapp_phone);
        $this->assertSame('confirmed', $order->status);
        $this->assertNotNull($order->whatsapp_confirmed_at);
        $this->assertSame('message-1', $order->whatsapp_confirmation_message_id);

        $this->assertDatabaseHas('whatsapp_inbox_messages', [
            'message_id' => 'message-1',
            'is_read' => true,
        ]);

        Http::assertSent(fn ($request): bool => $request->url() === 'https://megamsg.app/api/whatsapp/messages/send'
            && $request['phone_number'] === '+201234567890'
            && str_contains($request['text'], "رقم الطلب: {$order->order_number}")
            && str_contains($request['text'], "لتتبع الاوردر الخاص بك ارسل لنا هذا الكود فقط: {$order->order_number}")
            && str_contains($request['text'], 'الإجمالي: 130.00 درهم'));
    }

    public function test_webhook_replies_with_tracking_when_message_contains_order_code(): void
    {
        Http::fake([
            'megamsg.app/*' => Http::response(['ok' => true], 200),
        ]);

        $session = WhatsappSession::create([
            'name' => 'Main WhatsApp',
            'instance_id' => 'instance-1',
            'api_token' => 'test-token',
            'phone_number' => '+201000000000',
            'status' => WhatsappSessionStatus::Connected,
            'connected_at' => now(),
        ]);

        $order = $this->createOrder();
        $order->update([
            'delivery_status' => 'shipped',
            'tracking_number' => 'OZ-TRACK-001',
        ]);

        TrackingParcel::create([
            'order_id' => $order->id,
            'parcel_code' => 'OZ-TRACK-001',
            'statut_name' => 'Shipped',
            'situation_name' => 'In transit',
            'time' => now(),
        ]);

        $response = $this->postJson(route('api.whatsapp.webhook', ['instanceId' => $session->instance_id]), [
            'type' => 'message',
            'message' => [
                'id' => 'message-track-1',
                'from' => '201234567890@s.whatsapp.net',
                'push_name' => 'Ahmed',
                'text' => $order->order_number,
                'type' => 'text',
                'is_group' => false,
            ],
        ]);

        $response->assertOk();

        $order->refresh();

        $this->assertNull($order->whatsapp_confirmed_at);

        $this->assertDatabaseHas('whatsapp_inbox_messages', [
            'message_id' => 'message-track-1',
            'is_read' => true,
        ]);

        Http::assertSent(fn ($request): bool => $request->url() === 'https://megamsg.app/api/whatsapp/messages/send'
            && $request['phone_number'] === '+201234567890'
            && str_contains($request['text'], 'تتبع طلبك')
            && str_contains($request['text'], "رقم الطلب: {$order->order_number}")
            && str_contains($request['text'], 'رقم الشحنة: OZ-TRACK-001')
            && str_contains($request['text'], 'آخر تحديث: Shipped'));
    }

    public function test_webhook_stores_non_confirmation_messages_without_replying(): void
    {
        Http::fake();

        $session = WhatsappSession::create([
            'name' => 'Main WhatsApp',
            'instance_id' => 'instance-1',
            'api_token' => 'test-token',
            'phone_number' => '+201000000000',
            'status' => WhatsappSessionStatus::Connected,
            'connected_at' => now(),
        ]);

        $response = $this->postJson(route('api.whatsapp.webhook', ['instanceId' => $session->instance_id]), [
            'type' => 'message',
            'message' => [
                'id' => 'message-2',
                'from' => '201234567890@s.whatsapp.net',
                'push_name' => 'Ahmed',
                'text' => 'السلام عليكم',
                'type' => 'text',
                'is_group' => false,
            ],
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('whatsapp_inbox_messages', [
            'message_id' => 'message-2',
            'is_read' => false,
        ]);

        Http::assertNothingSent();
    }

    private function createOrder(): Order
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
            'selling_price' => 100,
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

        $order = Order::create([
            'order_number' => 'ORD-'.Str::upper(Str::random(10)),
            'name' => 'Ahmed Ali',
            'phone' => '0612345678',
            'address' => '123 Main St',
            'city' => 'Casablanca',
            'subtotal' => 100,
            'shipping' => 30,
            'discount' => 0,
            'total' => 130,
            'payment_method' => 'cod',
            'payment_status' => 'not_paid',
            'delivery_status' => 'pending',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'sku_code' => $skuCode->id,
            'price' => 100,
            'quantity' => 1,
        ]);

        return $order;
    }
}
