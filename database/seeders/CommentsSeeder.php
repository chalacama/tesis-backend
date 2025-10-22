<?php

namespace Database\Seeders;


use Illuminate\Database\Seeder;
use App\Models\Comment;
use App\Models\User; // Importa el modelo User
use App\Models\Course; // Importa el modelo Course
use App\Models\LikeComment;
class CommentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userA = User::find(1);
        $userB = User::find(2);
        $courseA = Course::find(1);
        // Comentarios de curso A
        // Comentario 1
        $parentA1 = Comment::create([
            'user_id'         => $userA->id,
            'texto'           => 'Muy bueno el curso',
            'parent_id'       => null,                   
            'commentable_type'=> Course::class,
            'commentable_id'  => 1,          
        ]);
        // Comentario 2
        $parentA2 = Comment::create([
            'user_id'         => $userA->id,
            'texto'           => 'Espero que sea el ultimo curso',
            'parent_id'       => null,                   
            'commentable_type'=> Course::class,
            'commentable_id'  => 1,          
        ]);
        // Respuesta 1 de comentario 1
        $reply_valid = Comment::create([
            'user_id'         => $userB->id, 
            'texto'           => 'Gracias por el comentario',
            'parent_id'       => $parentA1->id,
            'commentable_type'=> Course::class,
            'commentable_id'  => $courseA->id,          
        ]);

        // === Respuesta 2 que Responde a una respusta de comentario 1
        $reply_validB = Comment::create([
            'user_id'         => $userA->id,
            'texto'           => 'De nada bro usted es el mejor profesor de la plataforma',
            'parent_id'       => $reply_valid->id,
            'commentable_type'=> Course::class,
            'commentable_id'  => $courseA->id,          
        ]);
        // === Respuesta 3 que Responde a una respusta 2 de comentario 1
        $reply_validC = Comment::create([
            'user_id'         => $userB->id,
            'texto'           => 'no tu eres mejor profesor de la plataforma',
            'parent_id'       => $reply_validB->id,
            'commentable_type'=> Course::class,
            'commentable_id'  => $courseA->id,          
        ]);
        
        

        $reply_validD = Comment::create([
            'user_id'         => $userB->id, 
            'texto'           => 'Calla bot de la plataforma',
            'parent_id'       => $parentA2->id,
            'commentable_type'=> Course::class,
            'commentable_id'  => $courseA->id,          
        ]);
        $reply_validE = Comment::create([
            'user_id'         => $userA->id, 
            'texto'           => 'Ni un brillo pelao',
            'parent_id'       => $reply_validD->id,
            'commentable_type'=> Course::class,
            'commentable_id'  => $courseA->id,          
        ]);
        
    }
}
