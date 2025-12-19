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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id('invoice_id');
            $table->string('invoice_number', 100)->unique();
            $table->foreignId('supplier_id')->constrained('suppliers', 'supplier_id')->onDelete('restrict');
            $table->foreignId('cost_center_id')->constrained('cost_centers', 'cost_center_id')->onDelete('restrict');
            $table->decimal('amount_before_iva', 15, 2);
            $table->decimal('iva_percentage', 5, 2)->default(19.00);
            $table->decimal('iva_amount', 15, 2)->storedAs('amount_before_iva * iva_percentage / 100');
            $table->decimal('total_value', 15, 2);
            $table->enum('status', ['pendiente', 'pagada', 'parcial', 'anulada'])->default('pendiente');
            $table->enum('payment_type', ['parcial', 'total'])->default('total');
            $table->date('issue_date');
            $table->date('due_date')->nullable();
            $table->date('payment_date')->nullable();
            $table->string('invoice_file_path', 500)->nullable();
            $table->string('invoice_file_name', 255)->nullable();
            $table->string('invoice_file_type', 50)->nullable();
            $table->bigInteger('invoice_file_size')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};