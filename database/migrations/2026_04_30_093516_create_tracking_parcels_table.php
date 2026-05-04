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
        Schema::create('tracking_parcels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('parcel_code')->nullable();
            $table->string('statut_name');
            $table->string('statut_color')->default('#FFFFFF');
            $table->string('situation_name')->nullable();
            $table->string('situation_color')->default('#FFFFFF');
            $table->string('livreur')->nullable();
            $table->text('commentaire')->nullable();
            $table->timestamp('time')->nullable();
            $table->timestamps();

            $table->index(['order_id', 'statut_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tracking_parcels');
    }
};
