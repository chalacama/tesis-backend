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
            
            
        ]);
        //Tutor real de la espam no posen semestre ni nivel
        EducationalUser::create([
            'sede_id' => 1,
            'user_id' => 2,
            'career_id' => 5, 
        ]); 
        //studiante fuera de la espam paso a ser tutor temporalmente
        EducationalUser::create([
            'sede_id' => 2,
            'user_id' => 3,
            'career_id' => 5,
            'educational_level_id' => 3,
            'level' => 10,
            
        ]);
        // Estudiante  fuera de la espam
        EducationalUser::create([
            'sede_id' => 2,
            'user_id' => 4,
            'career_id' => 2,
            'educational_level_id' => 2,
            'level' => 3,
        ]);

    }
}
