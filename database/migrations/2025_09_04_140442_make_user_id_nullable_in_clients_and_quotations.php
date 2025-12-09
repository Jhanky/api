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
        // Hacer user_id nullable en clients
        Schema::table('clients', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->change();
        });

        // Hacer user_id nullable en quotations
        Schema::table('quotations', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir user_id a not null en clients
        Schema::table('clients', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable(false)->change();
        });

        // Revertir user_id a not null en quotations
        Schema::table('quotations', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable(false)->change();
        });
    }
};