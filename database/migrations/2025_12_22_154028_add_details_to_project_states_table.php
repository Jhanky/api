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
        Schema::table('project_states', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('name');
            $table->string('color')->nullable()->after('slug');
            $table->string('icon')->nullable()->after('color');
            $table->string('phase')->nullable()->after('icon');
            $table->integer('display_order')->default(0)->after('phase');
            $table->integer('estimated_duration')->nullable()->comment('in days')->after('display_order');
            $table->boolean('is_final')->default(false)->after('estimated_duration');
            $table->boolean('requires_approval')->default(false)->after('is_final');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_states', function (Blueprint $table) {
            $table->dropColumn([
                'slug',
                'color',
                'icon',
                'phase',
                'display_order',
                'estimated_duration',
                'is_final',
                'requires_approval'
            ]);
        });
    }
};
