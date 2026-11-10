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
            $table->string('order_number', 24)->unique();
            $table->string('full_name');
            $table->string('email');
            $table->string('phone', 32);
            $table->string('delivery_address');
            $table->string('delivery_city');
            $table->text('customer_notes')->nullable();
            $table->string('status', 40)->default('awaiting_confirmation');
            $table->string('payment_status', 24)->default('unpaid');
            $table->char('currency', 3)->default('KES');
            $table->decimal('subtotal', 12, 2);
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
