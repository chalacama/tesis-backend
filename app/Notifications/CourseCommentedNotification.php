<?php

namespace App\Notifications;

use App\Models\Course;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CourseCommentedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Course $course,
        public Comment $comment,
        public ?User $actor = null // quien comentó
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

        $texto = (string) ($this->comment->texto ?? '');
        $snippet = mb_substr($texto, 0, 120);
        if (mb_strlen($texto) > 120) {
            $snippet .= '...';
        }

        $message = $actorName
            ? "{$actorName} comentó en tu curso \"{$this->course->title}\"."
            : "Han comentado en tu curso \"{$this->course->title}\".";

        return [
            'key'           => 'course.commented',
            'title'         => 'Nuevo comentario en tu curso',
            'message'       => $message,
            'comment_snippet' => $snippet,
            'course_id'     => $this->course->id,
            'course_title'  => $this->course->title,
            'comment_id'    => $this->comment->id,
            // Ajusta a tu ruta real en Angular
            'url'           => "/courses/{$this->course->id}#comment-{$this->comment->id}",
        ];
    }
}
