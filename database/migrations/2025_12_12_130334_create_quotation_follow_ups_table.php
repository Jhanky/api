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
        Schema::create('quotation_follow_ups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->constrained()->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('from_status_id')->nullable()->constrained('quotation_statuses')->onDelete('set null')->onUpdate('cascade');
            $table->foreignId('to_status_id')->constrained('quotation_statuses')->onDelete('restrict')->onUpdate('cascade');
            $table->enum('action_type', ['status_change', 'client_feedback', 'revision_requested', 'price_negotiation', 'approval', 'rejection', 'expiration_reminder', 'general_note'])->default('general_note');
            $table->text('notes')->nullable();
            $table->text('client_comments')->nullable();
            $table->json('changes')->nullable()->comment('Campos modificados');
            $table->datetime('next_action_date')->nullable();
            $table->string('next_action_description', 255)->nullable();
            $table->boolean('next_action_completed')->default(false);
            $table->foreignId('user_id')->constrained()->onDelete('restrict')->onUpdate('cascade');
            $table->datetime('action_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotation_follow_ups');
    }
};