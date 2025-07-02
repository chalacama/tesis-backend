<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AcademicCareer;
use App\Models\AcademicProgram;
use App\Models\Career;
class AcademicCareerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ejemplo de datos para el Campus Calceta
        $academicProgram = AcademicProgram::find(1);
        $careers = Career::whereIn('id', [1, 2, 3, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16,17,18])->get();
        foreach ($careers as $career) {
            AcademicCareer::create([
                'academic_program_id' => $academicProgram->id,
                'career_id' => $career->id,
            ]);
        }

        // Ejemplo de datos para la Sede Galápagos
        $academicProgram = AcademicProgram::find(2);
        $careers = Career::whereIn('id', [4, 18])->get();
        foreach ($careers as $career) {
            AcademicCareer::create([
                'academic_program_id' => $academicProgram->id,
                'career_id' => $career->id,
            ]);
        }
    }
}
