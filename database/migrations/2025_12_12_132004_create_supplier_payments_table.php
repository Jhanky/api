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
        Schema::create('supplier_payments', function (Blueprint $table) {
            $table->id('payment_id');
            $table->string('payment_code', 50)->unique();
            $table->foreignId('supplier_id')->constrained('suppliers', 'supplier_id')->onDelete('restrict');
            $table->foreignId('bank_account_id')->nullable()->constrained('bank_accounts', 'account_id')->onDelete('set null');
            $table->decimal('payment_amount', 15, 2);
            $table->date('payment_date');
            $table->enum('payment_method', ['efectivo', 'transferencia', 'cheque', 'tarjeta', 'consignacion']);
            $table->string('bank_reference', 100)->nullable();
            $table->string('check_number', 50)->nullable();
            $table->string('voucher_file_path', 500)->nullable();
            $table->string('voucher_file_name', 255)->nullable();
            $table->string('voucher_file_type', 50)->nullable();
            $table->bigInteger('voucher_file_size')->nullable();
            $table->enum('allocation_strategy', ['fifo', 'manual', 'proportional'])->default('fifo');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approval_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_payments');
    }
};
