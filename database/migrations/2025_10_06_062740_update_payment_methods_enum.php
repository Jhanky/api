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
        // Actualizar el enum de métodos de pago con los valores específicos
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
        // Revertir a los métodos de pago anteriores
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
