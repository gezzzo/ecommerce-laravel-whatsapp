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
        Schema::create('whatsapp_inbox_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('whatsapp_session_id')->nullable()->constrained()->nullOnDelete();
            $table->string('message_id')->unique()->comment('WhatsApp message ID from MegaMsg');
            $table->string('from')->index()->comment('Sender phone number without @s.whatsapp.net');
            $table->string('push_name')->nullable()->comment("Sender's WhatsApp display name");
            $table->text('text')->nullable()->comment('Message text content');
            $table->enum('message_type', ['text', 'image', 'audio', 'video', 'document', 'sticker', 'location', 'contact', 'unknown'])->default('text');
            $table->boolean('is_group')->default(false);
            $table->boolean('is_read')->default(false);
            $table->json('raw_payload')->nullable()->comment('Full raw webhook payload for debugging');
            $table->timestamp('received_at')->useCurrent();
            $table->timestamps();

            $table->index(['from', 'received_at']);
            $table->index(['whatsapp_session_id', 'is_read']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_inbox_messages');
    }
};
