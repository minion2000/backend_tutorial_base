<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'article_id' => $this->article_id,
            'user' => [
                'id' => $this->user->id,
                'display_name' => $this->user->name,
                'avatar_url' => $this->user->avatar_url ?? null,
            ],
            'content' => $this->content,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
            'is_owner' => $request->user()?->id === $this->user_id,
        ];
    }
}
