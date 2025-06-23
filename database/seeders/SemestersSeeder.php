<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Semester;
class SemestersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $semesters = [
            'Primer Semestre',
            'Segundo Semestre',
            'Tercer Semestre',
            'Cuarto Semestre',
            'Quinto Semestre',
            'Sexto Semestre',
            'Séptimo Semestre',
            'Octavo Semestre',
            'Noveno Semestre',
            'Décimo Semestre',
        ];

        foreach ($semesters as $semester) {
            Semester::create(['name' => $semester]);
        }
    }
}
