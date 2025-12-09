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
        Schema::create('quotations', function (Blueprint $table) {
            $table->id('quotation_id');
            $table->foreignId('client_id')->constrained('clients', 'client_id')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('project_name', 200);
            $table->string('system_type', 50); // On-grid, Off-grid, Híbrido
            $table->decimal('power_kwp', 8, 2); // Potencia en kWp
            $table->integer('panel_count'); // Número de paneles
            $table->boolean('requires_financing')->default(false);
            
            // Porcentajes
            $table->decimal('profit_percentage', 5, 3)->default(0); // Porcentaje de ganancia
            $table->decimal('iva_profit_percentage', 5, 3)->default(0); // IVA sobre ganancia
            $table->decimal('commercial_management_percentage', 5, 3)->default(0); // Gestión comercial
            $table->decimal('administration_percentage', 5, 3)->default(0); // Administración
            $table->decimal('contingency_percentage', 5, 3)->default(0); // Contingencia
            $table->decimal('withholding_percentage', 5, 3)->default(0); // Retenciones
            
            // Valores calculados
            $table->decimal('subtotal', 15, 2)->default(0); // Subtotal productos + items
            $table->decimal('profit', 15, 2)->default(0); // Ganancia
            $table->decimal('profit_iva', 15, 2)->default(0); // IVA sobre ganancia
            $table->decimal('commercial_management', 15, 2)->default(0); // Gestión comercial
            $table->decimal('administration', 15, 2)->default(0); // Administración
            $table->decimal('contingency', 15, 2)->default(0); // Contingencia
            $table->decimal('withholdings', 15, 2)->default(0); // Retenciones
            $table->decimal('total_value', 15, 2)->default(0); // Valor total final
            $table->decimal('subtotal2', 15, 2)->default(0); // Subtotal + gestión comercial
            $table->decimal('subtotal3', 15, 2)->default(0); // Subtotal2 + admin + cont + ganancia + iva
            
            // Estado de la cotización
            $table->foreignId('status_id')->constrained('quotation_statuses', 'status_id')->onDelete('restrict');
            
            $table->timestamps();
            
            // Índices
            $table->index(['client_id', 'user_id']);
            $table->index('system_type');
            $table->index('power_kwp');
            $table->index('status_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotations');
    }
};
