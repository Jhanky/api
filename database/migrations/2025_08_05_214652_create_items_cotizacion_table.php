<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('items_cotizacion', function (Blueprint $table) {
            $table->id('id_item');
            $table->unsignedBigInteger('id_cotizacion');
            $table->text('descripcion');
            $table->string('tipo_item', 50); // 'material', 'mano_obra', 'transporte', etc.
            $table->decimal('cantidad', 10, 2);
            $table->string('unidad', 20); // 'unidad', 'metro', 'kg', etc.
            $table->decimal('precio_unitario', 15, 2);
            $table->decimal('valor_parcial', 15, 2);
            $table->decimal('porcentaje_ganancia', 5, 2)->default(0);
            $table->decimal('ganancia', 15, 2)->default(0);
            $table->decimal('valor_total_item', 15, 2);
            $table->timestamps();
            
            // Relaciones
            $table->foreign('id_cotizacion')->references('quotation_id')->on('quotations')->onDelete('cascade');
            
            // Índices
            $table->index(['id_cotizacion', 'tipo_item']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('items_cotizacion');
    }
};