<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\CareerCourse;
class CareerCourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        CareerCourse::create(['course_id' => 1, 'career_id' => 5]);
        CareerCourse::create(['course_id' => 1, 'career_id' => 1]);

        CareerCourse::create(['course_id' => 2, 'career_id' => 1]);
        CareerCourse::create(['course_id' => 2, 'career_id' => 2]);

        CareerCourse::create(['course_id' => 3, 'career_id' => 1]);
        CareerCourse::create(['course_id' => 3, 'career_id' => 2]);

        CareerCourse::create(['course_id' => 4, 'career_id' => 5]);
        CareerCourse::create(['course_id' => 4, 'career_id' => 1]);

        CareerCourse::create(['course_id' => 5, 'career_id' => 2]);
        CareerCourse::create(['course_id' => 5, 'career_id' => 1]);

        CareerCourse::create(['course_id' => 6, 'career_id' => 5]);
        CareerCourse::create(['course_id' => 6, 'career_id' => 3]);
    }
}
