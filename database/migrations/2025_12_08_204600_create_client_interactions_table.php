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
        Schema::create('client_interactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('type', ['meeting', 'call', 'whatsapp', 'email', 'other']);
            $table->text('description');
            $table->dateTime('interaction_date');
            $table->timestamps();

            $table->index(['client_id', 'interaction_date']);
            $table->index(['user_id']);
        });

        // Tabla para archivos adjuntos de interacciones
        Schema::create('client_interaction_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_interaction_id')->constrained('client_interactions')->onDelete('cascade');
            $table->string('original_name');
            $table->string('stored_name');
            $table->string('file_path');
            $table->string('mime_type');
            $table->bigInteger('file_size');
            $table->string('file_type')->nullable(); // pdf, image, document, etc.
            $table->foreignId('uploaded_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            $table->index(['client_interaction_id']);
            $table->index(['uploaded_by']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_interaction_attachments');
        Schema::dropIfExists('client_interactions');
    }
};
