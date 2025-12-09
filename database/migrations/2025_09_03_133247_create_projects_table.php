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
        Schema::create('projects', function (Blueprint $table) {
            $table->id('project_id');
            
            // Relaciones con tablas existentes
            $table->foreignId('quotation_id')->constrained('quotations', 'quotation_id')->onDelete('cascade');
            $table->foreignId('client_id')->constrained('clients', 'client_id')->onDelete('cascade');
            $table->foreignId('location_id')->constrained('locations', 'location_id')->onDelete('restrict');
            $table->foreignId('status_id')->constrained('project_statuses', 'status_id')->onDelete('restrict');
            
            // Campos específicos del proyecto
            $table->string('project_name', 200); // Heredado de la cotización
            $table->date('start_date')->nullable(); // Fecha de inicio
            $table->date('estimated_end_date')->nullable(); // Fecha estimada de finalización
            $table->date('actual_end_date')->nullable(); // Fecha real de finalización
            $table->foreignId('project_manager_id')->nullable()->constrained('users')->onDelete('restrict'); // Gerente del proyecto
            $table->decimal('budget', 15, 2)->nullable(); // Presupuesto real vs. cotizado
            $table->text('notes')->nullable(); // Notas adicionales del proyecto
            
            // Campos de georreferenciación específicos
            $table->decimal('latitude', 10, 8)->nullable(); // Coordenada específica del proyecto
            $table->decimal('longitude', 11, 8)->nullable(); // Coordenada específica del proyecto
            $table->text('installation_address')->nullable(); // Dirección específica de instalación
            
            $table->timestamps();
            
            // Índices
            $table->index(['quotation_id', 'client_id']);
            $table->index('status_id');
            $table->index('project_manager_id');
            $table->index('start_date');
            $table->index(['latitude', 'longitude']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
