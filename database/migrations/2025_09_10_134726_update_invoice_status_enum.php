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
        // Primero modificar la columna para permitir los nuevos valores
        \DB::statement("ALTER TABLE invoices MODIFY COLUMN status ENUM('Pending', 'Paid', 'PENDIENTE', 'PAGADA') DEFAULT 'Pending'");
        
        // Luego actualizar los valores existentes de 'Pending' a 'PENDIENTE' y 'Paid' a 'PAGADA'
        \DB::table('invoices')
            ->where('status', 'Pending')
            ->update(['status' => 'PENDIENTE']);
            
        \DB::table('invoices')
            ->where('status', 'Paid')
            ->update(['status' => 'PAGADA']);
        
        // Finalmente, modificar la columna para usar solo los nuevos valores del enum
        \DB::statement("ALTER TABLE invoices MODIFY COLUMN status ENUM('PENDIENTE', 'PAGADA') DEFAULT 'PENDIENTE'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir los valores de 'PENDIENTE' a 'Pending' y 'PAGADA' a 'Paid'
        \DB::table('invoices')
            ->where('status', 'PENDIENTE')
            ->update(['status' => 'Pending']);
            
        \DB::table('invoices')
            ->where('status', 'PAGADA')
            ->update(['status' => 'Paid']);
        
        // Revertir la columna a los valores originales del enum
        \DB::statement("ALTER TABLE invoices MODIFY COLUMN status ENUM('Pending', 'Paid') DEFAULT 'Pending'");
    }
};
