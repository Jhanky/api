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
        Schema::create('file_audit_log', function (Blueprint $table) {
            $table->id('log_id');
            $table->enum('entity_type', ['invoice', 'payment', 'document']);
            $table->unsignedBigInteger('entity_id');
            $table->enum('action', ['upload', 'delete', 'replace', 'download']);
            $table->string('file_path', 500)->nullable();
            $table->string('file_name', 255)->nullable();
            $table->bigInteger('file_size')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_audit_log');
    }
};
