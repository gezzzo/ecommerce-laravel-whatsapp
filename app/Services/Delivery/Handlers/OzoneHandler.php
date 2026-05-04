<?php

namespace App\Services\Delivery\Handlers;

use App\Models\DeliveryCompany;
use App\Models\Order;
use App\Models\Product;
use App\Models\TrackingParcel;
use App\Models\Variant;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OzoneHandler implements DeliveryHandlerInterface
{
    /**
     * Send an order to OZone Express.
     *
     * @return array<string, mixed>
     */
    public function send(Order $order, DeliveryCompany $company): array
    {
        $this->validateCredentials($company);

        $zone = $order->deliveryZone;
        if (! $zone || ! $zone->external_city_id) {
            throw new \RuntimeException('Invalid or missing delivery zone (external_city_id).');
        }

        Log::info('[OZone] Sending order', [
            'order_id' => $order->id,
            'zone' => $zone->city ?? 'N/A',
        ]);

        $order->loadMissing('items.skuCode.skuable');

        [$productDescriptions, $productRefs] = $this->buildProductPayload($order);

        $payload = [
            'parcel-receiver' => $order->name,
            'parcel-phone' => $order->phone,
            'parcel-city' => $zone->external_city_id,
            'parcel-address' => $order->address,
            'parcel-note' => $order->comment,
            'parcel-price' => $order->total,
            'parcel-nature' => implode(' | ', $productDescriptions),
            'parcel-stock' => 0,
            'products' => json_encode($productRefs),
        ];

        $baseUrl = $company->provider->base_url ?? 'https://api.ozonexpress.ma/customers';
        $url = "{$baseUrl}/{$company->client_key}/{$company->api_token}/add-parcel";

        try {
            $response = Http::asForm()
                ->timeout(15)
                ->connectTimeout(5)
                ->retry([500, 1000], throw: false)
                ->post($url, $payload);

            if (! $response->successful()) {
                Log::error('[OZone] HTTP request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'payload' => $payload,
                ]);
                throw new \RuntimeException('OZone HTTP error: HTTP ' . $response->status());
            }

            $data = $this->parseResponse($response->body());

            if (! is_array($data)) {
                Log::error('[OZone] Invalid JSON response', [
                    'response_body' => $response->body(),
                    'payload' => $payload,
                ]);
                throw new \RuntimeException('OZone HTTP Invalid JSON response');
            }

            $this->checkApiKeyError($data, $payload);
            $trackingNumber = $this->extractTrackingNumber($data, $order);

            $this->recordInitialTracking($order, $trackingNumber);

            Log::info('[OZone] Order sent successfully', $data);

            return $data;
        } catch (\Exception $e) {
            Log::error('[OZone] Exception occurred', [
                'error' => $e->getMessage(),
                'payload' => $payload,
                'order_id' => $order->id,
            ]);
            throw $e;
        }
    }

    /**
     * Track an order via OZone Express.
     */
    public function track(Order $order, DeliveryCompany $company): string
    {
        if (! $order->tracking_number) {
            throw new \RuntimeException('Missing delivery tracking number for OZone.');
        }

        $baseUrl = $company->provider->base_url ?? 'https://api.ozonexpress.ma/customers';
        $url = "{$baseUrl}/{$company->client_key}/{$company->api_token}/tracking";

        try {
            $response = Http::asForm()
                ->timeout(15)
                ->connectTimeout(5)
                ->retry([500, 1000], throw: false)
                ->post($url, [
                    'tracking-number' => $order->tracking_number,
                ]);

            if (! $response->successful()) {
                Log::warning('[OZone] Failed to track order', [
                    'tracking_number' => $order->tracking_number,
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);
                throw new \RuntimeException('OZone tracking error: HTTP ' . $response->status());
            }

            $data = $response->json();

            if (! is_array($data)) {
                Log::warning('[OZone] Invalid tracking response', [
                    'tracking_number' => $order->tracking_number,
                    'response' => $response->body(),
                ]);
                throw new \RuntimeException('OZone tracking error: Invalid response format');
            }

            Log::info('[OZone] Tracking Response:', $data);

            $this->saveTrackingHistory($order, $data);

            $latestStatus = $data['TRACKING']['LAST_TRACKING']['STATUT'] ?? 'Unknown';

            if (! $order->manual_delivery_status) {
                $order->update(['delivery_status' => $latestStatus]);
                Log::info("[OZone] Tracking updated for order ID {$order->id}", [
                    'latest_status' => $latestStatus,
                ]);
            } else {
                Log::info("[OZone] Skipping automatic update for order ID {$order->id} — manually set", [
                    'manual_status' => $order->delivery_status,
                    'external_status' => $latestStatus,
                ]);
            }

            return $latestStatus;
        } catch (\Exception $e) {
            Log::error('[OZone] Tracking exception', [
                'error' => $e->getMessage(),
                'tracking_number' => $order->tracking_number,
                'order_id' => $order->id,
            ]);
            throw $e;
        }
    }

    /**
     * Validate that the required API credentials are present.
     */
    private function validateCredentials(DeliveryCompany $company): void
    {
        if (! $company->api_token || ! $company->client_key) {
            throw new \RuntimeException('Missing OZone credentials (api_token / client_key).');
        }
    }

    /**
     * Build the product descriptions and refs from order items.
     *
     * @return array{0: list<string>, 1: list<array{ref: string, qnty: int}>}
     */
    private function buildProductPayload(Order $order): array
    {
        $descriptions = [];
        $refs = [];

        foreach ($order->items as $item) {
            $skuCode = $item->skuCode;

            if (! $skuCode || ! $skuCode->skuable) {
                $descriptions[] = 'منتج';
                $refs[] = ['ref' => 'N/A', 'qnty' => $item->quantity ?? 1];

                continue;
            }

            $skuable = $skuCode->skuable;

            if ($skuable instanceof Product) {
                $descriptions[] = $skuable->name;
            } elseif ($skuable instanceof Variant) {
                $descriptions[] = $skuable->product->name
                    . ' - مقاس: ' . ($skuable->size->name ?? 'N/A')
                    . ' - لون: ' . ($skuable->color->name ?? 'N/A')
                    . ' - الكمية: ' . $item->quantity;
            }

            $refs[] = [
                'ref' => $skuCode->sku_code ?? 'N/A',
                'qnty' => $item->quantity ?? 1,
            ];
        }

        return [$descriptions, $refs];
    }

    /**
     * Parse and clean possibly duplicated JSON response from OZone.
     *
     * @return array<string, mixed>|null
     */
    private function parseResponse(string $responseBody): ?array
    {
        if (str_contains($responseBody, '}{')) {
            $parts = explode('}{', $responseBody);
            $responseBody = $parts[0] . '}';
        }

        return json_decode($responseBody, true);
    }

    /**
     * Check for API key-level errors in the response.
     *
     * @param array<string, mixed> $data
     * @param array<string, mixed> $payload
     */
    private function checkApiKeyError(array $data, array $payload): void
    {
        if (isset($data['CHECK_API']['RESULT']) && $data['CHECK_API']['RESULT'] === 'ERROR') {
            Log::error('[OZone] API Key Error', [
                'payload' => $payload,
                'response' => $data,
            ]);
            throw new \RuntimeException('OZone API error: ' . ($data['CHECK_API']['MESSAGE'] ?? 'Unknown API error'));
        }
    }

    /**
     * Extract tracking number from the response and update the order.
     *
     * @param array<string, mixed> $data
     */
    private function extractTrackingNumber(array $data, Order $order): string
    {
        if (isset($data['ADD-PARCEL']['NEW-PARCEL']['TRACKING-NUMBER'])) {
            $trackingNumber = $data['ADD-PARCEL']['NEW-PARCEL']['TRACKING-NUMBER'];

            $order->update([
                'tracking_number' => $trackingNumber,
                'delivery_status' => 'Sent',
            ]);

            return $trackingNumber;
        }

        if (($data['ADD-PARCEL']['RESULT'] ?? null) === 'ERROR') {
            throw new \RuntimeException($data['ADD-PARCEL']['MESSAGE'] ?? 'Unknown error');
        }

        throw new \RuntimeException('Customer data is invalid');
    }

    /**
     * Record the initial "Sent" tracking entry.
     */
    private function recordInitialTracking(Order $order, string $trackingNumber): void
    {
        $alreadyExists = TrackingParcel::where('order_id', $order->id)
            ->where('statut_name', 'Sent')
            ->exists();

        if (! $alreadyExists) {
            TrackingParcel::create([
                'order_id' => $order->id,
                'parcel_code' => $trackingNumber,
                'statut_name' => 'Sent',
                'statut_color' => '#00FF00',
                'situation_name' => 'In Transit',
                'situation_color' => '#00FF00',
                'livreur' => null,
                'commentaire' => 'Order has been sent to OZone successfully',
                'time' => now(),
            ]);
        }
    }

    /**
     * Save tracking history entries from the API response.
     *
     * @param array<string, mixed> $data
     */
    private function saveTrackingHistory(Order $order, array $data): void
    {
        $history = $data['TRACKING']['HISTORY'] ?? [];

        if (! is_array($history)) {
            return;
        }

        foreach ($history as $entry) {
            if (! is_array($entry)) {
                continue;
            }

            $statutName = $entry['STATUT'] ?? 'Unknown';
            $timeStr = $entry['TIME_STR'] ?? now();

            $exists = TrackingParcel::where('order_id', $order->id)
                ->where('statut_name', $statutName)
                ->where('time', $timeStr)
                ->exists();

            if (! $exists) {
                TrackingParcel::create([
                    'order_id' => $order->id,
                    'parcel_code' => $data['TRACKING']['TRACKING-NUMBER'] ?? '',
                    'statut_name' => $statutName,
                    'statut_color' => '#FFFFFF',
                    'situation_name' => 'Tracking Updated',
                    'situation_color' => '#00FF00',
                    'livreur' => null,
                    'commentaire' => $entry['COMMENT'] ?? null,
                    'time' => $timeStr,
                ]);
            }
        }
    }
}
