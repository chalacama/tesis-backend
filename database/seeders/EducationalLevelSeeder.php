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
        // nivel 1
        EducationalLevel::create([
            'name' => 'Educación General Básica',
            'description' => 'Es obligatoria y se divide en varios subniveles: preparatoria (5-6 años), básica elemental (1-3 años), básica media (4-6 años) y básica superior (7-9 años).',
            'period' => 'EGB',
            'max_periods' => 10,
        ]);
        // nivel 2
        EducationalLevel::create([
            'name' => 'Bachillerato',
            'description' => 'Consta de tres años (1ro a 3ro de bachillerato), y busca proporcionar una formación general y preparación para la vida universitaria o laboral.',
            'period' => 'Bachiller',
            'max_periods' => 3,
        ]);

        // nivel 3
        EducationalLevel::create([
            'name' => 'Educación Superior',
            'description' => 'Se divide en niveles técnico superior, tercer nivel (universitario) y cuarto nivel (posgrado).',
            'period' => 'Ciclo/Semestre',
            'max_periods' => 12,
        ]);
        // nivel 4
        EducationalLevel::create([
            'name' => 'Cuarto Nivel (Posgrado)',
            'description' => 'Nivel de posgrado que incluye Especializaciones, Maestrías (requisito docente) y Doctorados (PhD - máximo grado académico).',
            'period' => 'Módulo/Semestre',
            'max_periods' => 8, 
        ]);
        // otros niveles 5
        EducationalLevel::create([
            'name' => 'Otros',
            'description' => 'Nivel educativo que no se encuentra dentro de las categorías establecidas.',
            
        ]);
        // ningun nivel 6
        EducationalLevel::create([
            'name' => 'Ninguno',
            'description' => 'No pose estudios.',
            
        ]);
        
        
    }
}
