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
        Schema::table('projects', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropForeign(['city_id']);
            $table->dropColumn(['department_id', 'city_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->foreignId('department_id')->nullable()->after('installation_address')->constrained('departments')->onDelete('set null')->onUpdate('cascade');
            $table->foreignId('city_id')->nullable()->after('department_id')->constrained('cities')->onDelete('set null')->onUpdate('cascade');
        });
    }
};
