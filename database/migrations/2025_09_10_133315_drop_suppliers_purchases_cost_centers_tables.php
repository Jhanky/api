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
        // Eliminar las tablas en orden inverso a su creación para evitar problemas de integridad referencial
        
        // 1. Primero eliminar la tabla de compras/facturas (tiene claves foráneas)
        Schema::dropIfExists('purchases');
        
        // 2. Luego eliminar la tabla de centros de costos
        Schema::dropIfExists('cost_centers');
        
        // 3. Después eliminar la tabla de categorías de centros de costos
        Schema::dropIfExists('cost_center_categories');
        
        // 4. Finalmente eliminar la tabla de proveedores
        Schema::dropIfExists('suppliers');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recrear las tablas en el orden original si necesitas hacer rollback
        
        // 1. Recrear tabla de categorías de centros de costos
        Schema::create('cost_center_categories', function (Blueprint $table) {
            $table->id('category_id');
            $table->string('name', 100)->comment('Nombre de la categoría');
            $table->text('description')->nullable()->comment('Descripción de la categoría');
            $table->enum('status', ['activo', 'inactivo'])->default('activo')->comment('Estado de la categoría');
            $table->string('color', 7)->default('#3B82F6')->comment('Color hexadecimal para identificación');
            $table->string('icon', 50)->nullable()->comment('Nombre del ícono para la interfaz');
            $table->timestamps();
            $table->index(['status']);
            $table->index(['name']);
        });
        
        // 2. Recrear tabla de centros de costos
        Schema::create('cost_centers', function (Blueprint $table) {
            $table->id('cost_center_id');
            $table->string('name', 100)->comment('Nombre del centro de costo');
            $table->text('description')->nullable()->comment('Descripción del centro de costo');
            $table->enum('status', ['activo', 'inactivo'])->default('activo')->comment('Estado del centro de costo');
            $table->unsignedBigInteger('category_id')->comment('Categoría del centro de costo');
            $table->timestamps();
            $table->index(['status']);
            $table->index(['name']);
            $table->index(['category_id']);
            $table->foreign('category_id')->references('category_id')->on('cost_center_categories')->onDelete('restrict');
        });
        
        // 3. Recrear tabla de proveedores
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id('supplier_id');
            $table->string('name', 100);
            $table->string('tax_id', 20)->unique();
            $table->string('address', 255)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('contact_name', 100)->nullable();
            $table->string('contact_phone', 20)->nullable();
            $table->string('contact_email', 100)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('department', 100)->nullable();
            $table->string('country', 100)->default('Colombia');
            $table->string('postal_code', 20)->nullable();
            $table->text('description')->nullable();
            $table->string('website', 255)->nullable();
            $table->enum('status', ['activo', 'inactivo', 'suspendido'])->default('activo');
            $table->enum('category', ['equipos', 'materiales', 'servicios', 'general'])->default('general');
            $table->decimal('credit_limit', 15, 2)->nullable();
            $table->integer('payment_terms')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['name', 'tax_id']);
            $table->index(['status', 'category']);
            $table->index(['city', 'department']);
        });
        
        // 4. Recrear tabla de compras/facturas
        Schema::create('purchases', function (Blueprint $table) {
            $table->id('purchase_id');
            $table->unsignedBigInteger('supplier_id');
            $table->unsignedBigInteger('project_id')->nullable();
            $table->unsignedBigInteger('user_id')->comment('Usuario que registra la factura');
            $table->string('invoice_number', 50)->unique()->comment('Número de factura del proveedor');
            $table->date('invoice_date')->comment('Fecha de la factura');
            $table->date('due_date')->comment('Fecha de vencimiento');
            $table->decimal('subtotal', 15, 2)->default(0)->comment('Subtotal sin impuestos');
            $table->decimal('tax_amount', 15, 2)->default(0)->comment('Monto de impuestos');
            $table->decimal('total_amount', 15, 2)->default(0)->comment('Total de la factura');
            $table->decimal('paid_amount', 15, 2)->default(0)->comment('Monto pagado');
            $table->decimal('balance', 15, 2)->default(0)->comment('Saldo pendiente');
            $table->enum('status', ['pendiente', 'parcial', 'pagada', 'vencida', 'cancelada'])->default('pendiente');
            $table->enum('payment_method', ['efectivo', 'transferencia', 'cheque', 'tarjeta', 'otro'])->nullable();
            $table->string('payment_reference', 100)->nullable()->comment('Referencia del pago');
            $table->text('description')->nullable()->comment('Descripción de la compra');
            $table->text('notes')->nullable()->comment('Notas adicionales');
            $table->string('document_url', 255)->nullable()->comment('URL del documento escaneado');
            $table->timestamp('paid_at')->nullable()->comment('Fecha de pago');
            $table->timestamp('cancelled_at')->nullable()->comment('Fecha de cancelación');
            $table->timestamps();
            $table->index(['supplier_id', 'status']);
            $table->index(['project_id', 'status']);
            $table->index(['invoice_date', 'due_date']);
            $table->index(['status', 'due_date']);
            $table->foreign('supplier_id')->references('supplier_id')->on('suppliers')->onDelete('restrict');
            $table->foreign('project_id')->references('project_id')->on('projects')->onDelete('set null');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('restrict');
        });
        
        // 5. Agregar la columna cost_center_id a purchases
        Schema::table('purchases', function (Blueprint $table) {
            $table->unsignedBigInteger('cost_center_id')->nullable()->after('project_id');
            $table->foreign('cost_center_id')->references('cost_center_id')->on('cost_centers')->onDelete('set null');
            $table->index(['cost_center_id', 'status']);
        });
    }
};
