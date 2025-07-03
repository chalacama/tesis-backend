<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseResource extends JsonResource
{ 
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Lógica para determinar si el usuario está registrado
        $isRegistered = $this->whenLoaded('registrations', function () {
            return $this->registrations->where('annulment', false)->isNotEmpty();
        }, false);

        // Lógica para obtener la última vista
        $lastViewId = null;
        if ($isRegistered) {
            $lastViewId = $this->modules->flatMap(fn ($module) => 
                $module->chapters->flatMap(fn ($chapter) => $chapter->learningContent->contentViews ?? [])
            )->sortByDesc('updated_at')->first()->id ?? null;
        } else {
            $lastViewId = $this->modules->first()->chapters->first()->learningContent->id ?? null;
        }

        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'enabled' => $this->enabled,
            'registered' => $isRegistered,
            'last_view_id' => $lastViewId,
            'categories' => $this->whenLoaded('categories'),
            // No es necesario devolver registrations y tutors en el JSON final si no se usan en el frontend
            // 'registrations' => $this->whenLoaded('registrations'), 
            'tutors' => $this->whenLoaded('tutors'),
            'modules' => $this->whenLoaded('modules', function () {
                // Aquí puedes usar otros resources para los módulos si quieres más control
                return $this->modules->map(function ($module) {
                    $module->chapters->each(function ($chapter) {
                        if ($chapter->learningContent) {
                            // Oculta la URL aquí, en la capa de presentación
                            $chapter->learningContent->makeHidden('url'); 
                        }
                    });
                    return $module;
                });
            }),
        ];
    }

}
