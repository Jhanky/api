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
        Schema::create('ai_quotations', function (Blueprint $table) {
            $table->id('ai_quotation_id');
            
            // Datos básicos del cliente
            $table->string('client_name', 255)->comment('Nombre del cliente');
            
            // Datos de la cotización
            $table->string('project_name', 255)->comment('Nombre del proyecto');
            $table->enum('system_type', ['On-grid', 'Off-grid', 'Híbrido'])->comment('Tipo de sistema');
            $table->decimal('power_kwp', 8, 2)->comment('Potencia del sistema en kWp');
            $table->integer('panel_count')->comment('Cantidad de paneles');
            $table->decimal('total_value', 15, 2)->comment('Valor total de la cotización');
            
            // Estado y metadatos
            $table->enum('status', ['pendiente', 'revisada', 'aprobada', 'rechazada'])->default('pendiente')->comment('Estado de la cotización');
            $table->text('notes')->nullable()->comment('Notas adicionales');
            $table->timestamp('valid_until')->nullable()->comment('Fecha de validez');
            
            // Información de la solicitud
            $table->string('source_ip', 45)->nullable()->comment('IP de origen de la solicitud');
            $table->string('user_agent', 500)->nullable()->comment('User agent del cliente');
            $table->json('request_data')->nullable()->comment('Datos completos de la solicitud original');
            
            $table->timestamps();
            
            // Índices
            $table->index(['status']);
            $table->index(['created_at']);
            $table->index(['system_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_quotations');
    }
};
