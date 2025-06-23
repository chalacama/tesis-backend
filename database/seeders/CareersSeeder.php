<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Career;
class CareersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         $careers = [
            'Administración de Empresas',
            'Administración Pública',
            'Agroindustria',
            'Computación',
            'Ingeniería Agricola',
            'Ingeniería Ambiental',
            'Medicina Veterinaria',
            'Turismo',
            'Ingeniería Agroforestal',
            'Electrónica y Automatización',
            'Ingeniería en Riesgos de Desastres',
            'Ingeniería de la Producción',
            'Biotecnologia',
            'Marketing Digital',
            'Gestión de la Innovación Organizacional y Productividad',
            'Turismo y Hotelería',
        ];

        foreach ($careers as $career) {
            Career::create(['name' => $career]);
        }
    }
}
