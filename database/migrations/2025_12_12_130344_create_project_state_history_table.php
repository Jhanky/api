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
        Schema::create('project_state_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('from_state_id')->nullable()->constrained('project_states')->onDelete('set null');
            $table->foreignId('to_state_id')->constrained('project_states')->onDelete('restrict');
            $table->foreignId('changed_by')->constrained('users')->onDelete('restrict');
            $table->text('reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_state_history');
    }
};