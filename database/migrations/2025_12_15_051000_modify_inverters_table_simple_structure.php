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
        Schema::table('inverters', function (Blueprint $table) {
            // Eliminar columnas complejas existentes
            $table->dropForeign(['material_id']);
            $table->dropColumn([
                'material_id',
                'inverter_type',
                'power_rating',
                'input_voltage_min',
                'input_voltage_max',
                'output_voltage',
                'efficiency',
                'mppt_trackers',
                'protection_features',
                'cooling_method',
                'warranty_years'
            ]);

            // Agregar columnas simples
            $table->string('brand', 100);
            $table->string('model', 100);
            $table->decimal('power', 8, 2); // Potencia en W
            $table->string('system_type', 50); // on_grid, off_grid, hybrid
            $table->string('grid_type', 50); // monofasico, trifasico
            $table->decimal('price', 10, 2); // Precio
            $table->string('technical_sheet_url')->nullable(); // URL de ficha técnica
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inverters', function (Blueprint $table) {
            // Revertir cambios - eliminar columnas nuevas
            $table->dropColumn(['brand', 'model', 'power', 'system_type', 'grid_type', 'price', 'technical_sheet_url']);

            // Restaurar columnas originales
            $table->foreignId('material_id')->constrained()->onDelete('cascade');
            $table->string('inverter_type', 50)->comment('On-grid, Off-grid, Híbrido');
            $table->decimal('power_rating', 8, 2)->comment('Potencia nominal en kW');
            $table->decimal('input_voltage_min', 6, 2)->comment('Voltaje de entrada mínimo en V');
            $table->decimal('input_voltage_max', 6, 2)->comment('Voltaje de entrada máximo en V');
            $table->decimal('output_voltage', 6, 2)->comment('Voltaje de salida en V');
            $table->decimal('efficiency', 5, 2)->comment('Eficiencia en %');
            $table->string('mppt_trackers', 20)->nullable()->comment('Número de trackers MPPT');
            $table->string('protection_features', 255)->nullable()->comment('Protecciones (sobretensión, cortocircuito, etc.)');
            $table->string('cooling_method', 50)->nullable()->comment('Método de enfriamiento');
            $table->string('warranty_years', 20)->nullable()->comment('Garantía en años');
        });
    }
};
