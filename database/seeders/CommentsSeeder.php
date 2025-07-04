<?php

namespace Database\Seeders;


use Illuminate\Database\Seeder;
use App\Models\Comment;
use App\Models\User; // Importa el modelo User
use App\Models\Course; // Importa el modelo Course

class CommentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // --- Prerequisitos ---
        // Asegurémonos de que existan usuarios y cursos para asociar los comentarios.
        // Si tus seeders de User y Course no se ejecutan antes, puedes crearlos aquí.
        if (User::count() == 0) {
            User::factory(10)->create();
        }
        if (Course::count() == 0) {
            Course::factory(5)->create();
        }

        // Obtenemos todos los cursos para agregarles comentarios.
        $courses = Course::all();

        // --- Creación de Comentarios ---
        foreach ($courses as $course) {
            // Para cada curso, creamos entre 3 y 5 comentarios principales.
            $numberOfComments = rand(3, 5);

            for ($i = 0; $i < $numberOfComments; $i++) {
                // Creamos un comentario principal y lo asociamos al curso actual.
                // El método `for()` es la forma elegante de manejar relaciones polimórficas en factories.
                $parentComment = Comment::factory()
                    ->for($course, 'commentable') // Asocia este comentario con el curso.
                    ->create();

                // Aleatoriamente, decidimos si este comentario tendrá respuestas.
                if (rand(0, 1)) {
                    // Creamos entre 1 y 3 respuestas para el comentario principal.
                    $numberOfReplies = rand(1, 3);
                    for ($j = 0; $j < $numberOfReplies; $j++) {
                        
                        Comment::factory()
                            ->for($course, 'commentable') // La respuesta también pertenece al mismo curso.
                            ->create([
                                'parent_id' => $parentComment->id, // Indicamos que es una respuesta al comentario padre.
                            ]);
                    }
                }
            }
        }
    }
}
