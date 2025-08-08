<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotation_statuses', function (Blueprint $table) {
            $table->id('status_id');
            $table->string('name', 50);
            $table->string('description', 255)->nullable();
            $table->string('color', 7)->default('#6c757d'); // Color hex para UI
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        
        // Insertar estados por defecto
        DB::table('quotation_statuses')->insert([
            ['name' => 'Borrador', 'description' => 'Cotización en proceso de creación', 'color' => '#6c757d'],
            ['name' => 'Enviada', 'description' => 'Cotización enviada al cliente', 'color' => '#007bff'],
            ['name' => 'Aprobada', 'description' => 'Cotización aprobada por el cliente', 'color' => '#28a745'],
            ['name' => 'Rechazada', 'description' => 'Cotización rechazada por el cliente', 'color' => '#dc3545'],
            ['name' => 'Vencida', 'description' => 'Cotización vencida', 'color' => '#ffc107'],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('quotation_statuses');
    }
};