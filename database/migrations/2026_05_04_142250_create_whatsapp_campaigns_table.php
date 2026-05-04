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
        Schema::create('whatsapp_campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('whatsapp_session_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('message');
            $table->string('media_url')->nullable();
            $table->enum('media_type', ['none', 'image', 'video', 'document', 'audio'])->default('none');
            $table->string('media_caption')->nullable();
            $table->enum('status', ['draft', 'scheduled', 'running', 'completed', 'failed', 'paused'])->default('draft');
            $table->timestamp('scheduled_at')->nullable();
            $table->unsignedInteger('delay_seconds')->default(3)->comment('Delay between messages in seconds');
            $table->unsignedInteger('total_contacts')->default(0);
            $table->unsignedInteger('sent_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_campaigns');
    }
};
