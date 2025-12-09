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
        // Insertar los métodos de pago
        \DB::table('payment_methods')->insert([
            [
                'code' => 'TCD',
                'name' => 'Transferencia desde cuenta Davivienda E4(TCD)',
                'description' => 'Transferencia bancaria desde cuenta empresarial Davivienda E4',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code' => 'CP',
                'name' => 'Transferencia desde Cuenta personal(CP)',
                'description' => 'Transferencia bancaria desde cuenta personal',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code' => 'EF',
                'name' => 'Efectivo(EF)',
                'description' => 'Pago en efectivo',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Eliminar los métodos de pago
        \DB::table('payment_methods')->whereIn('code', ['TCD', 'CP', 'EF'])->delete();
    }
};
