<?php

namespace Database\Seeders;

use App\Models\Color;
use Illuminate\Database\Seeder;

class ColorSeeder extends Seeder
{
    /**
     * Seed the colors from the old system's JSON export.
     */
    public function run(): void
    {
        $json = json_decode(
            file_get_contents(database_path('seeders/colors.json')),
            true,
        );

        foreach ($json['colors'] as $colorData) {
            Color::create([
                'name' => $colorData['name'],
                'hex_code' => $colorData['hex_code'],
            ]);
        }

        $this->command->info('Seeded ' . count($json['colors']) . ' colors.');
    }
}
