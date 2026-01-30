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
        // En MySQL, para agregar un valor a un ENUM debemos redefinir la columna
        DB::statement("ALTER TABLE invoices MODIFY COLUMN status ENUM('pendiente', 'pagada', 'parcial', 'anulada', 'cotizacion') NOT NULL DEFAULT 'pendiente'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir a los valores originales (Si hay datos con 'cotizacion', esto podría fallar o requerir limpieza previa)
        // Para seguridad en desarrollo, podemos simplemente redefinir sin 'cotizacion'
        DB::statement("ALTER TABLE invoices MODIFY COLUMN status ENUM('pendiente', 'pagada', 'parcial', 'anulada') NOT NULL DEFAULT 'pendiente'");
    }
};
