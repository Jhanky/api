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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->foreignId('ticket_type_id')->constrained()->onDelete('restrict');
            $table->foreignId('priority_id')->constrained('ticket_priorities')->onDelete('restrict');
            $table->foreignId('state_id')->constrained('ticket_states')->onDelete('restrict');
            $table->foreignId('project_id')->nullable()->constrained('projects')->onDelete('set null');
            $table->foreignId('client_id')->nullable()->constrained('clients')->onDelete('set null');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->string('title', 255);
            $table->text('description');
            $table->dateTime('due_date')->nullable();
            $table->dateTime('resolved_at')->nullable();
            $table->text('resolution_notes')->nullable();
            $table->integer('estimated_hours')->nullable();
            $table->integer('actual_hours')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
