<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SavedCourse;
class SavedCoursesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SavedCourse::create([
            'user_id' => 1,
            'course_id' => 1,
        ]);
        SavedCourse::create([
            'user_id' => 2,
            'course_id' => 1,
        ]);
    }
}
