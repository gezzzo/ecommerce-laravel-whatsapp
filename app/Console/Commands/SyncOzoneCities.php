<?php

namespace App\Console\Commands;

use App\Models\DeliveryCompany;
use App\Models\DeliveryZone;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SyncOzoneCities extends Command
{
    protected $signature = 'delivery:sync-ozone';

    protected $description = 'Sync cities from OZone Express API';

    public function handle(): int
    {
        $company = DeliveryCompany::whereHas('provider', function ($query) {
            $query->whereRaw('LOWER(slug) = ?', ['ozone']);
        })->first();

        if (! $company) {
            $this->error('OZone company not found. Please create a DeliveryCompany linked to the "ozone" provider first.');

            return self::FAILURE;
        }

        $this->info('Fetching cities from OZone Express...');

        try {
            $response = Http::timeout(15)
                ->connectTimeout(5)
                ->retry([500, 1000], throw: false)
                ->withHeaders(['Accept' => 'application/json'])
                ->get('https://api.ozonexpress.ma/cities');

            if (! $response->successful()) {
                $this->error('API call failed: ' . $response->body());
                Log::error('[OZone Sync] API call failed', ['body' => $response->body()]);

                return self::FAILURE;
            }

            $cities = $response->json('CITIES');

            if (! is_array($cities)) {
                $this->error('Unexpected response format: CITIES key not found');
                Log::error('[OZone Sync] Unexpected response format', ['body' => $response->body()]);

                return self::FAILURE;
            }

            $syncedIds = [];

            foreach ($cities as $city) {
                $zone = DeliveryZone::firstOrCreate(
                    [
                        'delivery_company_id' => $company->id,
                        'city' => $city['NAME'],
                        'external_city_id' => $city['ID'],
                    ],
                    [
                        'delivery_fee' => $city['DELIVERED-PRICE'] ?? 0,
                        'visible' => true,
                    ]
                );

                $zone->update(['visible' => true]);
                $syncedIds[] = $zone->id;
            }

            // Hide zones that no longer exist in the API
            $hiddenCount = DeliveryZone::where('delivery_company_id', $company->id)
                ->whereNotIn('id', $syncedIds)
                ->update(['visible' => false]);

            $this->info("Synced " . count($syncedIds) . " cities. Hidden {$hiddenCount} removed cities.");
            Log::info('[OZone Sync] Cities synced', [
                'synced' => count($syncedIds),
                'hidden' => $hiddenCount,
            ]);

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Failed: ' . $e->getMessage());
            Log::error('[OZone Sync] Exception', ['error' => $e->getMessage()]);

            return self::FAILURE;
        }
    }
}
