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
            'province' => 'Manabí',
            'canton' => 'bolívar',
            'educational_unit_id' => 1,
        ]);
        Sede::create([
            'province' => 'Galapagos',
            'canton' => 'San Cristóbal',
            'educational_unit_id' => 1,
        ]);
        Sede::create([
            'province' => 'Manabí',
            'canton' => 'Bolívar',
            'educational_unit_id' => 2,
        ]);
    }
}
