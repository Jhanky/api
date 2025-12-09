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
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique()->comment('Código del método de pago (TCD, CP, EF)');
            $table->string('name')->comment('Nombre completo del método de pago');
            $table->text('description')->nullable()->comment('Descripción del método de pago');
            $table->boolean('is_active')->default(true)->comment('Si el método está activo');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
