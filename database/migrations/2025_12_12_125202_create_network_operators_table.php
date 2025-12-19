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
        Schema::create('network_operators', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('code', 50)->unique();
            $table->string('contact_email', 255)->nullable();
            $table->string('contact_phone', 20)->nullable();
            $table->text('address')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('network_operators');
    }
};