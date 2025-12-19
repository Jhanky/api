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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique()->comment('PROY-2024-0001');
            $table->foreignId('client_id')->constrained()->onDelete('restrict')->onUpdate('cascade');
            $table->foreignId('quotation_id')->nullable()->constrained()->onDelete('set null')->onUpdate('cascade')->comment('Cotización que originó el proyecto');
            $table->foreignId('project_type_id')->constrained()->onDelete('restrict')->onUpdate('cascade');
            $table->foreignId('current_state_id')->constrained('project_states')->onDelete('restrict')->onUpdate('cascade');
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->text('installation_address');
            $table->foreignId('department_id')->nullable()->constrained()->onDelete('set null')->onUpdate('cascade');
            $table->foreignId('city_id')->nullable()->constrained()->onDelete('set null')->onUpdate('cascade');
            $table->string('coordinates', 50)->nullable()->comment('lat,lng');
            $table->date('start_date')->nullable();
            $table->date('estimated_end_date')->nullable();
            $table->date('actual_end_date')->nullable();
            $table->decimal('contracted_value_cop', 15, 2);
            $table->decimal('total_cost_cop', 15, 2)->nullable();
            $table->foreignId('project_manager_id')->nullable()->constrained('users')->onDelete('set null')->onUpdate('cascade');
            $table->foreignId('technical_leader_id')->nullable()->constrained('users')->onDelete('set null')->onUpdate('cascade');
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');
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
        Schema::dropIfExists('projects');
    }
};