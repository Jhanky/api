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
        Schema::create('used_products', function (Blueprint $table) {
            $table->id('used_product_id');
            $table->foreignId('quotation_id')->constrained('quotations', 'quotation_id')->onDelete('cascade');
            $table->string('product_type', 20); // panel, inverter, battery
            $table->unsignedBigInteger('product_id'); // ID del producto específico
            $table->integer('quantity');
            $table->decimal('unit_price', 12, 2); // Precio unitario en COP
            $table->decimal('partial_value', 12, 2)->default(0); // Cantidad * precio unitario
            $table->decimal('profit_percentage', 5, 2)->default(0); // Porcentaje de ganancia
            $table->decimal('profit', 12, 2)->default(0); // Ganancia calculada
            $table->decimal('total_value', 12, 2)->default(0); // Valor total con ganancia
            
            $table->timestamps();
            
            // Índices
            $table->index(['quotation_id', 'product_type']);
            $table->index('product_type');
            $table->index('product_id');
            $table->index('unit_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('used_products');
    }
};
