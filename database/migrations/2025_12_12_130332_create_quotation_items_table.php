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
        Schema::create('quotation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->constrained()->onDelete('cascade')->onUpdate('cascade');
            $table->string('description', 255);
            $table->string('category', 100)->nullable()->comment('Mano de obra, Material, Trámites');
            $table->decimal('quantity', 10, 2);
            $table->string('unit_measure', 20)->default('unidad');
            $table->decimal('unit_price_cop', 12, 2);
            $table->decimal('profit_percentage', 5, 3)->nullable();
            $table->smallInteger('display_order')->unsigned()->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotation_items');
    }
};