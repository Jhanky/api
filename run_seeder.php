<?php

require_once __DIR__ . '/vendor/autoload.php';

// Load environment
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Bootstrap Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    // Ejecutar todos los seeders necesarios para suministros
    $seeders = [
        \Database\Seeders\PanelSeeder::class,
        \Database\Seeders\InverterSeeder::class,
        \Database\Seeders\BatterySeeder::class,
    ];

    foreach ($seeders as $seederClass) {
        echo "Ejecutando seeder: " . class_basename($seederClass) . "\n";
        $seeder = new $seederClass();
        $seeder->run();
        echo "✅ " . class_basename($seederClass) . " ejecutado exitosamente\n";
    }

    echo "🎉 Todos los seeders de suministros ejecutados correctamente\n";
} catch (Exception $e) {
    echo "❌ Error ejecutando seeders: " . $e->getMessage() . "\n";
}
