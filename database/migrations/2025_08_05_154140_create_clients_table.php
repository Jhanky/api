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
        Schema::create('clients', function (Blueprint $table) {
            $table->id('client_id');
            $table->string('nic', 50);
            $table->string('client_type', 50);
            $table->string('name', 100);
            $table->string('department', 100);
            $table->string('city', 100);
            $table->text('address');
            $table->float('monthly_consumption_kwh');
            $table->float('energy_rate');
            $table->string('network_type', 50);
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Índices
            $table->unique('nic');
            $table->index(['client_type', 'is_active']);
            $table->index(['department', 'city']);
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};