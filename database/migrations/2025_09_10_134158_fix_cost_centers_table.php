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
        // Eliminar primero la tabla de facturas para evitar problemas de claves foráneas
        Schema::dropIfExists('invoices');
        
        // Eliminar la tabla cost_centers existente y recrearla con la estructura correcta
        Schema::dropIfExists('cost_centers');
        
        Schema::create('cost_centers', function (Blueprint $table) {
            $table->id('cost_center_id');
            $table->string('cost_center_name', 255);
            $table->timestamps();
            
            // Índices para mejorar el rendimiento
            $table->index(['cost_center_name']);
        });
        
        // Recrear la tabla de facturas con las relaciones correctas
        Schema::create('invoices', function (Blueprint $table) {
            $table->id('invoice_id');
            $table->string('invoice_number', 100);
            $table->date('invoice_date');
            $table->date('due_date')->nullable();
            $table->decimal('total_amount', 15, 2);
            $table->enum('status', ['PENDIENTE', 'PAGADA'])->default('PENDIENTE');
            $table->unsignedBigInteger('provider_id');
            $table->unsignedBigInteger('cost_center_id');
            $table->timestamps();
            
            // Índices para mejorar el rendimiento
            $table->index(['invoice_number']);
            $table->index(['invoice_date']);
            $table->index(['due_date']);
            $table->index(['status']);
            $table->index(['provider_id']);
            $table->index(['cost_center_id']);
            
            // Claves foráneas
            $table->foreign('provider_id')->references('provider_id')->on('providers')->onDelete('restrict');
            $table->foreign('cost_center_id')->references('cost_center_id')->on('cost_centers')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recrear la tabla con la estructura original si es necesario
        Schema::dropIfExists('cost_centers');
    }
};
