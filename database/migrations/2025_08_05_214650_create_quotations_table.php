<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotations', function (Blueprint $table) {
            $table->id('quotation_id');
            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('user_id');
            $table->string('project_name', 255);
            $table->string('system_type', 50);
            $table->decimal('power_kwp', 12, 3);  // ← Hasta 999,999,999.999 kWp
            $table->integer('panel_count');
            $table->boolean('requires_financing')->default(false);
            
            // Porcentajes (sin cambios)
            $table->decimal('profit_percentage', 6, 3)->default(0);
            $table->decimal('iva_profit_percentage', 6, 3)->default(0);
            $table->decimal('commercial_management_percentage', 6, 3)->default(0);
            $table->decimal('administration_percentage', 6, 3)->default(0);
            $table->decimal('contingency_percentage', 6, 3)->default(0);
            $table->decimal('withholding_percentage', 6, 3)->default(0);
            
            // Valores monetarios - CORREGIDOS para miles de millones
            $table->decimal('subtotal', 15, 2)->default(0);              // ← Hasta 9,999,999,999,999.99
            $table->decimal('profit', 15, 2)->default(0);                // ← Hasta 9,999,999,999,999.99
            $table->decimal('profit_iva', 15, 2)->default(0);            // ← Hasta 9,999,999,999,999.99
            $table->decimal('commercial_management', 15, 2)->default(0); // ← Hasta 9,999,999,999,999.99
            $table->decimal('administration', 15, 2)->default(0);        // ← Hasta 9,999,999,999,999.99
            $table->decimal('contingency', 15, 2)->default(0);           // ← Hasta 9,999,999,999,999.99
            $table->decimal('withholdings', 15, 2)->default(0);          // ← Hasta 9,999,999,999,999.99
            $table->decimal('total_value', 15, 2)->default(0);           // ← Hasta 9,999,999,999,999.99
            $table->decimal('subtotal2', 15, 2)->default(0);             // ← Hasta 9,999,999,999,999.99
            $table->decimal('subtotal3', 15, 2)->default(0);             // ← Hasta 9,999,999,999,999.99
            
            $table->timestamp('creation_date')->useCurrent();
            $table->unsignedBigInteger('status_id')->default(1);
            $table->timestamps();
            
            // Índices y relaciones
            $table->foreign('client_id')->references('client_id')->on('clients')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('status_id')->references('status_id')->on('quotation_statuses');
            
            $table->index(['client_id', 'status_id']);
            $table->index('creation_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotations');
    }
};