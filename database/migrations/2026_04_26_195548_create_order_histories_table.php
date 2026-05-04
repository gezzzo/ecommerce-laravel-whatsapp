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
        Schema::create('order_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade'); // Tracks the affected order
            $table->string('status')->nullable();
            $table->text('delivery_status')->nullable(); // Updated delivery status (nullable at start)
            $table->enum('payment_status', ['paid', 'not_paid'])->nullable(); // Updated payment status (nullable at start)
            $table->unsignedBigInteger('admin_id')->nullable();
            $table->text('comment')->nullable(); // Optional comment for the change
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_histories');
    }
};
