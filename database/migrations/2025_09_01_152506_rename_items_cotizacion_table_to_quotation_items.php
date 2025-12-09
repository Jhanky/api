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
        Schema::table('items_cotizacion', function (Blueprint $table) {
            // Renombrar columnas
            $table->renameColumn('id_item', 'item_id');
            $table->renameColumn('id_cotizacion', 'quotation_id');
            $table->renameColumn('descripcion', 'description');
            $table->renameColumn('tipo_item', 'item_type');
            $table->renameColumn('cantidad', 'quantity');
            $table->renameColumn('unidad', 'unit');
            $table->renameColumn('precio_unitario', 'unit_price');
            $table->renameColumn('valor_parcial', 'partial_value');
            $table->renameColumn('porcentaje_ganancia', 'profit_percentage');
            $table->renameColumn('ganancia', 'profit');
            $table->renameColumn('valor_total_item', 'total_value');
        });

        // Renombrar la tabla
        Schema::rename('items_cotizacion', 'quotation_items');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('quotation_items', 'items_cotizacion');

        Schema::table('items_cotizacion', function (Blueprint $table) {
            // Revertir nombres de columnas
            $table->renameColumn('item_id', 'id_item');
            $table->renameColumn('quotation_id', 'id_cotizacion');
            $table->renameColumn('description', 'descripcion');
            $table->renameColumn('item_type', 'tipo_item');
            $table->renameColumn('quantity', 'cantidad');
            $table->renameColumn('unit', 'unidad');
            $table->renameColumn('unit_price', 'precio_unitario');
            $table->renameColumn('partial_value', 'valor_parcial');
            $table->renameColumn('profit_percentage', 'porcentaje_ganancia');
            $table->renameColumn('profit', 'ganancia');
            $table->renameColumn('total_value', 'valor_total_item');
        });
    }
};
