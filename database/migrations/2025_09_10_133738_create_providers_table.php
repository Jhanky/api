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
        Schema::create('providers', function (Blueprint $table) {
            $table->id('provider_id');
            $table->string('provider_name', 255);
            $table->string('provider_tax_id', 50)->unique();
            $table->timestamps();
            
            // Índices para mejorar el rendimiento
            $table->index(['provider_name']);
            $table->index(['provider_tax_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('providers');
    }
};
