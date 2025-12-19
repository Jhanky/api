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
        Schema::table('panels', function (Blueprint $table) {
            // Eliminar columnas complejas existentes
            $table->dropForeign(['material_id']);
            $table->dropColumn([
                'material_id',
                'panel_type',
                'power_rating',
                'voltage_mp',
                'current_mp',
                'voltage_oc',
                'current_sc',
                'efficiency',
                'dimensions',
                'weight',
                'warranty_years'
            ]);

            // Agregar columnas simples
            $table->string('brand', 100);
            $table->string('model', 100);
            $table->decimal('power', 8, 2); // Potencia en W
            $table->string('type', 50); // monocristalino, bifacial, etc.
            $table->decimal('price', 10, 2); // Precio
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('panels', function (Blueprint $table) {
            // Revertir cambios - eliminar columnas nuevas
            $table->dropColumn(['brand', 'model', 'power', 'type', 'price']);

            // Restaurar columnas originales
            $table->foreignId('material_id')->constrained()->onDelete('cascade');
            $table->string('panel_type', 50)->comment('Monocristalino, Policristalino, etc.');
            $table->decimal('power_rating', 6, 2)->comment('Potencia nominal en W');
            $table->decimal('voltage_mp', 6, 2)->comment('Voltaje en punto de máxima potencia en V');
            $table->decimal('current_mp', 6, 2)->comment('Corriente en punto de máxima potencia en A');
            $table->decimal('voltage_oc', 6, 2)->comment('Voltaje de circuito abierto en V');
            $table->decimal('current_sc', 6, 2)->comment('Corriente de cortocircuito en A');
            $table->decimal('efficiency', 5, 2)->comment('Eficiencia en %');
            $table->string('dimensions', 100)->nullable()->comment('Dimensiones en mm');
            $table->decimal('weight', 6, 2)->nullable()->comment('Peso en kg');
            $table->string('warranty_years', 20)->nullable()->comment('Garantía en años');
        });
    }
};
