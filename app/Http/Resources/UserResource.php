<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $img = $this->avatar_path
        ? Storage::url($this->avatar_path)
        : asset('img/default_avatar.jpeg');

        return [
            "id" => $this->id,
            "name" => $this->name,
            "email" => $this->email,
            "email_verified_at" => $this->email_verified_at,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at,
            "username" => $this->username,
            'pinned_post_id' => $this->pinned_post_id,
            "cover_url" => $this->cover_path ? Storage::url($this->cover_path) : null,
            "avatar_url" => $img,
            "stories" => StoriesResource::collection($this->stories),
        ];
    }
}
