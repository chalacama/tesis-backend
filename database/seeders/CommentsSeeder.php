<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Comment;
class CommentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Comment::create([
            'texto' => '¡Excelente curso!',
            'enabled' => true,
            'user_id' => 1, // Asegúrate de que el usuario y el curso existan
            'curso_id' => 1,
        ]);
        Comment::create([
            'texto' => 'Muy útil y bien explicado.',
            'enabled' => true,
            'user_id' => 2,
            'curso_id' => 1,
        ]);
    }
}
