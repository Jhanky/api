<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->id('location_id');
            $table->string('department', 50);
            $table->string('municipality', 50);
            $table->float('radiation'); // Radiación solar en kWh/m²/día
            $table->timestamps();
            
            // Índices para optimizar búsquedas
            $table->index(['department', 'municipality']);
            $table->index('radiation');
            
            // Índice único para evitar duplicados de departamento-municipio
            $table->unique(['department', 'municipality']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};