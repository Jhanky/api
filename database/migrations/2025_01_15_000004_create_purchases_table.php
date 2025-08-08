<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number', 50)->unique();
            $table->date('date');
            $table->decimal('total_amount', 10, 2);
            $table->enum('payment_method', ['Efectivo', 'Transferencia', 'Tarjeta', 'Cheque', 'Crédito']);
            $table->enum('status', ['Pendiente', 'Pagado', 'Cancelado'])->default('Pendiente');
            $table->text('description')->nullable();
            $table->foreignId('supplier_id')->constrained('suppliers');
            $table->foreignId('cost_center_id')->constrained('cost_centers');
            $table->foreignId('project_id')->nullable()->constrained('projects');
            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('purchases');
    }
};