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
        Schema::create('items_cotizacion', function (Blueprint $table) {
            $table->id('id_item');
            $table->foreignId('id_cotizacion')->constrained('quotations', 'quotation_id')->onDelete('cascade');
            $table->string('descripcion', 500);
            $table->string('tipo_item', 50); // Materiales, Mano de obra, Equipos, etc.
            $table->decimal('cantidad', 8, 2);
            $table->string('unidad', 20); // Unidades, Metros, Horas, etc.
            $table->decimal('precio_unitario', 12, 2); // Precio unitario en COP
            $table->decimal('valor_parcial', 12, 2)->default(0); // Cantidad * precio unitario
            $table->decimal('porcentaje_ganancia', 5, 2)->default(0); // Porcentaje de ganancia
            $table->decimal('ganancia', 12, 2)->default(0); // Ganancia calculada
            $table->decimal('valor_total_item', 12, 2)->default(0); // Valor total con ganancia
            
            $table->timestamps();
            
            // Índices
            $table->index(['id_cotizacion', 'tipo_item']);
            $table->index('tipo_item');
            $table->index('precio_unitario');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items_cotizacion');
    }
};
