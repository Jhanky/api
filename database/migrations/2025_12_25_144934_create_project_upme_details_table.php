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
        Schema::create('project_upme_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->string('radicado_number')->nullable();
            $table->string('case_number')->nullable();
            $table->enum('status', ['NO_RADICADO', 'RADICADO', 'RESPUESTA_RECIBIDA'])->default('NO_RADICADO');
            $table->date('filing_date')->nullable();
            $table->string('consultation_url')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_upme_details');
    }
};
