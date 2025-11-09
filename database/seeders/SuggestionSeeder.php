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
        
        Suggestion::create(['texto' => 'Curso Gratis', 'searched' => 1 , 'user_id' => 2]);
        Suggestion::create(['texto' => 'programacion', 'searched' => 1 ,   'user_id' => 2]);
        Suggestion::create(['texto' => 'Curso de diseño', 'searched' => 1 , 'user_id' => 2]);
        Suggestion::create(['texto' => 'curso para hacer trampa en el examen', 'searched' => 1 , 'user_id' => 2]);
        Suggestion::create(['texto' => 'Computacion', 'searched' => 1 , 'user_id' => 2]);
    }
}
