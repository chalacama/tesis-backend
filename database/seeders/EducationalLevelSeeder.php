<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\EducationalLevel;
class EducationalLevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        EducationalLevel::create([
            'name' => 'Educación Superior',
            'description' => 'Se divide en niveles técnico superior, tercer nivel (universitario) y cuarto nivel (posgrado).',
            'period' => 'Semestre',
            'max_periods' => 10,
        ]);
        EducationalLevel::create([
            'name' => 'Bachillerato',
            'description' => 'Consta de tres años (1ro a 3ro de bachillerato), y busca proporcionar una formación general y preparación para la vida universitaria o laboral.',
            'period' => 'Bachiller',
            'max_periods' => 3,
        ]);
        EducationalLevel::create([
            'name' => 'Educación General Básica',
            'description' => 'Es obligatoria y se divide en varios subniveles: preparatoria (5-6 años), básica elemental (1-3 años), básica media (4-6 años) y básica superior (7-9 años).',
            'period' => 'EGB',
            'max_periods' => 10,
        ]);
    }
}
