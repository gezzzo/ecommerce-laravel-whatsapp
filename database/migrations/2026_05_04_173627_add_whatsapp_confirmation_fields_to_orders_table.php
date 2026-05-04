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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('whatsapp_phone')->nullable()->after('phone')->index();
            $table->timestamp('whatsapp_confirmed_at')->nullable()->after('whatsapp_phone')->index();
            $table->string('whatsapp_confirmation_message_id')->nullable()->after('whatsapp_confirmed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'whatsapp_phone',
                'whatsapp_confirmed_at',
                'whatsapp_confirmation_message_id',
            ]);
        });
    }
};
