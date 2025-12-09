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
        // Reordenar físicamente las columnas en la base de datos
        // para seguir el orden lógico contable
        
        \DB::statement("
            ALTER TABLE invoices 
            MODIFY COLUMN invoice_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT FIRST,
            MODIFY COLUMN invoice_number VARCHAR(255) NOT NULL AFTER invoice_id,
            MODIFY COLUMN invoice_date DATE NOT NULL AFTER invoice_number,
            MODIFY COLUMN due_date DATE NOT NULL AFTER invoice_date,
            MODIFY COLUMN provider_id BIGINT UNSIGNED NOT NULL AFTER due_date,
            MODIFY COLUMN cost_center_id BIGINT UNSIGNED NOT NULL AFTER provider_id,
            MODIFY COLUMN subtotal DECIMAL(15,2) NULL AFTER cost_center_id,
            MODIFY COLUMN iva_amount DECIMAL(15,2) NULL AFTER subtotal,
            MODIFY COLUMN retention DECIMAL(15,2) NULL AFTER iva_amount,
            MODIFY COLUMN total_amount DECIMAL(15,2) NOT NULL AFTER retention,
            MODIFY COLUMN status ENUM('PENDIENTE', 'PAGADA') NOT NULL DEFAULT 'PENDIENTE' AFTER total_amount,
            MODIFY COLUMN payment_method ENUM('EFECTIVO', 'TRANSFERENCIA', 'CHEQUE', 'TARJETA', 'OTRO') NULL AFTER status,
            MODIFY COLUMN payment_support VARCHAR(255) NULL AFTER payment_method,
            MODIFY COLUMN invoice_file VARCHAR(255) NULL AFTER payment_support,
            MODIFY COLUMN description TEXT NULL AFTER invoice_file,
            MODIFY COLUMN created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP AFTER description,
            MODIFY COLUMN updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir el orden de las columnas (orden original)
        // Nota: Este es un proceso complejo y puede requerir migración manual
        // si se necesita revertir completamente
        
        \DB::statement("
            ALTER TABLE invoices 
            MODIFY COLUMN invoice_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT FIRST,
            MODIFY COLUMN invoice_number VARCHAR(255) NOT NULL AFTER invoice_id,
            MODIFY COLUMN invoice_date DATE NOT NULL AFTER invoice_number,
            MODIFY COLUMN due_date DATE NOT NULL AFTER invoice_date,
            MODIFY COLUMN total_amount DECIMAL(15,2) NOT NULL AFTER due_date,
            MODIFY COLUMN description TEXT NULL AFTER total_amount,
            MODIFY COLUMN status ENUM('PENDIENTE', 'PAGADA') NOT NULL DEFAULT 'PENDIENTE' AFTER description,
            MODIFY COLUMN provider_id BIGINT UNSIGNED NOT NULL AFTER status,
            MODIFY COLUMN cost_center_id BIGINT UNSIGNED NOT NULL AFTER provider_id,
            MODIFY COLUMN subtotal DECIMAL(15,2) NULL AFTER cost_center_id,
            MODIFY COLUMN iva_amount DECIMAL(15,2) NULL AFTER subtotal,
            MODIFY COLUMN retention DECIMAL(15,2) NULL AFTER iva_amount,
            MODIFY COLUMN payment_method ENUM('EFECTIVO', 'TRANSFERENCIA', 'CHEQUE', 'TARJETA', 'OTRO') NULL AFTER retention,
            MODIFY COLUMN payment_support VARCHAR(255) NULL AFTER payment_method,
            MODIFY COLUMN invoice_file VARCHAR(255) NULL AFTER payment_support,
            MODIFY COLUMN created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP AFTER invoice_file,
            MODIFY COLUMN updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at
        ");
    }
};
