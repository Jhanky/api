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
        Schema::table('project_upme_details', function (Blueprint $table) {
            $table->text('filing_comments')->nullable()->after('filing_date');
            $table->date('response_date')->nullable()->after('consultation_url');
            $table->string('response_number')->nullable()->after('response_date');
            $table->text('response_comments')->nullable()->after('response_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_upme_details', function (Blueprint $table) {
            $table->dropColumn(['filing_comments', 'response_date', 'response_number', 'response_comments']);
        });
    }
};
