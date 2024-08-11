<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class StoriesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $file = $this->file ? Storage::url($this->file) : asset('img/default_avatar.jpeg');

        return [
            "id" => $this->id,
            "description" => $this->description,
            "files" => $file,
            "created_at" => $this->created_at->diffForHumans(),
        ];
    }
}
