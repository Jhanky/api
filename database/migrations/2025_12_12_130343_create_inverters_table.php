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
        Schema::create('inverters', function (Blueprint $table) {
            $table->id();
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
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inverters');
    }
};