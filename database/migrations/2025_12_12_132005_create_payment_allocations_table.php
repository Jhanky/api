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
        Schema::create('payment_allocations', function (Blueprint $table) {
            $table->id('allocation_id');
            $table->foreignId('payment_id')->constrained('supplier_payments', 'payment_id')->onDelete('cascade');
            $table->foreignId('invoice_id')->constrained('invoices', 'invoice_id')->onDelete('cascade');
            $table->decimal('allocated_amount', 15, 2);
            $table->timestamp('allocation_date')->useCurrent();
            $table->text('notes')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_allocations');
    }
};
