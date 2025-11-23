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
            'contry' => 'Ecuador',
            'province' => 'Manabí',
            'canton' => 'bolívar',
            'educational_unit_id' => 1,
        ]);
        Sede::create([
            'contry' => 'Ecuador',
            'province' => 'Galapagos',
            'canton' => 'San Cristóbal',
            'educational_unit_id' => 1,
        ]);
        // Sedes del Ecuador - Otras instituciones o Ninguno
        Sede::create([
            'contry' => 'Ecuador',
            'educational_unit_id' => 2,
        ]);
        Sede::create([
            'contry' => 'Ecuador',
            'educational_unit_id' => 3,
        ]);
        // Sede del Colegio Raymundo Aveiga
        Sede::create([
            'contry' => 'Ecuador',
            'province' => 'Manabí',
            'canton' => 'Chone',
            'educational_unit_id' => 4,
        ]);
    }
}
