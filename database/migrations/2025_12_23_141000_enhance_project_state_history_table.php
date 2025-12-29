<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('project_state_history', function (Blueprint $table) {
            if (!Schema::hasColumn('project_state_history', 'changed_at')) {
                $table->timestamp('changed_at')->nullable()->after('changed_by');
            }
            
            if (!Schema::hasColumn('project_state_history', 'started_at')) {
                $table->timestamp('started_at')->nullable()->after('changed_by');
            }
            
            if (!Schema::hasColumn('project_state_history', 'ended_at')) {
                $table->timestamp('ended_at')->nullable()->after('started_at');
            }
            
            if (!Schema::hasColumn('project_state_history', 'duration_days')) {
                $table->integer('duration_days')->nullable()->after('ended_at');
            }
            
            if (!Schema::hasColumn('project_state_history', 'notes')) {
                $table->text('notes')->nullable()->after('reason');
            }
        });

        // Populate changed_at with created_at if it's null
        if (Schema::hasColumn('project_state_history', 'changed_at')) {
            DB::statement('UPDATE project_state_history SET changed_at = created_at WHERE changed_at IS NULL');
        }
        
        // Populate started_at with changed_at (or created_at) for existing records
        if (Schema::hasColumn('project_state_history', 'started_at')) {
            DB::statement('UPDATE project_state_history SET started_at = created_at WHERE started_at IS NULL');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_state_history', function (Blueprint $table) {
            $columns = ['started_at', 'ended_at', 'duration_days', 'notes'];
            // We usually don't drop changed_at if it was added as a fix, but to be strict to this migration:
            if (Schema::hasColumn('project_state_history', 'changed_at')) {
                // Check if we should drop it? Maybe keep it if it's useful. 
                // For now, let's only drop the new enhancements.
                // $columns[] = 'changed_at'; 
            }
            $table->dropColumn($columns);
        });
    }
};
