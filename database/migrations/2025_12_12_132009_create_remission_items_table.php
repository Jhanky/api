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
        Schema::create('remission_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('remission_id')->constrained()->onDelete('cascade');
            $table->foreignId('material_id')->constrained()->onDelete('restrict');
            $table->decimal('quantity_requested', 10, 2);
            $table->decimal('quantity_dispatched', 10, 2)->nullable();
            $table->decimal('quantity_received', 10, 2)->nullable();
            $table->string('unit_measure', 20);
            $table->string('lot_number', 50)->nullable();
            $table->json('serial_numbers')->nullable();
            $table->text('notes')->nullable();
            $table->smallInteger('display_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('remission_items');
    }
};
