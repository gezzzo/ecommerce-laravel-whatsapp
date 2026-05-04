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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnUpdate();
            $table->string('order_number')->unique();
            $table->string('name');
            $table->string('phone');
            $table->string('address');
            $table->string('city');
            $table->text('comment')->nullable();
            $table->string('status')->nullable();
            $table->string('delivery_status')->nullable();
            $table->enum('payment_status', ['paid', 'not_paid'])->default('not_paid');
            $table->enum('payment_method', ['cod','card','wallet'])->default('cod');
            $table->boolean('manual_delivery_status')->default(false);
            $table->foreignId('coupon_id')->nullable()->constrained()->cascadeOnUpdate();
            $table->decimal('subtotal', 10, 2);
            $table->decimal('shipping', 8, 2)->default(0);
            $table->decimal('discount', 8, 2)->default(0);
            $table->decimal('total', 10, 2);
            $table->string('tracking_number')->nullable();
            $table->string('coupon_code')->nullable();
            $table->foreignId('delivery_zone_id')->nullable()->constrained()->cascadeOnUpdate();
            $table->foreignId('delivery_company_id')->nullable()->constrained()->cascadeOnUpdate();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
