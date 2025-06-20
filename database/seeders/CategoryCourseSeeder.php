<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\CategoryCourse;
class CategoryCourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        CategoryCourse::create([
            'order' => 1,
            'course_id' => 1, // Asegúrate de que el curso y la categoría existan
            'category_id' => 1,
        ]);
        CategoryCourse::create([
            'order' => 1,
            'course_id' => 2,
            'category_id' => 2,
        ]);
        CategoryCourse::create([
            'order' => 2,
            'course_id' => 2,
            'category_id' => 3,
        ]);
    }
}
