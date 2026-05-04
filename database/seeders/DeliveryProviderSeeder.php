<?php

namespace Database\Seeders;

use App\Models\DeliveryProvider;
use Illuminate\Database\Seeder;

class DeliveryProviderSeeder extends Seeder
{
    /**
     * Seed the delivery providers table.
     */
    public function run(): void
    {
        $providers = [
            ['name' => 'Ozone Express',   'slug' => 'ozone',         'is_active' => true, 'base_url' => 'https://api.ozonexpress.ma/customers'],
            ['name' => 'SK System',       'slug' => 'sksystem',        'is_active' => false, 'base_url' => 'https://sksystem.ma/ApiParcelsTrans/public/api/api-client'],
            ['name' => 'Safed Express',   'slug' => 'safedexpress',    'is_active' => false, 'base_url' => 'https://clients.safedexpress.com/api/client'],
            ['name' => 'Rapid Delivery',  'slug' => 'rapiddelivery',   'is_active' => false, 'base_url' => 'https://www.rapiddelivery.ma/api/v1'],
            ['name' => 'Atlas Livraison', 'slug' => 'atlaslivraison',  'is_active' => false, 'base_url' => 'https://atlaslivraison.ma'],
            ['name' => 'Olivraison',      'slug' => 'olivraison',      'is_active' => false, 'base_url' => 'https://partners.olivraison.com'],
            ['name' => 'ForceLog',        'slug' => 'forcelog',        'is_active' => false, 'base_url' => 'https://api.forcelog.ma'],
            ['name' => 'Irsaliyat',       'slug' => 'irsaliyat',       'is_active' => false, 'base_url' => 'https://irsaliyat.ma'],
        ];

        foreach ($providers as $provider) {
            DeliveryProvider::firstOrCreate(
                ['slug' => $provider['slug']],
                $provider
            );
        }
    }
}
