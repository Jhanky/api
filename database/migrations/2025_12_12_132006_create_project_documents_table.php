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
        Schema::create('project_documents', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('document_type_id')->nullable();
            $table->unsignedBigInteger('milestone_id')->nullable();
            $table->string('name');
            $table->string('original_filename');
            $table->string('file_path');
            $table->string('mime_type', 100);
            $table->bigInteger('file_size');
            $table->string('file_extension', 10);
            $table->text('description')->nullable();
            $table->date('document_date')->nullable();
            $table->string('responsible')->nullable();
            $table->string('version', 20)->default('1.0');
            $table->foreignId('replaces_document_id')->nullable()->constrained('project_documents')->onDelete('set null');
            $table->boolean('is_public')->default(false);
            $table->boolean('requires_approval')->default(false);
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->datetime('approved_at')->nullable();
            $table->foreignId('uploaded_by')->constrained('users')->onDelete('restrict');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_documents');
    }
};
