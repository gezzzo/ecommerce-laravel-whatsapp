<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('store_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, boolean, integer, json
            $table->string('group')->default('general');
            $table->timestamps();
        });

        // Seed default settings
        DB::table('store_settings')->insert([
            [
                'key'   => 'checkout_mode',
                'value' => 'optional',
                'type'  => 'string',
                'group' => 'checkout',
            ],
            [
                'key'   => 'store_name',
                'value' => 'متجري',
                'type'  => 'string',
                'group' => 'general',
            ],
            [
                'key'   => 'free_shipping_threshold',
                'value' => '200',
                'type'  => 'integer',
                'group' => 'shipping',
            ],
            [
                'key'   => 'shipping_cost',
                'value' => '30',
                'type'  => 'integer',
                'group' => 'shipping',
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('store_settings');
    }
};

