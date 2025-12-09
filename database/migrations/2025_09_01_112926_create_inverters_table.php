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
            $table->id('inverter_id');
            $table->string('brand', 100);
            $table->string('model', 100);
            $table->decimal('power', 8, 2); // Potencia en W
            $table->string('system_type', 50); // Tipo de sistema (On-grid, Off-grid, Híbrido)
            $table->string('grid_type', 50); // Tipo de red (Monofásica, Trifásica)
            $table->string('technical_sheet_url')->nullable(); // URL del archivo PDF
            $table->decimal('price', 10, 2); // Precio en USD
            $table->timestamps();
            
            // Índices para mejorar el rendimiento
            $table->index(['brand', 'model']);
            $table->index('power');
            $table->index('system_type');
            $table->index('grid_type');
            $table->index('price');
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
