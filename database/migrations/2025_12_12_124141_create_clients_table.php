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
            $table->id();
            $table->string('name', 255);
            $table->foreignId('client_type_id')->constrained('client_types')->onDelete('restrict')->onUpdate('cascade');
            $table->string('document_type', 20)->default('NIT');
            $table->string('document_number', 50)->unique();
            $table->string('nic', 50)->nullable();
            $table->string('email', 255)->unique();
            $table->string('phone', 20)->nullable();
            $table->string('mobile', 20)->nullable();
            $table->text('address')->nullable();
            $table->foreignId('department_id')->nullable()->constrained('departments')->onDelete('set null')->onUpdate('cascade');
            $table->foreignId('city_id')->nullable()->constrained('cities')->onDelete('set null')->onUpdate('cascade');
            $table->decimal('monthly_consumption_kwh', 10, 2)->nullable();
            $table->decimal('tariff_cop_kwh', 10, 2)->nullable();
            $table->foreignId('responsible_user_id')->nullable()->constrained('users')->onDelete('set null')->onUpdate('cascade');
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
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
