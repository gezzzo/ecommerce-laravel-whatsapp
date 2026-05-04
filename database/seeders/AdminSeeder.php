<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Developer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Admin::create(
            ['email' => 'admin@example.com',
                'name' => 'Admin User',
                'password' => Hash::make('password'),
            ]
        );
    }
}
