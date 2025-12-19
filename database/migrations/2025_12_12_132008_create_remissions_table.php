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
        Schema::create('remissions', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->foreignId('from_warehouse_id')->constrained('warehouses')->onDelete('restrict');
            $table->foreignId('to_warehouse_id')->nullable()->constrained('warehouses')->onDelete('set null');
            $table->foreignId('to_project_id')->nullable()->constrained('projects')->onDelete('set null');
            $table->enum('remission_type', ['transfer', 'dispatch', 'loan', 'return'])->default('dispatch');
            $table->enum('status', ['draft', 'pending', 'in_transit', 'received', 'cancelled'])->default('draft');
            $table->date('issue_date');
            $table->date('expected_delivery_date')->nullable();
            $table->date('actual_delivery_date')->nullable();
            $table->string('carrier_name')->nullable();
            $table->string('tracking_number', 100)->nullable();
            $table->string('vehicle_plate', 20)->nullable();
            $table->string('driver_name')->nullable();
            $table->string('driver_phone', 20)->nullable();
            $table->text('delivery_address')->nullable();
            $table->string('delivery_contact_name')->nullable();
            $table->string('delivery_contact_phone', 20)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('prepared_by')->constrained('users')->onDelete('restrict');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('received_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('remissions');
    }
};