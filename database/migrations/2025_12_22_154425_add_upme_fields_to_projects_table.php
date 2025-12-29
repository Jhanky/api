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
            $table->string('upme_radicado_number')->nullable()->after('current_state_id');
            $table->string('upme_case_number')->nullable()->after('upme_radicado_number');
            $table->enum('upme_status', ['NO_RADICADO', 'RADICADO', 'RESPUESTA_RECIBIDA'])->default('NO_RADICADO')->after('upme_case_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['upme_radicado_number', 'upme_case_number', 'upme_status']);
        });
    }
};
