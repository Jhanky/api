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
        Schema::create('quotation_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->constrained()->onDelete('cascade')->onUpdate('cascade');
            $table->string('product_type', 20)->comment('panel, inverter, battery');
            $table->unsignedBigInteger('product_id');
            $table->string('snapshot_brand', 100);
            $table->string('snapshot_model', 100);
            $table->text('snapshot_specs')->nullable()->comment('JSON con especificaciones');
            $table->decimal('quantity', 10, 2);
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
        Schema::dropIfExists('quotation_products');
    }
};