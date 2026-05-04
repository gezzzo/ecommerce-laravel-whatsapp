<?php

namespace Database\Seeders;

use App\Models\Size;
use Illuminate\Database\Seeder;

class SizeSeeder extends Seeder
{
    /**
     * Seed the sizes from the old system's JSON export.
     */
    public function run(): void
    {
        $json = json_decode(
            file_get_contents(database_path('seeders/sizes.json')),
            true,
        );

        foreach ($json['sizes'] as $sizeData) {
            Size::create([
                'name' => $sizeData['name'],
                'type' => $sizeData['type'],
            ]);
        }

        $this->command->info('Seeded ' . count($json['sizes']) . ' sizes.');
    }
}
