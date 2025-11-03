<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Answer;
class AnswersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Respuestas para la pregunta 1
        // Asumiendo que las preguntas se crean en orden y tienen IDs secuenciales empezando desde 1
        // Capítulo 1
        // Pregunta 1
// Respuestas para la pregunta 1 (¿Qué es Laravel?)
        Answer::create(['option' => 'Un framework PHP para desarrollo web', 'is_correct' => true, 'order' => 1, 'question_id' => 1]);
        Answer::create(['option' => 'Un lenguaje de programación', 'is_correct' => false, 'order' => 2, 'question_id' => 1]);
        Answer::create(['option' => 'Una base de datos', 'is_correct' => false, 'order' => 3, 'question_id' => 1]);
        Answer::create(['option' => 'Un servidor web', 'is_correct' => false, 'order' => 4, 'question_id' => 1]);

        // Respuestas para la pregunta 2 (¿Quién es el creador de Laravel?)
        Answer::create(['option' => 'Taylor Otwell', 'is_correct' => true, 'order' => 1, 'question_id' => 2]);
        Answer::create(['option' => 'Linus Torvalds', 'is_correct' => false, 'order' => 2, 'question_id' => 2]);
        Answer::create(['option' => 'Mark Zuckerberg', 'is_correct' => false, 'order' => 3, 'question_id' => 2]);
        Answer::create(['option' => 'Guido van Rossum', 'is_correct' => false, 'order' => 4, 'question_id' => 2]);

        // Respuestas para la pregunta 3 (¿Cuáles son características principales de Laravel?)
        Answer::create(['option' => 'Eloquent ORM', 'is_correct' => true, 'order' => 1, 'question_id' => 3]);
        Answer::create(['option' => 'Blade templating', 'is_correct' => true, 'order' => 2, 'question_id' => 3]);
        Answer::create(['option' => 'Artisan CLI', 'is_correct' => true, 'order' => 3, 'question_id' => 3]);
        Answer::create(['option' => 'Compilación a máquina', 'is_correct' => false, 'order' => 4, 'question_id' => 3]);

        // Respuestas para la pregunta 4 (¿En qué año fue lanzado Laravel por primera vez?)
        Answer::create(['option' => '2011', 'is_correct' => true, 'order' => 1, 'question_id' => 4]);
        Answer::create(['option' => '2005', 'is_correct' => false, 'order' => 2, 'question_id' => 4]);
        Answer::create(['option' => '2015', 'is_correct' => false, 'order' => 3, 'question_id' => 4]);
        Answer::create(['option' => '2020', 'is_correct' => false, 'order' => 4, 'question_id' => 4]);

        // Respuestas para la pregunta 5 (¿Cuál es la versión mínima de PHP requerida para Laravel 12?)
        Answer::create(['option' => '8.2', 'is_correct' => true, 'order' => 1, 'question_id' => 5]);
        Answer::create(['option' => '7.4', 'is_correct' => false, 'order' => 2, 'question_id' => 5]);
        Answer::create(['option' => '8.0', 'is_correct' => false, 'order' => 3, 'question_id' => 5]);
        Answer::create(['option' => '8.1', 'is_correct' => false, 'order' => 4, 'question_id' => 5]);

        // Respuestas para la pregunta 6 (¿Cuáles son algunos requisitos mínimos para instalar Laravel 12?)
        Answer::create(['option' => 'PHP 8.2 o superior', 'is_correct' => true, 'order' => 1, 'question_id' => 6]);
        Answer::create(['option' => 'Composer', 'is_correct' => true, 'order' => 2, 'question_id' => 6]);
        Answer::create(['option' => 'Extensiones PHP como PDO y Mbstring', 'is_correct' => true, 'order' => 3, 'question_id' => 6]);
        Answer::create(['option' => 'Node.js', 'is_correct' => false, 'order' => 4, 'question_id' => 6]);

        // Respuestas para la pregunta 7 (¿Qué comando se usa para crear un nuevo proyecto Laravel?)
        Answer::create(['option' => 'composer create-project laravel/laravel example-app', 'is_correct' => true, 'order' => 1, 'question_id' => 7]);
        Answer::create(['option' => 'php artisan new project', 'is_correct' => false, 'order' => 2, 'question_id' => 7]);
        Answer::create(['option' => 'laravel install example-app', 'is_correct' => false, 'order' => 3, 'question_id' => 7]);
        Answer::create(['option' => 'composer install laravel/framework', 'is_correct' => false, 'order' => 4, 'question_id' => 7]);

        // Respuestas para la pregunta 8 (¿Qué es Artisan en Laravel?)
        Answer::create(['option' => 'La interfaz de línea de comandos incluida con Laravel', 'is_correct' => true, 'order' => 1, 'question_id' => 8]);
        Answer::create(['option' => 'Un motor de plantillas', 'is_correct' => false, 'order' => 2, 'question_id' => 8]);
        Answer::create(['option' => 'Un ORM para bases de datos', 'is_correct' => false, 'order' => 3, 'question_id' => 8]);
        Answer::create(['option' => 'Un sistema de enrutamiento', 'is_correct' => false, 'order' => 4, 'question_id' => 8]);

        // Respuestas para la pregunta 9 (¿Cuáles son beneficios de usar Laravel?)
        Answer::create(['option' => 'Sintaxis elegante y expresiva', 'is_correct' => true, 'order' => 1, 'question_id' => 9]);
        Answer::create(['option' => 'Gran comunidad y ecosistema', 'is_correct' => true, 'order' => 2, 'question_id' => 9]);
        Answer::create(['option' => 'Funciones de seguridad integradas', 'is_correct' => true, 'order' => 3, 'question_id' => 9]);
        Answer::create(['option' => 'Bajo rendimiento en aplicaciones grandes', 'is_correct' => false, 'order' => 4, 'question_id' => 9]);

        // Respuestas para la pregunta 10 (¿Laravel es un framework de código abierto?)
        Answer::create(['option' => 'Sí', 'is_correct' => true, 'order' => 1, 'question_id' => 10]);
        Answer::create(['option' => 'No', 'is_correct' => false, 'order' => 2, 'question_id' => 10]);
        Answer::create(['option' => 'Solo partes de él', 'is_correct' => false, 'order' => 3, 'question_id' => 10]);
        Answer::create(['option' => 'Depende de la versión', 'is_correct' => false, 'order' => 4, 'question_id' => 10]);

        // Capítulo 2
        // Pregunta 4
        Answer::create(['option' => 'Route::get(\'/\', function() { return view(\'welcome\'); });', 'is_correct' => true, 'order' => 1, 'question_id' => 11]);
        Answer::create(['option' => 'getRoute(\'/\', \'welcome\');', 'is_correct' => false, 'order' => 2, 'question_id' => 11]);
        Answer::create(['option' => 'Route::post(\'/\', \'Controller\');', 'is_correct' => false, 'order' => 3, 'question_id' => 11]);
        Answer::create(['option' => 'defineRoute(\'get\', \'/\' );', 'is_correct' => false, 'order' => 4, 'question_id' => 11]);

        // Pregunta 5
        Answer::create(['option' => 'Una clase que maneja la lógica de las rutas', 'is_correct' => true, 'order' => 1, 'question_id' => 12]);
        Answer::create(['option' => 'Una vista HTML', 'is_correct' => false, 'order' => 3, 'question_id' => 12]);
        Answer::create(['option' => 'Un modelo de base de datos', 'is_correct' => false, 'order' => 3, 'question_id' => 12]);
        Answer::create(['option' => 'Un middleware de seguridad', 'is_correct' => false, 'order' => 4, 'question_id' => 12]);

        // Pregunta 6
        Answer::create(['option' => 'GET', 'is_correct' => true, 'order' => 1, 'question_id' => 13]);
        Answer::create(['option' => 'POST', 'is_correct' => true, 'order' => 2, 'question_id' => 13]);
        Answer::create(['option' => 'PUT', 'is_correct' => true, 'order' => 3, 'question_id' => 13]);
        Answer::create(['option' => 'FTP', 'is_correct' => false, 'order' => 4, 'question_id' => 13]);

        // Capítulo 4
        // Pregunta 7
        Answer::create(['option' => 'Un ORM para interactuar con bases de datos', 'is_correct' => true, 'order' => 1, 'question_id' => 14]);
        Answer::create(['option' => 'Un motor de plantillas', 'is_correct' => false, 'order' => 2,  'question_id' => 14]);
        Answer::create(['option' => 'Una herramienta de línea de comandos', 'is_correct' => false, 'order' => 3, 'question_id' => 14]);
        Answer::create(['option' => 'Un sistema de rutas', 'is_correct' => false, 'order' => 4, 'question_id' => 14]);

        // Pregunta 8
        Answer::create(['option' => 'php artisan make:model NombreModelo', 'is_correct' => true, 'order' => 1, 'question_id' => 15]);
        Answer::create(['option' => 'php artisan create:model NombreModelo', 'is_correct' => false, 'order' => 2, 'question_id' => 15]);
        Answer::create(['option' => 'model:make NombreModelo', 'is_correct' => false, 'order' => 3, 'question_id' => 15]);
        Answer::create(['option' => 'new Model(NombreModelo)', 'is_correct' => false, 'order' => 4, 'question_id' => 15]);

        // Pregunta 9
        Answer::create(['option' => 'hasOne', 'is_correct' => true, 'order' => 1, 'question_id' => 16]);
        Answer::create(['option' => 'hasMany', 'is_correct' => true, 'order' => 2, 'question_id' => 16]);
        Answer::create(['option' => 'belongsTo', 'is_correct' => true, 'order' => 3, 'question_id' => 16]);
        Answer::create(['option' => 'getAll', 'is_correct' => false, 'order' => 4, 'question_id' => 16]);
        
    }
}
