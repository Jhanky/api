<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('used_products', function (Blueprint $table) {
            $table->id('used_product_id');
            $table->unsignedBigInteger('quotation_id');
            $table->enum('product_type', ['panel', 'inverter', 'battery']);
            $table->unsignedBigInteger('product_id');
            $table->integer('quantity');
            $table->decimal('unit_price', 15, 2);
            $table->decimal('partial_value', 15, 2);
            $table->decimal('profit_percentage', 5, 2)->default(0);
            $table->decimal('profit', 15, 2)->default(0);
            $table->decimal('total_value', 15, 2);
            $table->timestamps();
            
            // Relaciones
            $table->foreign('quotation_id')->references('quotation_id')->on('quotations')->onDelete('cascade');
            
            // Índices
            $table->index(['quotation_id', 'product_type']);
            $table->index(['product_type', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('used_products');
    }
};