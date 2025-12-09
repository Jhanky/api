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
        Schema::table('invoices', function (Blueprint $table) {
            // Agregar campo para tipo de venta (Contado/Crédito)
            $table->enum('sale_type', ['CONTADO', 'CREDITO'])->default('CONTADO')->after('status')->comment('Tipo de venta: CONTADO o CREDITO');
        });

        // Actualizar datos existentes:
        // - Las facturas PENDIENTES se convierten en CREDITO
        // - Las facturas PAGADAS se convierten en CONTADO
        \DB::table('invoices')
            ->where('status', 'PENDIENTE')
            ->update(['sale_type' => 'CREDITO']);

        \DB::table('invoices')
            ->where('status', 'PAGADA')
            ->update(['sale_type' => 'CONTADO']);

        // Ahora actualizar el campo status para que sea más claro:
        // - PENDIENTE = Factura pendiente de pago
        // - PAGADA = Factura ya pagada
        \DB::statement("ALTER TABLE invoices MODIFY COLUMN status ENUM('PENDIENTE', 'PAGADA') NOT NULL DEFAULT 'PENDIENTE' COMMENT 'Estado de pago de la factura'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('sale_type');
        });
    }
};
