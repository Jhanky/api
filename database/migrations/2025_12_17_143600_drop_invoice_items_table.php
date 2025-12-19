<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Elimina la tabla invoice_items ya que no se necesitan los items detallados.
     */
    public function up(): void
    {
        Schema::dropIfExists('invoice_items');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id('item_id');
            $table->foreignId('invoice_id')->constrained('invoices', 'invoice_id')->onDelete('cascade');
            $table->string('description', 500);
            $table->decimal('quantity', 10, 2);
            $table->decimal('unit_price', 15, 2);
            $table->decimal('subtotal', 15, 2)->storedAs('quantity * unit_price');
            $table->string('category', 100)->nullable();
        });
    }
};
