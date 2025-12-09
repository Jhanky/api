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
        Schema::create('ai_quotation_items', function (Blueprint $table) {
            $table->id('item_id');
            
            // Relación con la cotización de IA
            $table->unsignedBigInteger('ai_quotation_id')->comment('ID de la cotización de IA');
            
            // Datos del item
            $table->string('description', 500)->comment('Descripción del item');
            $table->enum('item_type', ['conductor_fotovoltaico', 'material_electrico', 'estructura', 'mano_obra', 'legalization'])->comment('Tipo específico del item');
            $table->integer('quantity')->comment('Cantidad del item');
            $table->string('unit', 50)->default('unidad')->comment('Unidad de medida');
            $table->decimal('unit_price', 15, 2)->comment('Precio unitario');
            $table->decimal('profit_percentage', 5, 2)->default(0.25)->comment('Porcentaje de ganancia');
            $table->decimal('total_price', 15, 2)->comment('Precio total del item');
            
            // Información adicional
            $table->text('notes')->nullable()->comment('Notas adicionales del item');
            
            $table->timestamps();
            
            // Índices
            $table->index(['ai_quotation_id']);
            $table->index(['item_type']);
            $table->index(['unit_price']);
            
            // Clave foránea
            $table->foreign('ai_quotation_id')->references('ai_quotation_id')->on('ai_quotations')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_quotation_items');
    }
};
