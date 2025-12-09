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
        // Paso 1: Cambiar temporalmente a VARCHAR para permitir cualquier valor
        \DB::statement("
            ALTER TABLE invoices 
            MODIFY COLUMN payment_method VARCHAR(255) NULL COMMENT 'Método de pago utilizado'
        ");
        
        // Paso 2: Actualizar los datos existentes
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
            
        // Paso 3: Cambiar de vuelta a ENUM con los nuevos valores
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
        // Revertir a los valores anteriores
        \DB::statement("
            ALTER TABLE invoices 
            MODIFY COLUMN payment_method VARCHAR(255) NULL COMMENT 'Método de pago utilizado'
        ");
        
        \DB::table('invoices')
            ->where('payment_method', 'Efectivo(EF)')
            ->update(['payment_method' => 'EFECTIVO']);
            
        \DB::table('invoices')
            ->where('payment_method', 'Transferencia desde Cuenta personal(CP)')
            ->update(['payment_method' => 'TRANSFERENCIA']);
            
        \DB::table('invoices')
            ->where('payment_method', 'Transferencia desde cuenta Davivienda E4(TCD)')
            ->update(['payment_method' => 'TRANSFERENCIA']);
            
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
