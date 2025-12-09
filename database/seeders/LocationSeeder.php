<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Location;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $locations = [
            // San Salvador
            ['department' => 'San Salvador', 'municipality' => 'San Salvador', 'radiation' => 1420.50],
            ['department' => 'San Salvador', 'municipality' => 'Soyapango', 'radiation' => 1415.75],
            ['department' => 'San Salvador', 'municipality' => 'Santa Tecla', 'radiation' => 1430.25],
            ['department' => 'San Salvador', 'municipality' => 'Mejicanos', 'radiation' => 1405.80],
            ['department' => 'San Salvador', 'municipality' => 'Apopa', 'radiation' => 1445.90],
            
            // La Libertad
            ['department' => 'La Libertad', 'municipality' => 'Santa Tecla', 'radiation' => 1430.25],
            ['department' => 'La Libertad', 'municipality' => 'Antiguo Cuscatlán', 'radiation' => 1420.50],
            ['department' => 'La Libertad', 'municipality' => 'Nuevo Cuscatlán', 'radiation' => 1415.75],
            ['department' => 'La Libertad', 'municipality' => 'San Juan Opico', 'radiation' => 1455.60],
            ['department' => 'La Libertad', 'municipality' => 'Colón', 'radiation' => 1445.90],
            
            // Santa Ana
            ['department' => 'Santa Ana', 'municipality' => 'Santa Ana', 'radiation' => 1465.40],
            ['department' => 'Santa Ana', 'municipality' => 'Chalchuapa', 'radiation' => 1475.20],
            ['department' => 'Santa Ana', 'municipality' => 'Metapán', 'radiation' => 1485.30],
            ['department' => 'Santa Ana', 'municipality' => 'Coatepeque', 'radiation' => 1455.60],
            ['department' => 'Santa Ana', 'municipality' => 'El Congo', 'radiation' => 1465.40],
            
            // Sonsonate
            ['department' => 'Sonsonate', 'municipality' => 'Sonsonate', 'radiation' => 1445.90],
            ['department' => 'Sonsonate', 'municipality' => 'Acajutla', 'radiation' => 1430.25],
            ['department' => 'Sonsonate', 'municipality' => 'Izalco', 'radiation' => 1455.60],
            ['department' => 'Sonsonate', 'municipality' => 'Nahuizalco', 'radiation' => 1465.40],
            ['department' => 'Sonsonate', 'municipality' => 'Juayúa', 'radiation' => 1475.20],
            
            // Ahuachapán
            ['department' => 'Ahuachapán', 'municipality' => 'Ahuachapán', 'radiation' => 1485.30],
            ['department' => 'Ahuachapán', 'municipality' => 'Atiquizaya', 'radiation' => 1495.80],
            ['department' => 'Ahuachapán', 'municipality' => 'Jujutla', 'radiation' => 1475.20],
            ['department' => 'Ahuachapán', 'municipality' => 'Tacuba', 'radiation' => 1465.40],
            ['department' => 'Ahuachapán', 'municipality' => 'Turín', 'radiation' => 1485.30],
            
            // Chalatenango
            ['department' => 'Chalatenango', 'municipality' => 'Chalatenango', 'radiation' => 1420.50],
            ['department' => 'Chalatenango', 'municipality' => 'Nueva Concepción', 'radiation' => 1430.25],
            ['department' => 'Chalatenango', 'municipality' => 'La Palma', 'radiation' => 1405.80],
            ['department' => 'Chalatenango', 'municipality' => 'El Paraíso', 'radiation' => 1400.00],
            ['department' => 'Chalatenango', 'municipality' => 'Dulce Nombre de María', 'radiation' => 1420.50],
            
            // Cuscatlán
            ['department' => 'Cuscatlán', 'municipality' => 'Cojutepeque', 'radiation' => 1415.75],
            ['department' => 'Cuscatlán', 'municipality' => 'Suchitoto', 'radiation' => 1420.50],
            ['department' => 'Cuscatlán', 'municipality' => 'San Pedro Perulapán', 'radiation' => 1400.00],
            ['department' => 'Cuscatlán', 'municipality' => 'Tecoluca', 'radiation' => 1430.25],
            ['department' => 'Cuscatlán', 'municipality' => 'San Rafael Cedros', 'radiation' => 1415.75],
            
            // La Paz
            ['department' => 'La Paz', 'municipality' => 'Zacatecoluca', 'radiation' => 1445.90],
            ['department' => 'La Paz', 'municipality' => 'Santiago Nonualco', 'radiation' => 1455.60],
            ['department' => 'La Paz', 'municipality' => 'San Pedro Nonualco', 'radiation' => 1430.25],
            ['department' => 'La Paz', 'municipality' => 'Olocuilta', 'radiation' => 1420.50],
            ['department' => 'La Paz', 'municipality' => 'San Luis La Herradura', 'radiation' => 1465.40],
            
            // Cabañas
            ['department' => 'Cabañas', 'municipality' => 'Sensuntepeque', 'radiation' => 1430.25],
            ['department' => 'Cabañas', 'municipality' => 'Ilobasco', 'radiation' => 1420.50],
            ['department' => 'Cabañas', 'municipality' => 'Victoria', 'radiation' => 1445.90],
            ['department' => 'Cabañas', 'municipality' => 'San Isidro', 'radiation' => 1415.75],
            ['department' => 'Cabañas', 'municipality' => 'Dolores', 'radiation' => 1420.50],
            
            // San Vicente
            ['department' => 'San Vicente', 'municipality' => 'San Vicente', 'radiation' => 1455.60],
            ['department' => 'San Vicente', 'municipality' => 'Apastepeque', 'radiation' => 1445.90],
            ['department' => 'San Vicente', 'municipality' => 'Tecoluca', 'radiation' => 1430.25],
            ['department' => 'San Vicente', 'municipality' => 'Verapaz', 'radiation' => 1420.50],
            ['department' => 'San Vicente', 'municipality' => 'San Esteban Catarina', 'radiation' => 1415.75],
            
            // Usulután
            ['department' => 'Usulután', 'municipality' => 'Usulután', 'radiation' => 1465.40],
            ['department' => 'Usulután', 'municipality' => 'Santiago de María', 'radiation' => 1475.20],
            ['department' => 'Usulután', 'municipality' => 'Jucuapa', 'radiation' => 1455.60],
            ['department' => 'Usulután', 'municipality' => 'Berlín', 'radiation' => 1445.90],
            ['department' => 'Usulután', 'municipality' => 'Alegría', 'radiation' => 1485.30],
            
            // San Miguel
            ['department' => 'San Miguel', 'municipality' => 'San Miguel', 'radiation' => 1475.20],
            ['department' => 'San Miguel', 'municipality' => 'Chinameca', 'radiation' => 1465.40],
            ['department' => 'San Miguel', 'municipality' => 'Nueva Guadalupe', 'radiation' => 1455.60],
            ['department' => 'San Miguel', 'municipality' => 'Lolotique', 'radiation' => 1485.30],
            ['department' => 'San Miguel', 'municipality' => 'Moncagua', 'radiation' => 1465.40],
            
            // Morazán
            ['department' => 'Morazán', 'municipality' => 'San Francisco Gotera', 'radiation' => 1445.90],
            ['department' => 'Morazán', 'municipality' => 'Sociedad', 'radiation' => 1430.25],
            ['department' => 'Morazán', 'municipality' => 'Jocoro', 'radiation' => 1455.60],
            ['department' => 'Morazán', 'municipality' => 'El Rosario', 'radiation' => 1420.50],
            ['department' => 'Morazán', 'municipality' => 'Cacaopera', 'radiation' => 1415.75],
            
            // La Unión
            ['department' => 'La Unión', 'municipality' => 'La Unión', 'radiation' => 1485.30],
            ['department' => 'La Unión', 'municipality' => 'San Alejo', 'radiation' => 1495.80],
            ['department' => 'La Unión', 'municipality' => 'El Carmen', 'radiation' => 1475.20],
            ['department' => 'La Unión', 'municipality' => 'Intipucá', 'radiation' => 1485.30],
            ['department' => 'La Unión', 'municipality' => 'Pasaquina', 'radiation' => 1465.40],
        ];

        foreach ($locations as $location) {
            Location::create($location);
        }
    }
}
