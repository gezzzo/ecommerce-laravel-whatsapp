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
        Schema::table('order_histories', function (Blueprint $table) {
            $table->string('action_type')->nullable()->after('admin_id');
            $table->text('old_value')->nullable()->after('action_type');
            $table->text('new_value')->nullable()->after('old_value');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_histories', function (Blueprint $table) {
            $table->dropColumn(['action_type', 'old_value', 'new_value']);
        });
    }
};
