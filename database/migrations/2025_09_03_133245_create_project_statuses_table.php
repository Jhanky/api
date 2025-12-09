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
        Schema::create('project_statuses', function (Blueprint $table) {
            $table->id('status_id');
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->string('color', 7)->default('#3B82F6'); // Color hexadecimal
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Índices
            $table->index('name');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_statuses');
    }
};
