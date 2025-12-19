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
        Schema::create('client_contact_people', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade')->onUpdate('cascade');
            $table->string('name', 255);
            $table->string('position', 100)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('mobile', 20)->nullable();
            $table->string('whatsapp', 20)->nullable();
            $table->enum('preferred_contact_method', ['email', 'phone', 'whatsapp', 'presential'])->default('email');
            $table->json('availability')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->boolean('is_decision_maker')->default(false);
            $table->text('notes')->nullable();
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
        Schema::dropIfExists('client_contact_people');
    }
};
