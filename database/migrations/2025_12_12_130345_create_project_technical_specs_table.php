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
        Schema::create('project_technical_specs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->json('electrical_specs')->nullable()->comment('Especificaciones eléctricas');
            $table->json('structural_specs')->nullable()->comment('Especificaciones estructurales');
            $table->json('environmental_conditions')->nullable()->comment('Condiciones ambientales');
            $table->json('regulatory_compliance')->nullable()->comment('Cumplimiento regulatorio');
            $table->text('technical_notes')->nullable();
            $table->foreignId('updated_by')->constrained('users')->onDelete('restrict');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_technical_specs');
    }
};