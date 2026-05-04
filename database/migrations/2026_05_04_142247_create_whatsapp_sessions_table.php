<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('whatsapp_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('instance_id')->unique()->comment('MegaMsg Instance ID from dashboard');
            $table->longText('api_token')->comment('MegaMsg Bearer Token from dashboard');
            $table->string('phone_number')->nullable()->comment('Connected WhatsApp number');
            $table->enum('status', ['disconnected', 'connected'])->default('disconnected');
            $table->timestamp('connected_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_sessions');
    }
};
