<?php

namespace Database\Seeders;

use App\Models\Developer;
use Illuminate\Database\Seeder;

class DeveloperSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Developer::updateOrCreate(
            ['email' => 'developer@example.com'],
            [
                'name' => 'Developer User',
                'password' => 'password',
            ]
        );
    }
}
