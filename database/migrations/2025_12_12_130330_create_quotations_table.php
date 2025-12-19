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
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique()->comment('COT-2024-0001');
            $table->foreignId('client_id')->constrained()->onDelete('restrict')->onUpdate('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('restrict')->onUpdate('cascade')->comment('Usuario que creó');
            $table->foreignId('status_id')->constrained('quotation_statuses')->onDelete('restrict')->onUpdate('cascade');
            $table->string('project_name', 200);
            $table->foreignId('system_type_id')->constrained()->onDelete('restrict')->onUpdate('cascade');
            $table->foreignId('grid_type_id')->nullable()->constrained()->onDelete('set null')->onUpdate('cascade');
            $table->decimal('power_kwp', 8, 2)->comment('Potencia en kWp');
            $table->smallInteger('panel_count')->unsigned();
            $table->boolean('requires_financing')->default(false);
            $table->decimal('profit_percentage', 5, 3)->default(0.000);
            $table->decimal('iva_profit_percentage', 5, 3)->default(0.190);
            $table->decimal('commercial_management_percentage', 5, 3)->default(0.000);
            $table->decimal('administration_percentage', 5, 3)->default(0.000);
            $table->decimal('contingency_percentage', 5, 3)->default(0.000);
            $table->decimal('withholding_percentage', 5, 3)->default(0.000);
            $table->date('issue_date');
            $table->date('expiration_date');
            $table->date('approved_at')->nullable();
            $table->text('notes')->nullable();
            $table->text('terms_conditions')->nullable();
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
        Schema::dropIfExists('quotations');
    }
};
