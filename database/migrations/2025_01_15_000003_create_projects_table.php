<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20);
            $table->string('name', 100);
            $table->unsignedBigInteger('quotation_id')->unique();
            $table->enum('status', ['activo', 'finalizado', 'pendiente'])->default('activo');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->timestamps();
            
            $table->foreign('quotation_id')->references('quotation_id')->on('quotations');
        });
    }

    public function down()
    {
        Schema::dropIfExists('projects');
    }
};