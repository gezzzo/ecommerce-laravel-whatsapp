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
        Schema::create('whatsapp_campaign_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('whatsapp_campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('whatsapp_campaign_contact_id')->nullable()->constrained()->nullOnDelete();
            $table->string('phone')->nullable();
            $table->enum('event', ['sent', 'delivered', 'read', 'failed', 'info']);
            $table->text('message')->nullable();
            $table->json('payload')->nullable();
            $table->timestamp('happened_at')->useCurrent();
            $table->timestamps();

            $table->index(['whatsapp_campaign_id', 'event']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_campaign_logs');
    }
};
