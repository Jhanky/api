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
        // Agregar comentarios a las columnas para mejor documentación contable
        \DB::statement("ALTER TABLE invoices MODIFY COLUMN invoice_number VARCHAR(255) COMMENT 'Número de factura'");
        \DB::statement("ALTER TABLE invoices MODIFY COLUMN invoice_date DATE COMMENT 'Fecha de emisión de la factura'");
        \DB::statement("ALTER TABLE invoices MODIFY COLUMN due_date DATE COMMENT 'Fecha de vencimiento de la factura'");
        \DB::statement("ALTER TABLE invoices MODIFY COLUMN provider_id BIGINT UNSIGNED COMMENT 'ID del proveedor que emitió la factura'");
        \DB::statement("ALTER TABLE invoices MODIFY COLUMN cost_center_id BIGINT UNSIGNED COMMENT 'ID del centro de costos asociado'");
        \DB::statement("ALTER TABLE invoices MODIFY COLUMN subtotal DECIMAL(15,2) COMMENT 'Subtotal antes de impuestos'");
        \DB::statement("ALTER TABLE invoices MODIFY COLUMN iva_amount DECIMAL(15,2) COMMENT 'Valor del IVA (19% del subtotal)'");
        \DB::statement("ALTER TABLE invoices MODIFY COLUMN retention DECIMAL(15,2) COMMENT 'Retención en la fuente'");
        \DB::statement("ALTER TABLE invoices MODIFY COLUMN total_amount DECIMAL(15,2) COMMENT 'Total a pagar (subtotal + IVA - retención)'");
        \DB::statement("ALTER TABLE invoices MODIFY COLUMN status ENUM('PENDIENTE', 'PAGADA') COMMENT 'Estado de pago de la factura'");
        \DB::statement("ALTER TABLE invoices MODIFY COLUMN payment_method ENUM('EFECTIVO', 'TRANSFERENCIA', 'CHEQUE', 'TARJETA', 'OTRO') COMMENT 'Método de pago utilizado'");
        \DB::statement("ALTER TABLE invoices MODIFY COLUMN payment_support VARCHAR(255) COMMENT 'Archivo de soporte de pago (PDF/imagen)'");
        \DB::statement("ALTER TABLE invoices MODIFY COLUMN invoice_file VARCHAR(255) COMMENT 'Archivo de la factura (PDF/imagen)'");
        \DB::statement("ALTER TABLE invoices MODIFY COLUMN description TEXT COMMENT 'Descripción o notas adicionales'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No se puede revertir fácilmente el reordenamiento de columnas
        // Esta migración es principalmente para mejorar la estructura
        // Si es necesario revertir, se requeriría una migración manual
    }
};
