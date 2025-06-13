<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;
class CategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Category::create([
            'name' => 'Programación', // id : 1 
            'description' => 'Cursos relacionados con programación.',
            'enabled' => true,
        ]);
        Category::create([
            'name' => 'Diseño', // id : 2 
            'description' => 'Cursos de diseño gráfico y digital.',
            'enabled' => true,
        ]);
        Category::create([
            'name' => 'Diagramas', // id : 3 
            'description' => 'Cursos de base de datos.',
            'enabled' => true,
        ]);
    }
}
