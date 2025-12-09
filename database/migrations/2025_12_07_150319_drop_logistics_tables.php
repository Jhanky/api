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
        Schema::dropIfExists('project_materials');
        Schema::dropIfExists('materials');
        Schema::dropIfExists('warehouses');
        Schema::dropIfExists('remissions');
        Schema::dropIfExists('tools');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No restoration logic as this is a destructive operation requested by user
    }
};
