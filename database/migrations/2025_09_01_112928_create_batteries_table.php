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
        Schema::create('batteries', function (Blueprint $table) {
            $table->id('battery_id');
            $table->string('brand', 100);
            $table->string('model', 100);
            $table->decimal('capacity', 8, 2); // Capacidad en Ah
            $table->decimal('voltage', 6, 2); // Voltaje en V
            $table->string('type', 50); // Tipo de batería (Plomo-ácido, Litio, etc.)
            $table->string('technical_sheet_url')->nullable(); // URL del archivo PDF
            $table->decimal('price', 10, 2); // Precio en USD
            $table->timestamps();
            
            // Índices para mejorar el rendimiento
            $table->index(['brand', 'model']);
            $table->index('capacity');
            $table->index('voltage');
            $table->index('type');
            $table->index('price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('batteries');
    }
};
