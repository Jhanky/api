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
        Schema::table('project_documents', function (Blueprint $table) {
            $table->foreignId('required_document_id')->nullable()->constrained('required_documents')->nullOnDelete()->after('project_id');
            // Make document_type_id nullable if it exists, or we leave it as is if we can't easily change it without doctrine/dbal.
            // Assuming we just add required_document_id for now.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_documents', function (Blueprint $table) {
            $table->dropForeign(['required_document_id']);
            $table->dropColumn('required_document_id');
        });
    }
};
