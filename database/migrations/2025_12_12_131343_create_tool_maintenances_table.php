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
        Schema::create('tool_maintenances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tool_id')->constrained('tools')->onDelete('cascade');
            $table->date('maintenance_date');
            $table->text('description');
            $table->foreignId('performed_by')->constrained('users')->onDelete('cascade');
            $table->decimal('cost', 10, 2)->nullable();
            $table->date('next_maintenance_date')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tool_maintenances');
    }
};
