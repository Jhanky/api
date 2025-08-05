<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('batteries', function (Blueprint $table) {
            $table->id('battery_id');
            $table->string('brand', 100);
            $table->string('model', 100);
            $table->float('capacity'); // Capacidad en Ah
            $table->float('voltage'); // Voltaje en V
            $table->string('type', 50); // Tipo: Litio, AGM, Gel, etc.
            $table->text('technical_sheet_url')->nullable();
            $table->float('price');
            $table->timestamps();
            
            // Índices para optimizar búsquedas
            $table->index(['brand', 'type']);
            $table->index(['capacity', 'voltage']);
            $table->index('price');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('batteries');
    }
};