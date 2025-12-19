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
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_id')->constrained('materials')->onDelete('cascade');
            $table->foreignId('warehouse_id')->constrained('warehouses')->onDelete('cascade');
            $table->decimal('quantity', 10, 2);
            $table->decimal('min_stock', 10, 2)->nullable();
            $table->decimal('max_stock', 10, 2)->nullable();
            $table->string('location', 100)->nullable();
            $table->string('batch_number', 100)->nullable();
            $table->date('expiry_date')->nullable();
            $table->decimal('unit_cost', 10, 2)->nullable();
            $table->decimal('total_value', 12, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};