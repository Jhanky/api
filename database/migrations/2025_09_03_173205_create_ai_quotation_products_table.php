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
        Schema::create('ai_quotation_products', function (Blueprint $table) {
            $table->id('product_id');
            
            // Relación con la cotización de IA
            $table->unsignedBigInteger('ai_quotation_id')->comment('ID de la cotización de IA');
            
            // Datos del producto
            $table->enum('product_type', ['panel', 'inverter', 'battery'])->comment('Tipo de producto');
            $table->unsignedBigInteger('catalog_product_id')->comment('ID del producto en el catálogo principal');
            $table->integer('quantity')->comment('Cantidad del producto');
            $table->decimal('unit_price', 15, 2)->comment('Precio unitario');
            $table->decimal('total_price', 15, 2)->comment('Precio total del producto');
            
            // Información adicional del catálogo
            $table->string('brand', 100)->nullable()->comment('Marca del producto');
            $table->string('model', 100)->nullable()->comment('Modelo del producto');
            $table->string('specifications', 255)->nullable()->comment('Especificaciones del producto');
            
            $table->timestamps();
            
            // Índices
            $table->index(['ai_quotation_id']);
            $table->index(['product_type']);
            $table->index(['catalog_product_id']);
            
            // Clave foránea
            $table->foreign('ai_quotation_id')->references('ai_quotation_id')->on('ai_quotations')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_quotation_products');
    }
};
