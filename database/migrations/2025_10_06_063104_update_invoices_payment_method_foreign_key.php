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
        // Paso 1: Agregar nueva columna payment_method_id
        Schema::table('invoices', function (Blueprint $table) {
            $table->unsignedBigInteger('payment_method_id')->nullable()->after('payment_method')->comment('ID del método de pago');
        });

        // Paso 2: Migrar datos existentes
        $paymentMethods = \DB::table('payment_methods')->get()->keyBy('code');
        
        // Actualizar facturas con TCD
        \DB::table('invoices')
            ->where('payment_method', 'Transferencia desde cuenta Davivienda E4(TCD)')
            ->update(['payment_method_id' => $paymentMethods['TCD']->id]);
            
        // Actualizar facturas con CP
        \DB::table('invoices')
            ->where('payment_method', 'Transferencia desde Cuenta personal(CP)')
            ->update(['payment_method_id' => $paymentMethods['CP']->id]);
            
        // Actualizar facturas con EF
        \DB::table('invoices')
            ->where('payment_method', 'Efectivo(EF)')
            ->update(['payment_method_id' => $paymentMethods['EF']->id]);

        // Paso 3: Agregar foreign key constraint
        Schema::table('invoices', function (Blueprint $table) {
            $table->foreign('payment_method_id')->references('id')->on('payment_methods')->onDelete('set null');
        });

        // Paso 4: Eliminar la columna payment_method antigua
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('payment_method');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Paso 1: Agregar columna payment_method de vuelta
        Schema::table('invoices', function (Blueprint $table) {
            $table->enum('payment_method', [
                'Transferencia desde cuenta Davivienda E4(TCD)',
                'Transferencia desde Cuenta personal(CP)', 
                'Efectivo(EF)'
            ])->nullable()->after('payment_method_id')->comment('Método de pago utilizado');
        });

        // Paso 2: Migrar datos de vuelta
        $paymentMethods = \DB::table('payment_methods')->get()->keyBy('id');
        
        \DB::table('invoices')
            ->whereNotNull('payment_method_id')
            ->chunkById(100, function ($invoices) use ($paymentMethods) {
                foreach ($invoices as $invoice) {
                    $method = $paymentMethods[$invoice->payment_method_id] ?? null;
                    if ($method) {
                        \DB::table('invoices')
                            ->where('id', $invoice->id)
                            ->update(['payment_method' => $method->name]);
                    }
                }
            });

        // Paso 3: Eliminar foreign key constraint
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['payment_method_id']);
        });

        // Paso 4: Eliminar columna payment_method_id
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('payment_method_id');
        });
    }
};
