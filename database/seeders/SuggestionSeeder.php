<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Suggestion;

class SuggestionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        
        // Usuario de prueba
        $userId = 1; 

        $data = [
            // 1. Tipo: Título
            ['texto' => 'Laravel Básico', 'search_type' => 'title', 'entity_id' => 1, 'searched' => 5],
            
            // 2. Tipo: Carrera
            ['texto' => 'Computación', 'search_type' => 'career', 'entity_id' => 5, 'searched' => 2],
            
            // 3. Tipo: Categoría
            ['texto' => 'Programaciónb', 'search_type' => 'category', 'entity_id' => 1, 'searched' => 8],
            
            // 4. Tipo: Tutor
            ['texto' => 'Juan Jandry', 'search_type' => 'tutor', 'entity_id' => 2, 'searched' => 1],
 
            // 5. Tipo: Dificultad
            ['texto' => 'Beginner', 'search_type' => 'difficulty', 'entity_id' => 1, 'searched' => 1],

            
        ];

        foreach ($data as $item) {
            Suggestion::create(array_merge($item, ['user_id' => $userId]));
        }

    }
}
