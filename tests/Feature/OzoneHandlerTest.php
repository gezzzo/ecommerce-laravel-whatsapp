<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\DeliveryCompany;
use App\Models\DeliveryProvider;
use App\Models\DeliveryZone;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\SkuCode;
use App\Models\TrackingParcel;
use App\Services\Delivery\DeliveryGatewayService;
use App\Services\Delivery\Handlers\OzoneHandler;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OzoneHandlerTest extends TestCase
{
    use LazilyRefreshDatabase;

    private DeliveryProvider $provider;

    private DeliveryCompany $company;

    private DeliveryZone $zone;

    private Order $order;

    protected function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();

        Model::unguarded(function () {
            $this->provider = DeliveryProvider::create([
                'name' => 'Ozone Express',
                'slug' => 'ozone',
                'base_url' => 'https://api.ozonexpress.ma/customers',
                'is_active' => true,
            ]);

            $this->company = DeliveryCompany::create([
                'delivery_provider_id' => $this->provider->id,
                'api_token' => 'test-api-token',
                'client_key' => 'test-client-key',
                'is_active' => true,
            ]);

            $this->zone = DeliveryZone::create([
                'delivery_company_id' => $this->company->id,
                'city' => 'Casablanca',
                'delivery_fee' => 30.00,
                'external_city_id' => '42',
                'visible' => true,
            ]);

            $category = Category::create([
                'name' => 'Test Category',
                'slug' => 'test-category',
            ]);

            $product = Product::create([
                'category_id' => $category->id,
                'name' => 'Test Product',
                'slug' => 'test-product',
                'description' => 'Test product description',
                'selling_price' => 100.00,
                'thumbnail' => 'test-thumb.jpg',
                'image' => 'test-image.jpg',
                'has_variants' => false,
                'is_active' => true,
            ]);

            $skuCode = SkuCode::create([
                'skuable_type' => Product::class,
                'skuable_id' => $product->id,
                'sku_code' => 'TST-001',
            ]);

            $this->order = Order::create([
                'order_number' => 'ORD-001',
                'name' => 'Ahmed Ali',
                'phone' => '0612345678',
                'address' => '123 Main St',
                'city' => 'Casablanca',
                'subtotal' => 100.00,
                'total' => 130.00,
                'delivery_zone_id' => $this->zone->id,
                'delivery_company_id' => $this->company->id,
            ]);

            OrderItem::create([
                'order_id' => $this->order->id,
                'sku_code' => $skuCode->id,
                'price' => 100.00,
                'quantity' => 1,
            ]);
        });
    }

    public function test_gateway_resolves_ozone_handler(): void
    {
        $gateway = new DeliveryGatewayService();
        $handler = $gateway->getHandler($this->company);

        $this->assertInstanceOf(OzoneHandler::class, $handler);
    }

    public function test_gateway_throws_for_unsupported_provider(): void
    {
        $unknownProvider = DeliveryProvider::create([
            'name' => 'Unknown',
            'slug' => 'unknown-provider',
            'base_url' => 'https://example.com',
        ]);

        $unknownCompany = DeliveryCompany::create([
            'delivery_provider_id' => $unknownProvider->id,
            'api_token' => 'token',
            'client_key' => 'key',
        ]);

        $gateway = new DeliveryGatewayService();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unsupported delivery provider');
        $gateway->getHandler($unknownCompany);
    }

    public function test_send_order_successfully(): void
    {
        Http::fake([
            'api.ozonexpress.ma/*' => Http::response(json_encode([
                'ADD-PARCEL' => [
                    'RESULT' => 'OK',
                    'NEW-PARCEL' => [
                        'TRACKING-NUMBER' => 'OZ-123456',
                    ],
                ],
            ])),
        ]);

        $handler = new OzoneHandler();
        $result = $handler->send($this->order, $this->company);

        $this->assertArrayHasKey('ADD-PARCEL', $result);
        $this->assertEquals('OZ-123456', $result['ADD-PARCEL']['NEW-PARCEL']['TRACKING-NUMBER']);

        $this->order->refresh();
        $this->assertEquals('OZ-123456', $this->order->tracking_number);
        $this->assertEquals('Sent', $this->order->delivery_status);

        $this->assertDatabaseHas('tracking_parcels', [
            'order_id' => $this->order->id,
            'parcel_code' => 'OZ-123456',
            'statut_name' => 'Sent',
        ]);
    }

    public function test_send_throws_on_missing_credentials(): void
    {
        $companyNoCredentials = DeliveryCompany::create([
            'delivery_provider_id' => $this->provider->id,
            'api_token' => null,
            'client_key' => null,
        ]);

        $handler = new OzoneHandler();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Missing OZone credentials');
        $handler->send($this->order, $companyNoCredentials);
    }

    public function test_send_throws_on_missing_delivery_zone(): void
    {
        $orderNoZone = Order::create([
            'order_number' => 'ORD-002',
            'name' => 'No Zone',
            'phone' => '0600000000',
            'address' => 'Nowhere',
            'city' => 'Unknown',
            'subtotal' => 50.00,
            'total' => 50.00,
        ]);

        $handler = new OzoneHandler();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Invalid or missing delivery zone');
        $handler->send($orderNoZone, $this->company);
    }

    public function test_send_throws_on_http_failure(): void
    {
        Http::fake([
            'api.ozonexpress.ma/*' => Http::response('Server Error', 500),
        ]);

        $handler = new OzoneHandler();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('OZone HTTP error: HTTP 500');
        $handler->send($this->order, $this->company);
    }

    public function test_send_throws_on_api_key_error(): void
    {
        Http::fake([
            'api.ozonexpress.ma/*' => Http::response(json_encode([
                'CHECK_API' => [
                    'RESULT' => 'ERROR',
                    'MESSAGE' => 'Invalid API key',
                ],
            ])),
        ]);

        $handler = new OzoneHandler();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('OZone API error: Invalid API key');
        $handler->send($this->order, $this->company);
    }

    public function test_send_throws_on_parcel_error(): void
    {
        Http::fake([
            'api.ozonexpress.ma/*' => Http::response(json_encode([
                'ADD-PARCEL' => [
                    'RESULT' => 'ERROR',
                    'MESSAGE' => 'Phone number invalid',
                ],
            ])),
        ]);

        $handler = new OzoneHandler();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Phone number invalid');
        $handler->send($this->order, $this->company);
    }

    public function test_send_handles_duplicated_json_response(): void
    {
        $responseBody = json_encode([
            'ADD-PARCEL' => [
                'RESULT' => 'OK',
                'NEW-PARCEL' => ['TRACKING-NUMBER' => 'OZ-DUP-001'],
            ],
        ]);
        $duplicatedBody = $responseBody . $responseBody;

        Http::fake([
            'api.ozonexpress.ma/*' => Http::response($duplicatedBody),
        ]);

        $handler = new OzoneHandler();
        $result = $handler->send($this->order, $this->company);

        $this->order->refresh();
        $this->assertEquals('OZ-DUP-001', $this->order->tracking_number);
    }

    public function test_track_order_successfully(): void
    {
        $this->order->update(['tracking_number' => 'OZ-TRACK-001']);

        Http::fake([
            'api.ozonexpress.ma/*' => Http::response([
                'TRACKING' => [
                    'TRACKING-NUMBER' => 'OZ-TRACK-001',
                    'LAST_TRACKING' => ['STATUT' => 'Delivered'],
                    'HISTORY' => [
                        [
                            'STATUT' => 'Picked Up',
                            'TIME_STR' => '2026-04-30 10:00:00',
                            'COMMENT' => 'Package picked up',
                        ],
                        [
                            'STATUT' => 'Delivered',
                            'TIME_STR' => '2026-04-30 14:00:00',
                            'COMMENT' => 'Delivered to customer',
                        ],
                    ],
                ],
            ]),
        ]);

        $handler = new OzoneHandler();
        $status = $handler->track($this->order, $this->company);

        $this->assertEquals('Delivered', $status);

        $this->order->refresh();
        $this->assertEquals('Delivered', $this->order->delivery_status);

        $this->assertDatabaseHas('tracking_parcels', [
            'order_id' => $this->order->id,
            'statut_name' => 'Picked Up',
        ]);

        $this->assertDatabaseHas('tracking_parcels', [
            'order_id' => $this->order->id,
            'statut_name' => 'Delivered',
        ]);
    }

    public function test_track_skips_update_when_manual_delivery_status(): void
    {
        $this->order->update([
            'tracking_number' => 'OZ-MANUAL-001',
            'delivery_status' => 'CustomStatus',
            'manual_delivery_status' => true,
        ]);

        Http::fake([
            'api.ozonexpress.ma/*' => Http::response([
                'TRACKING' => [
                    'TRACKING-NUMBER' => 'OZ-MANUAL-001',
                    'LAST_TRACKING' => ['STATUT' => 'Delivered'],
                    'HISTORY' => [],
                ],
            ]),
        ]);

        $handler = new OzoneHandler();
        $status = $handler->track($this->order, $this->company);

        $this->assertEquals('Delivered', $status);

        $this->order->refresh();
        $this->assertEquals('CustomStatus', $this->order->delivery_status);
    }

    public function test_track_throws_without_tracking_number(): void
    {
        $handler = new OzoneHandler();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Missing delivery tracking number');
        $handler->track($this->order, $this->company);
    }

    public function test_track_throws_on_http_failure(): void
    {
        $this->order->update(['tracking_number' => 'OZ-FAIL-001']);

        Http::fake([
            'api.ozonexpress.ma/*' => Http::response('Error', 502),
        ]);

        $handler = new OzoneHandler();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('OZone tracking error: HTTP 502');
        $handler->track($this->order, $this->company);
    }

    public function test_does_not_duplicate_tracking_entries(): void
    {
        $this->order->update(['tracking_number' => 'OZ-NODUP-001']);

        TrackingParcel::create([
            'order_id' => $this->order->id,
            'parcel_code' => 'OZ-NODUP-001',
            'statut_name' => 'Picked Up',
            'time' => '2026-04-30 10:00:00',
        ]);

        Http::fake([
            'api.ozonexpress.ma/*' => Http::response([
                'TRACKING' => [
                    'TRACKING-NUMBER' => 'OZ-NODUP-001',
                    'LAST_TRACKING' => ['STATUT' => 'Picked Up'],
                    'HISTORY' => [
                        [
                            'STATUT' => 'Picked Up',
                            'TIME_STR' => '2026-04-30 10:00:00',
                            'COMMENT' => 'Already exists',
                        ],
                    ],
                ],
            ]),
        ]);

        $handler = new OzoneHandler();
        $handler->track($this->order, $this->company);

        $count = TrackingParcel::where('order_id', $this->order->id)
            ->where('statut_name', 'Picked Up')
            ->count();

        $this->assertEquals(1, $count);
    }
}
