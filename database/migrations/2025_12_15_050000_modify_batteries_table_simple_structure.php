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
        Schema::table('batteries', function (Blueprint $table) {
            // Eliminar columnas complejas existentes
            $table->dropForeign(['material_id']);
            $table->dropColumn([
                'material_id',
                'battery_type',
                'energy_capacity',
                'chemistry',
                'cycle_life',
                'depth_of_discharge',
                'efficiency',
                'warranty_years'
            ]);

            // Agregar columnas simples (capacity y voltage ya existen)
            $table->string('brand', 100);
            $table->string('model', 100);
            $table->string('type', 50); // litio, gel, plomo_acido
            $table->decimal('price', 10, 2); // Precio
            $table->string('technical_sheet_url')->nullable(); // URL de ficha técnica
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('batteries', function (Blueprint $table) {
            // Revertir cambios - eliminar columnas nuevas
            $table->dropColumn(['brand', 'model', 'capacity', 'voltage', 'type', 'price', 'technical_sheet_url']);

            // Restaurar columnas originales
            $table->foreignId('material_id')->constrained()->onDelete('cascade');
            $table->string('battery_type', 50)->comment('LiFePO4, AGM, Gel, etc.');
            $table->decimal('voltage', 5, 2)->comment('Voltaje nominal en V');
            $table->decimal('capacity', 8, 2)->comment('Capacidad en Ah');
            $table->decimal('energy_capacity', 8, 2)->comment('Capacidad energética en kWh');
            $table->string('chemistry', 50)->nullable()->comment('Química de la batería');
            $table->integer('cycle_life')->nullable()->comment('Ciclos de vida');
            $table->decimal('depth_of_discharge', 5, 2)->nullable()->comment('Profundidad de descarga en %');
            $table->decimal('efficiency', 5, 2)->nullable()->comment('Eficiencia en %');
            $table->string('warranty_years', 20)->nullable()->comment('Garantía en años');
        });
    }
};
