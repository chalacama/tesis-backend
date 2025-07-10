<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TutorCourseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // El método whenLoaded() asegura que las relaciones solo se incluyan
        // si se cargaron previamente, evitando consultas N+1.
        return [
            'id' => $this->id,
            'enabled' => $this->enabled,
            'tutor' => new UserResource($this->whenLoaded('user')),
            'course' => new CourseResource($this->whenLoaded('course')),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
