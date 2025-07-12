<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TutorCourse;

class TutorCourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TutorCourse::create([
            'course_id' => 1, // Asegúrate de que el curso con ID 1 exista
            'user_id' => 2,   // Asegúrate de que el usuario con ID 1 exista
        ]);
        
        TutorCourse::create([
            'course_id' => 2,
            'user_id' => 3,
        ]);
        TutorCourse::create([
            'course_id' => 3,
            'user_id' => 2,
        ]);
        TutorCourse::create([
            'course_id' => 4,
            'user_id' => 3,
        ]);
    }
}
