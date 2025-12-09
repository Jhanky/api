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
            // Subtotal (antes de IVA)
            $table->decimal('subtotal', 15, 2)->nullable()->after('total_amount');
            
            // Valor del IVA (19% del subtotal)
            $table->decimal('iva_amount', 15, 2)->nullable()->after('subtotal');
            
            // Retención
            $table->decimal('retention', 15, 2)->nullable()->after('iva_amount');
            
            // Estado (ya existe, pero vamos a asegurar que esté)
            // $table->enum('status', ['PENDIENTE', 'PAGADA'])->default('PENDIENTE');
            
            // Método de pago
            $table->enum('payment_method', ['EFECTIVO', 'TRANSFERENCIA', 'CHEQUE', 'TARJETA', 'OTRO'])->nullable()->after('retention');
            
            // Soporte de pago (archivo PDF o imagen)
            $table->string('payment_support')->nullable()->after('payment_method');
            
            // Factura (archivo PDF o imagen)
            $table->string('invoice_file')->nullable()->after('payment_support');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn([
                'subtotal',
                'iva_amount', 
                'retention',
                'payment_method',
                'payment_support',
                'invoice_file'
            ]);
        });
    }
};
