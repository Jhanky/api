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
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_item_id')->constrained()->onDelete('cascade');
            $table->enum('movement_type', ['entry', 'exit', 'adjustment', 'transfer']);
            $table->decimal('quantity_change', 10, 2);
            $table->decimal('previous_quantity', 10, 2);
            $table->decimal('new_quantity', 10, 2);
            $table->string('movement_reason', 255)->nullable();
            $table->foreignId('related_document_id')->nullable()->comment('ID del documento relacionado');
            $table->string('related_document_type', 50)->nullable()->comment('Tipo de documento (remission, invoice, etc.)');
            $table->foreignId('performed_by')->constrained('users')->onDelete('restrict');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
    }
};