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
        Schema::create('locations', function (Blueprint $table) {
            $table->id('location_id');
            $table->string('department', 100);
            $table->string('municipality', 100);
            $table->decimal('radiation', 5, 2); // Radiación solar en kWh/m²/día
            $table->timestamps();
            
            // Índices para mejorar el rendimiento de las consultas
            $table->index(['department', 'municipality']);
            $table->index('radiation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
