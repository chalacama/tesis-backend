<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\LikeComment;
use App\Models\Comment;
use App\Models\User;
class LikeCommentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener todos los comentarios
        $comments = Comment::all();

        // Obtener todos los usuarios
        $users = User::all();

        // Crear likes para cada comentario y usuario
        foreach ($comments as $comment) {
            foreach ($users as $user) {
                // Verificar si el usuario ya ha dado like al comentario
                if (!LikeComment::where('comment_id', $comment->id)->where('user_id', $user->id)->exists()) {
                    // Crear un nuevo like
                    LikeComment::create([
                        'comment_id' => $comment->id,
                        'user_id' => $user->id,
                    ]);
                }
            }
        }
    }
}
