<?php

namespace App\Notifications;

use App\Models\Course;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CommentRepliedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Course $course,
        public Comment $parentComment,  // comentario que están respondiendo
        public Comment $replyComment,   // la respuesta
        public ?User $actor = null      // quien responde
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $actorName = null;
        if ($this->actor) {
            $actorName = trim(($this->actor->name ?? '') . ' ' . ($this->actor->lastname ?? ''));
            if ($actorName === '') {
                $actorName = $this->actor->username ?? 'un usuario';
            }
        }

        $texto = (string) ($this->replyComment->texto ?? '');
        $snippet = mb_substr($texto, 0, 120);
        if (mb_strlen($texto) > 120) {
            $snippet .= '...';
        }

        $message = $actorName
            ? "{$actorName} respondió a tu comentario en el curso \"{$this->course->title}\"."
            : "Han respondido a tu comentario en el curso \"{$this->course->title}\".";

        return [
            'key'                => 'comment.replied',
            'title'              => 'Nueva respuesta a tu comentario',
            'message'            => $message,
            'reply_snippet'      => $snippet,
            'course_id'          => $this->course->id,
            'course_title'       => $this->course->title,
            'parent_comment_id'  => $this->parentComment->id,
            'reply_comment_id'   => $this->replyComment->id,
            // Ajusta a tu ruta real en Angular
            'url'                => "/courses/{$this->course->id}#comment-{$this->replyComment->id}",
        ];
    }
}
