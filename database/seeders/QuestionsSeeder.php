<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Question;
class QuestionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        
         // Capítulo 1: Introducción a Laravel
        Question::create([
            'statement' => '¿Qué es Laravel?',
            'spot' => 1,
            'type_questions_id' => 1, // Opción múltiple
            'chapter_id' => 1
        ]);

        Question::create([
            'statement' => '¿Quién es el creador de Laravel?',
            'spot' => 2,
            'type_questions_id' => 1, // Opción múltiple
            'chapter_id' => 1
        ]);

        Question::create([
            'statement' => '¿Cuáles son características principales de Laravel?',
            'spot' => 3,
            'type_questions_id' => 2, // Casilla de verificación
            'chapter_id' => 1
        ]);

        // Capítulo 2: Rutas y Controladores
        Question::create([
            'statement' => '¿Cómo se define una ruta GET básica en Laravel?',
            'spot' => 1,
            'type_questions_id' => 1, // Opción múltiple
            'chapter_id' => 2
        ]);

        Question::create([
            'statement' => '¿Qué es un controlador en Laravel?',
            'spot' => 2,
            'type_questions_id' => 1, // Opción múltiple
            'chapter_id' => 2
        ]);

        Question::create([
            'statement' => '¿Cuáles son métodos HTTP soportados en rutas de Laravel?',
            'spot' => 3,
            'type_questions_id' => 2, // Casilla de verificación
            'chapter_id' => 2
        ]);

        // Capítulo 4: Eloquent ORM
        Question::create([
            'statement' => '¿Qué es Eloquent en Laravel?',
            'spot' => 1,
            'type_questions_id' => 1, // Opción múltiple
            'chapter_id' => 4
        ]);

        Question::create([
            'statement' => '¿Cómo se crea un modelo en Laravel?',
            'spot' => 2,
            'type_questions_id' => 1, // Opción múltiple
            'chapter_id' => 4
        ]);

        Question::create([
            'statement' => '¿Cuáles son tipos de relaciones en Eloquent?',
            'spot' => 3,
            'type_questions_id' => 2, // Casilla de verificación
            'chapter_id' => 4
        ]);
        
    }
}
