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
      Schema::create('panels', function (Blueprint $table) {
            $table->id('panel_id');
            $table->string('brand', 100);
            $table->string('model', 100);
            $table->float('power');
            $table->string('type', 50);
            $table->text('technical_sheet_url')->nullable();
            $table->float('price');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('panels');
    }
};
