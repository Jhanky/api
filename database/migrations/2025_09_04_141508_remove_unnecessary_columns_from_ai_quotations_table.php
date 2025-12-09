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
        Schema::table('ai_quotations', function (Blueprint $table) {
            // Eliminar columnas irrelevantes para estimación rápida
            $table->dropColumn([
                'status',
                'notes', 
                'valid_until',
                'source_ip',
                'user_agent',
                'request_data'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ai_quotations', function (Blueprint $table) {
            // Restaurar columnas eliminadas
            $table->enum('status', ['pendiente', 'revisada', 'aprobada', 'rechazada'])->default('pendiente')->comment('Estado de la cotización');
            $table->text('notes')->nullable()->comment('Notas adicionales');
            $table->timestamp('valid_until')->nullable()->comment('Fecha de validez');
            $table->string('source_ip', 45)->nullable()->comment('IP de origen de la solicitud');
            $table->string('user_agent', 500)->nullable()->comment('User agent del cliente');
            $table->json('request_data')->nullable()->comment('Datos completos de la solicitud original');
        });
    }
};