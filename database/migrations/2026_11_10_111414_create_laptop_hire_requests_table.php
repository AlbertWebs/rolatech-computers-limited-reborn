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
        Schema::create('laptop_hire_requests', function (Blueprint $table) {
            $table->id();
            $table->string('equipment_type', 40)->default('laptop');
            $table->string('name');
            $table->string('email');
            $table->string('phone', 30);
            $table->date('pickup_date');
            $table->unsignedSmallInteger('number_of_items');
            $table->text('desired_specs');
            $table->string('status', 24)->default('pending');
            $table->text('admin_notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laptop_hire_requests');
    }
};
