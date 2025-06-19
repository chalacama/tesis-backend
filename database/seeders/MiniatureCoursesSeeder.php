<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\MiniatureCourse;
class MiniatureCoursesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        MiniatureCourse::create([
            'course_id' => 1,
            'url' => 'https://example.com/miniatura1.jpg',
        ]);
        MiniatureCourse::create([
            'course_id' => 1,
            'url' => 'https://example.com/miniatura2.jpg',
        ]);
    }
}
