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
            'url' => 'https://i.ytimg.com/vi/f7unUpshmpA/maxresdefault.jpg',

        ]);
        MiniatureCourse::create([
            'course_id' => 2,
            'url' => 'https://i.ytimg.com/vi/AE5U8zjkU2s/maxresdefault.jpg',
            
        ]);
    }
}
