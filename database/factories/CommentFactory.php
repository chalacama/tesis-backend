<?php

namespace Database\Factories;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Comment>
 */
class CommentFactory extends Factory
{
     /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Comment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // Asigna un usuario existente de forma aleatoria.
            // Asegúrate de tener usuarios creados antes de ejecutar el seeder de comentarios.
            'user_id' => User::inRandomOrder()->first()->id ?? User::factory(),
            
            // Usa el generador de texto de Faker para crear un comentario realista.
            'texto' => $this->faker->paragraph(),
            
            // Por defecto, los comentarios son de nivel superior (no son respuestas).
            'parent_id' => null,
            
            // Por defecto, los comentarios están habilitados.
            'enabled' => true,
        ];
    }
}
