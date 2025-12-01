<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Sede;
class SedeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Sede::create([
            'contry' => 'ECUADOR',
            'province_id' => 13,   // 👈 ID real de Manabí en tu JSON
            'canton_id'   => 1303, // 👈 ID real de Bolívar en tu JSON
            'educational_unit_id' => 1,
        ]);
        Sede::create([
            'contry' => 'ECUADOR',
            'province_id' => 20,   
            'canton_id'   => 2001, 
            'educational_unit_id' => 1,
        ]);
        // Sedes del Ecuador - Otras instituciones o Ninguno
        Sede::create([
            'contry' => 'ECUADOR',
            'educational_unit_id' => 2,
        ]);
        Sede::create([
            'contry' => 'ECUADOR',
            'educational_unit_id' => 3,
        ]);
        // Sede del Colegio Raymundo Aveiga
        Sede::create([
            'contry' => 'ECUADOR',
            'province_id' => 13,   // 👈 ID real de Manabí en tu JSON
            'canton_id'   => 1303, // 👈 ID real de Bolívar en tu JSON
            'educational_unit_id' => 4,
        ]);
    }
}
