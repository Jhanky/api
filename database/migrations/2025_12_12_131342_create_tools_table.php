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
        Schema::create('tools', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('tool_category_id')->constrained('tool_categories')->onDelete('cascade');
            $table->foreignId('tool_status_id')->constrained('tool_statuses')->onDelete('cascade');
            $table->string('serial_number', 100)->nullable();
            $table->string('model')->nullable();
            $table->string('brand')->nullable();
            $table->date('purchase_date')->nullable();
            $table->date('warranty_expiry')->nullable();
            $table->string('location')->nullable();
            $table->json('specifications')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tools');
    }
};
