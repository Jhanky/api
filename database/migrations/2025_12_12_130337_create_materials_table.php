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
        Schema::create('materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('material_categories')->onDelete('restrict');
            $table->string('name', 255);
            $table->string('code', 50)->unique();
            $table->string('brand', 100)->nullable();
            $table->string('model', 100)->nullable();
            $table->text('description')->nullable();
            $table->string('unit_measure', 20)->default('unidad');
            $table->decimal('cost_price', 12, 2)->nullable();
            $table->decimal('selling_price', 12, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('materials');
    }
};