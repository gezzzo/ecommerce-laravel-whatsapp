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
        Schema::create('order_returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('order_item_id')->constrained('order_items')->onDelete('cascade');
            $table->integer('quantity'); // الكمية المرتجعة
            $table->decimal('refund_amount', 10, 2)->default(0); // مبلغ الاسترداد
            $table->enum('status', ['pending', 'approved', 'rejected', 'completed'])->default('pending');
            $table->text('reason')->nullable(); // سبب الإرجاع
            $table->text('admin_notes')->nullable(); // ملاحظات الأدمن
            $table->foreignId('admin_id')->nullable()->constrained('admins')->nullOnDelete(); // الأدمن الذي قام بالمعالجة
            $table->boolean('inventory_restored')->default(false); // هل تم إرجاع المخزون
            $table->timestamp('processed_at')->nullable(); // تاريخ المعالجة
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_returns');
    }
};
