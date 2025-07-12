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
        CareerCourse::create(['course_id' => 1, 'career_id' => 2]);
        CareerCourse::create(['course_id' => 1, 'career_id' => 3]);
        CareerCourse::create(['course_id' => 2, 'career_id' => 1]);
    }
}
