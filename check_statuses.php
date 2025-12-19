<?php

require_once __DIR__ . '/vendor/autoload.php';

// Load environment
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    // Get all quotation statuses
    $statuses = \App\Models\QuotationStatus::orderBy('display_order')->get();

    echo "=== ESTADOS DE COTIZACIÓN EN LA BASE DE DATOS ===\n\n";

    if ($statuses->isEmpty()) {
        echo "No hay estados de cotización en la base de datos.\n";
        echo "Ejecutando seeder...\n";

        $seeder = new \Database\Seeders\QuotationStatusSeeder();
        $seeder->run();

        $statuses = \App\Models\QuotationStatus::orderBy('display_order')->get();
        echo "Seeder ejecutado. Estados creados:\n\n";
    }

    foreach ($statuses as $status) {
        echo "ID: {$status->id}\n";
        echo "Nombre: {$status->name}\n";
        echo "Slug: {$status->slug}\n";
        echo "Descripción: {$status->description}\n";
        echo "Color: {$status->color}\n";
        echo "Orden: {$status->display_order}\n";
        echo "Final: " . ($status->is_final ? 'Sí' : 'No') . "\n";
        echo "Activo: " . ($status->is_active ? 'Sí' : 'No') . "\n";
        echo "Creado: {$status->created_at}\n";
        echo "Actualizado: {$status->updated_at}\n";
        echo str_repeat("-", 50) . "\n";
    }

    echo "\n=== DATOS PARA SEEDER ===\n\n";
    echo "[\n";
    foreach ($statuses as $index => $status) {
        echo "    [\n";
        echo "        'name' => '{$status->name}',\n";
        echo "        'slug' => '{$status->slug}',\n";
        echo "        'description' => '" . addslashes($status->description) . "',\n";
        echo "        'color' => '{$status->color}',\n";
        echo "        'display_order' => {$status->display_order},\n";
        echo "        'is_final' => " . ($status->is_final ? 'true' : 'false') . ",\n";
        echo "        'is_active' => " . ($status->is_active ? 'true' : 'false') . ",\n";
        echo "    ],\n";
    }
    echo "]\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
