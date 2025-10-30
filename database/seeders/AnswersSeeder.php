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
        Answer::create(['option' => 'Un framework PHP para desarrollo web', 'is_correct' => true, 'order' => 1, 'question_id' => 1]);
        Answer::create(['option' => 'Un lenguaje de programación', 'is_correct' => false, 'order' => 2,  'question_id' => 1]);
        Answer::create(['option' => 'Una base de datos', 'is_correct' => false, 'order' => 3, 'question_id' => 1]);
        Answer::create(['option' => 'Un servidor web', 'is_correct' => false, 'order' => 4, 'question_id' => 1]);

        // Pregunta 2
        Answer::create(['option' => 'Taylor Otwell', 'is_correct' => true, 'order' => 1, 'question_id' => 2]);
        Answer::create(['option' => 'Linus Torvalds', 'is_correct' => false, 'order' => 2 , 'question_id' => 2]);
        Answer::create(['option' => 'Mark Zuckerberg', 'is_correct' => false, 'order' => 3, 'question_id' => 2]);
        Answer::create(['option' => 'Guido van Rossum', 'is_correct' => false, 'order' => 4, 'question_id' => 2]);

        // Pregunta 3
        Answer::create(['option' => 'Eloquent ORM', 'is_correct' => true, 'order' => 1, 'question_id' => 3]);
        Answer::create(['option' => 'Blade templating', 'is_correct' => true, 'order' => 2, 'question_id' => 3]);
        Answer::create(['option' => 'Artisan CLI', 'is_correct' => true, 'order' => 3, 'question_id' => 3]);
        Answer::create(['option' => 'Compilación a máquina', 'is_correct' => false, 'order' => 4, 'question_id' => 3]);

        // Capítulo 2
        // Pregunta 4
        Answer::create(['option' => 'Route::get(\'/\', function() { return view(\'welcome\'); });', 'is_correct' => true, 'order' => 1, 'question_id' => 4]);
        Answer::create(['option' => 'getRoute(\'/\', \'welcome\');', 'is_correct' => false, 'order' => 2, 'question_id' => 4]);
        Answer::create(['option' => 'Route::post(\'/\', \'Controller\');', 'is_correct' => false, 'order' => 3, 'question_id' => 4]);
        Answer::create(['option' => 'defineRoute(\'get\', \'/\' );', 'is_correct' => false, 'order' => 4, 'question_id' => 4]);

        // Pregunta 5
        Answer::create(['option' => 'Una clase que maneja la lógica de las rutas', 'is_correct' => true, 'order' => 1, 'question_id' => 5]);
        Answer::create(['option' => 'Una vista HTML', 'is_correct' => false, 'order' => 3, 'question_id' => 5]);
        Answer::create(['option' => 'Un modelo de base de datos', 'is_correct' => false, 'order' => 3, 'question_id' => 5]);
        Answer::create(['option' => 'Un middleware de seguridad', 'is_correct' => false, 'order' => 4, 'question_id' => 5]);

        // Pregunta 6
        Answer::create(['option' => 'GET', 'is_correct' => true, 'order' => 1, 'question_id' => 6]);
        Answer::create(['option' => 'POST', 'is_correct' => true, 'order' => 2, 'question_id' => 6]);
        Answer::create(['option' => 'PUT', 'is_correct' => true, 'order' => 3, 'question_id' => 6]);
        Answer::create(['option' => 'FTP', 'is_correct' => false, 'order' => 4, 'question_id' => 6]);

        // Capítulo 4
        // Pregunta 7
        Answer::create(['option' => 'Un ORM para interactuar con bases de datos', 'is_correct' => true, 'order' => 1, 'question_id' => 7]);
        Answer::create(['option' => 'Un motor de plantillas', 'is_correct' => false, 'order' => 2,  'question_id' => 7]);
        Answer::create(['option' => 'Una herramienta de línea de comandos', 'is_correct' => false, 'order' => 3, 'question_id' => 7]);
        Answer::create(['option' => 'Un sistema de rutas', 'is_correct' => false, 'order' => 4, 'question_id' => 7]);

        // Pregunta 8
        Answer::create(['option' => 'php artisan make:model NombreModelo', 'is_correct' => true, 'order' => 1, 'question_id' => 8]);
        Answer::create(['option' => 'php artisan create:model NombreModelo', 'is_correct' => false, 'order' => 2, 'question_id' => 8]);
        Answer::create(['option' => 'model:make NombreModelo', 'is_correct' => false, 'order' => 3, 'question_id' => 8]);
        Answer::create(['option' => 'new Model(NombreModelo)', 'is_correct' => false, 'order' => 4, 'question_id' => 8]);

        // Pregunta 9
        Answer::create(['option' => 'hasOne', 'is_correct' => true, 'order' => 1, 'question_id' => 9]);
        Answer::create(['option' => 'hasMany', 'is_correct' => true, 'order' => 2, 'question_id' => 9]);
        Answer::create(['option' => 'belongsTo', 'is_correct' => true, 'order' => 3, 'question_id' => 9]);
        Answer::create(['option' => 'getAll', 'is_correct' => false, 'order' => 4, 'question_id' => 9]);
        
    }
}
