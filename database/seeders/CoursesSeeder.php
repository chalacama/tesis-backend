<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Course;
class CoursesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // id : 1
        Course::create([
            'title' => 'Laravel Básico',
            'description' => 'Curso introductorio a Laravel.',
            'private' => true,       
            'enabled' => false, 
            
        ]);
        // id : 2
        Course::create([
            'title' => 'Angular Básico',
            'description' => 'Curso introductorio a Angular.',
            'private' => false,
            'enabled' => true,
            
            
        ]);
        // id : 3
        Course::create([
            'title' => 'PHP Avanzado',
            'description' => 'Curso avanzado de PHP.',
            'private' => false,
            'enabled' => true,
            
            
        ]);
        // id : 4
        Course::create([
            'title' => 'Java Avanzado',
            'description' => 'Curso avanzado de Java.',
            'private' => false,
            'enabled' => false,
            
           
        ]);
    }
}
