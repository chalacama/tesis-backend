<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\EducationalUser;
class EducationalUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //admin
        EducationalUser::create([
            'sede_id' => 1,
            'user_id' => 1,
            // 'career_id' => null,
            // 'level' => 1,
            // 'period' => 'Semestre',            
        ]);
        //ingenieri@
        EducationalUser::create([
            'sede_id' => 2,
            'user_id' => 2,
            'career_id' => 1,
            // 'educational_level_id' => 1,
            // 'level' => 2,
            
        ]); 
        //estudiante de la Espam Mfl
        EducationalUser::create([
            'sede_id' => 1,
            'user_id' => 3,
            'career_id' => 5,
            'educational_level_id' => 1,
            'level' => 8,
            
        ]);
        //Estudiante de colegio
        EducationalUser::create([
            'sede_id' => 3,
            'user_id' => 4,
            // 'career_id' => 5,
            'educational_level_id' => 2,
            'level' => 3,
        ]);

    }
}
