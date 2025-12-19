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
        Schema::table('quotations', function (Blueprint $table) {
            $table->decimal('subtotal', 15, 2)->default(0)->after('withholding_percentage');
            $table->decimal('profit', 15, 2)->default(0)->after('subtotal');
            $table->decimal('profit_iva', 15, 2)->default(0)->after('profit');
            $table->decimal('commercial_management', 15, 2)->default(0)->after('profit_iva');
            $table->decimal('administration', 15, 2)->default(0)->after('commercial_management');
            $table->decimal('contingency', 15, 2)->default(0)->after('administration');
            $table->decimal('withholdings', 15, 2)->default(0)->after('contingency');
            $table->decimal('total_value', 15, 2)->default(0)->after('withholdings');
            $table->decimal('subtotal2', 15, 2)->default(0)->after('total_value');
            $table->decimal('subtotal3', 15, 2)->default(0)->after('subtotal2');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropColumn([
                'subtotal',
                'profit',
                'profit_iva',
                'commercial_management',
                'administration',
                'contingency',
                'withholdings',
                'total_value',
                'subtotal2',
                'subtotal3'
            ]);
        });
    }
};
