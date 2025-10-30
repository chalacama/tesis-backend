<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Comment;
use App\Models\LikeComment;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
class LikeCommentController extends Controller
{
     use AuthorizesRequests;
    public function update(Request $request, Comment $comment)
    {
        // Debe pertenecer a un commentable visible y el usuario debe poder ver el curso
        $course = $comment->commentable;
        
        // Si comentaste también en capítulos, ajusta este check si quieres limitar a Course
        $this->authorize('viewRegistered', $course);

        // Evita likes sobre comentarios eliminados lógicamente
        if ($comment->trashed()) {
            return response()->json([
                'ok' => false,
                'message' => 'Este comentario ya no está disponible.'
            ], 410); // Gone
        }

        $user = $request->user();
        if (!$user) {
            return response()->json([
                'ok' => false,
                'message' => 'No autenticado.'
            ], 401);
        }

        // Valida 'liked' si viene; si no viene, haremos toggle
        $data = $request->validate([
            'liked' => ['sometimes','boolean'],
        ]);

        // Estado actual
        $exists = LikeComment::where('comment_id', $comment->id)
            ->where('user_id', $user->id)
            ->exists();

        // Determina la acción
        $target = $data['liked'] ?? !$exists; // si no envían liked, alterna

        if ($target) {
            // LIKE (idempotente)
            LikeComment::firstOrCreate([
                'comment_id' => $comment->id,
                'user_id'    => $user->id,
            ]);
        } else {
            // UNLIKE (idempotente)
            LikeComment::where('comment_id', $comment->id)
                ->where('user_id', $user->id)
                ->delete();
        }

        return response()->json([
            'ok'   => true,
            'liked' => $target
        ]);
    }
}
