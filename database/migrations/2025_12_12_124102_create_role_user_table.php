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
        Schema::create('role_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('role_id')->constrained('roles')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamp('assigned_at')->useCurrent();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->onDelete('set null')->onUpdate('cascade');
            $table->unique(['user_id', 'role_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('role_user');
    }
};
