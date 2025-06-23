<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AcademicProgram;
class AcademicProgramsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $programs = [
            'Campus Calceta',
            'Sede Galápagos',
        ];

        foreach ($programs as $program) {
            AcademicProgram::create(['name' => $program]);
        }
    }
}
