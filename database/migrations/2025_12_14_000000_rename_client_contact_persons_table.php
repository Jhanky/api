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
        Schema::rename('client_contact_persons', 'client_contact_people');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('client_contact_people', 'client_contact_persons');
    }
};
