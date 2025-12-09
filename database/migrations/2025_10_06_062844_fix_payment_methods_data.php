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
        // Primero actualizar los datos existentes a los nuevos valores
        \DB::table('invoices')
            ->where('payment_method', 'EFECTIVO')
            ->update(['payment_method' => 'Efectivo(EF)']);
            
        \DB::table('invoices')
            ->where('payment_method', 'TRANSFERENCIA')
            ->update(['payment_method' => 'Transferencia desde Cuenta personal(CP)']);
            
        \DB::table('invoices')
            ->where('payment_method', 'CHEQUE')
            ->update(['payment_method' => 'Efectivo(EF)']);
            
        \DB::table('invoices')
            ->where('payment_method', 'TARJETA')
            ->update(['payment_method' => 'Transferencia desde Cuenta personal(CP)']);
            
        \DB::table('invoices')
            ->where('payment_method', 'OTRO')
            ->update(['payment_method' => 'Efectivo(EF)']);
            
        // Ahora cambiar el enum
        \DB::statement("
            ALTER TABLE invoices 
            MODIFY COLUMN payment_method ENUM(
                'Transferencia desde cuenta Davivienda E4(TCD)',
                'Transferencia desde Cuenta personal(CP)', 
                'Efectivo(EF)'
            ) NULL COMMENT 'Método de pago utilizado'
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir los datos a los valores anteriores
        \DB::table('invoices')
            ->where('payment_method', 'Efectivo(EF)')
            ->update(['payment_method' => 'EFECTIVO']);
            
        \DB::table('invoices')
            ->where('payment_method', 'Transferencia desde Cuenta personal(CP)')
            ->update(['payment_method' => 'TRANSFERENCIA']);
            
        \DB::table('invoices')
            ->where('payment_method', 'Transferencia desde cuenta Davivienda E4(TCD)')
            ->update(['payment_method' => 'TRANSFERENCIA']);
            
        // Revertir el enum
        \DB::statement("
            ALTER TABLE invoices 
            MODIFY COLUMN payment_method ENUM(
                'EFECTIVO',
                'TRANSFERENCIA', 
                'CHEQUE',
                'TARJETA',
                'OTRO'
            ) NULL COMMENT 'Método de pago utilizado'
        ");
    }
};
