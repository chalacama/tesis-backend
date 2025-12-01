<?php

namespace App\Notifications;

use App\Models\Course;
use App\Models\Chapter;
use App\Models\LearningContent;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewContentInCourseNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Course $course,
        public Chapter $chapter,
        public LearningContent $content,
        public ?User $actor = null, // quién agrega el contenido
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        // Nos aseguramos de tener el tipo cargado
        $this->content->loadMissing('typeLearningContent');

        $typeName = optional($this->content->typeLearningContent)->name;
        $typeName = $typeName ? strtolower($typeName) : null;

        $actorName = null;
        if ($this->actor) {
            $actorName = trim(($this->actor->name ?? '') . ' ' . ($this->actor->lastname ?? ''));
            if ($actorName === '') {
                $actorName = $this->actor->username ?? 'un tutor';
            }
        }

        $msg = 'Se ha agregado nuevo contenido';
        if ($typeName) {
            $msg .= " ({$typeName})";
        }
        $msg .= ' en el capítulo "' . ($this->chapter->title ?? 'sin título') . '"';
        $msg .= ' del curso "' . ($this->course->title ?? 'sin título') . '"';
        if ($actorName) {
            $msg .= " por {$actorName}.";
        } else {
            $msg .= '.';
        }

        return [
            'key'             => 'course.new_content',
            'title'           => 'Nuevo contenido disponible',
            'message'         => $msg,
            'course_id'       => $this->course->id,
            'course_title'    => $this->course->title,
            'chapter_id'      => $this->chapter->id,
            'chapter_title'   => $this->chapter->title,
            'type_content'    => $typeName,
            // Ajusta esta URL a tu ruta en Angular
            'url'             => "learning/course/{$this->course->title}/{$this->course->id}/{$this->chapter->title}/{$this->chapter->id}",
            'content_id'      => $this->content->id,
        ];
    }
}
