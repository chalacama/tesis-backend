<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\RatingCourse;
class RatingCourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        RatingCourse::create([
            'stars' => 5,
            'course_id' => 1, // Asegúrate de que el curso con ID 1 exista
            'user_id' => 1,   // Asegúrate de que el usuario con ID 1 exista
        ]);
        RatingCourse::create([
            'stars' => 4,
            'course_id' => 2,
            'user_id' => 2,
        ]);
        RatingCourse::create([
            'stars' => 2,
            'course_id' => 3,
            'user_id' => 2,
        ]);
        RatingCourse::create([
            'stars' => 3,
            'course_id' => 1,
            'user_id' => 3,
        ]);
        RatingCourse::create([
            'stars' => 1,
            'course_id' => 3,
            'user_id' => 4,
        ]);
        RatingCourse::create([
            'stars' => 5,
            'course_id' => 1,
            'user_id' => 4,
        ]);
        RatingCourse::create([
            'stars' => 4,
            'course_id' => 2,
            'user_id' => 3,
        ]);
    }
}
