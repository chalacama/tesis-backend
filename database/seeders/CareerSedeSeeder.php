<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\CareerSede;
class CareerSedeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Sede 2: carreras 18 y 4
        CareerSede::create(['sede_id' => 2, 'career_id' => 18/* ,'max_semesters' => 10 */]);
        CareerSede::create(['sede_id' => 2, 'career_id' => 4/* ,'max_semesters' => 10 */]);

        // Sede 1: todas menos la 4
        $careerIds = [
            1, 2, 3, /*4,*/ 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18
        ];

        foreach ($careerIds as $careerId) {
            if ($careerId != 4) {
                CareerSede::create([
                    'sede_id' => 1,
                    'career_id' => $careerId,
                    // 'max_semesters' => 10
                ]);
            }
        }
    }
}
