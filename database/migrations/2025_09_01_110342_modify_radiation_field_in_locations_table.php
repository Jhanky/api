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
        Schema::table('locations', function (Blueprint $table) {
            // Cambiar el campo radiation de decimal(5,2) a decimal(8,2) para permitir valores como 1420
            $table->decimal('radiation', 8, 2)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            // Revertir el cambio
            $table->decimal('radiation', 5, 2)->change();
        });
    }
};
