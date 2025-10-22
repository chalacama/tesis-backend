<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
{
   
   public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'parent_id'     => $this->parent_id,
            'text'          => $this->texto,
            'created_at'    => optional($this->created_at)->toIso8601String(),

            'likes'         => (int)($this->likes_count ?? 0),
            'liked_by_me'   => (bool)((int)($this->liked_by_me ?? 0)),
            'liked_by_owner'=> (bool)((int)($this->liked_by_owner ?? 0)),
            'all_replies_count' => (int)($this->all_replies_count ?? $this->replies_count ?? 0),
            'depth'         => (int)($this->depth ?? 1), // 1=hijo directo, 2=nieto, etc.

            'user' => [
                'id'        => $this->user->id ?? null,
                'username'  => $this->user->username ?? null,
                'name'      => $this->user->name ?? null,
                'lastname'  => $this->user->lastname ?? null,
                'avatar'    => $this->user->profile_picture_url ?? null,
            ],

            // Para pintar "respondiendo a @username" en la UI (no lo metas dentro de text)
            'reply_to' => $this->whenLoaded('parent', function () {
                $u = optional($this->parent)->user;
                if (! $u) return null;

                return [
                    'id'        => $u->id,
                    'username'  => $u->username,
                    'name'      => $u->name,
                    'lastname'  => $u->lastname,
                    'avatar'    => $u->profile_picture_url,
                ];
            }),
        ];
    }
}
