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
        Schema::create('milestones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('milestone_type_id')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('planned_date');
            $table->date('actual_date')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'completed', 'delayed', 'cancelled'])->default('pending');
            $table->decimal('amount_cop', 15, 2)->nullable();
            $table->boolean('requires_verification')->default(false);
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->datetime('verified_at')->nullable();
            $table->text('verification_notes')->nullable();
            $table->foreignId('responsible_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('milestones');
    }
};
