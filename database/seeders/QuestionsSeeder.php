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
            'order' => 1,
            'type_questions_id' => 1, // Opción múltiple
            'test_id' => 1
        ]);

        Question::create([
            'statement' => '¿Quién es el creador de Laravel?',
            'spot' => 2,
            'order' => 2,
            'type_questions_id' => 1, // Opción múltiple
            'test_id' => 1
        ]);

        Question::create([
            'statement' => '¿Cuáles son características principales de Laravel?',
            'spot' => 3,
            'order' => 3,
            'type_questions_id' => 2, // Casilla de verificación
            'test_id' => 1
        ]);

        Question::create([
            'statement' => '¿En qué año fue lanzado Laravel por primera vez?',
            'spot' => 4,
            'order' => 4,
            'type_questions_id' => 1, // Opción múltiple
            'test_id' => 1
        ]);

        Question::create([
            'statement' => '¿Cuál es la versión mínima de PHP requerida para Laravel 12?',
            'spot' => 5,
            'order' => 5,
            'type_questions_id' => 1, // Opción múltiple
            'test_id' => 1
        ]);

        Question::create([
            'statement' => '¿Cuáles son algunos requisitos mínimos para instalar Laravel 12?',
            'spot' => 6,
            'order' => 6,
            'type_questions_id' => 2, // Casilla de verificación
            'test_id' => 1
        ]);

        Question::create([
            'statement' => '¿Qué comando se usa para crear un nuevo proyecto Laravel?',
            'spot' => 7,
            'order' => 7,
            'type_questions_id' => 1, // Opción múltiple
            'test_id' => 1
        ]);

        Question::create([
            'statement' => '¿Qué es Artisan en Laravel?',
            'spot' => 8,
            'order' => 8,
            'type_questions_id' => 1, // Opción múltiple
            'test_id' => 1
        ]);

        Question::create([
            'statement' => '¿Cuáles son beneficios de usar Laravel?',
            'spot' => 9,
            'order' => 9,
            'type_questions_id' => 2, // Casilla de verificación
            'test_id' => 1
        ]);

        Question::create([
            'statement' => '¿Laravel es un framework de código abierto?',
            'spot' => 10,
            'order' => 10,
            'type_questions_id' => 1, // Opción múltiple
            'test_id' => 1
        ]);

        // Capítulo 2: Rutas y Controladores
        Question::create([
            'statement' => '¿Cómo se define una ruta GET básica en Laravel?',
            'spot' => 1,
            'order' => 1,
            'type_questions_id' => 1, // Opción múltiple
            'test_id' => 2
        ]);

        Question::create([
            'statement' => '¿Qué es un controlador en Laravel?',
            'spot' => 2,
            'order' => 2,
            'type_questions_id' => 1, // Opción múltiple
            'test_id' => 2
        ]);

        Question::create([
            'statement' => '¿Cuáles son métodos HTTP soportados en rutas de Laravel?',
            'spot' => 3,
            'order' => 3,
            'type_questions_id' => 2, // Casilla de verificación
            'test_id' => 2
        ]);

        // Capítulo 4: Eloquent ORM
        Question::create([
            'statement' => '¿Qué es Eloquent en Laravel?',
            'spot' => 1,
            'order' => 1,
            'type_questions_id' => 1, // Opción múltiple
            'test_id' => 3
        ]);

        Question::create([
            'statement' => '¿Cómo se crea un modelo en Laravel?',
            'spot' => 2,
            'order' => 2,
            'type_questions_id' => 1, // Opción múltiple
            'test_id' => 3
        ]);

        Question::create([
            'statement' => '¿Cuáles son tipos de relaciones en Eloquent?',
            'spot' => 3,
            'order' => 3,
            'type_questions_id' => 2, // Casilla de verificación
            'test_id' => 3
        ]);
        
    }
}
