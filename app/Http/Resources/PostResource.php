<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $comments = $this->comments;
        return [
            'id' => $this->id,
            'body' => $this->body,
            'preview' => $this->preview,
            'preview_url' => $this->preview_url,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            'user' => new UserResource($this->user),
            'group' => new GroupResource($this->group),
            'attachments' => PostAttachmentResource::collection($this->attachments),
            'num_of_reactions' => count($this->reactions),
            'num_of_comments' => count($comments),
            'curent_user_has_reactions' => $this->reactions
                ->where('object_id', $this->id)
                ->where('user_id', auth()->id())
                ->isNotEmpty(),
            'comments' => self::convertCommentsIntoTree($comments),
        ];
    }

    /**
     *
     *  @param \App\Models\Comment[] $comments
     *  @param int $parentId
     *  @return array
     *
     */

    private function convertCommentsIntoTree($comments, $parentId = null): array
    {
        $commentTree = [];

        foreach ($comments as $comment) {
            if ($comment->parent_id === $parentId) {
                //Find all comment whitch has parentId as $comment->id
                $children = self::convertCommentsIntoTree($comments, $comment->id);

                $comment->childComments = $children;
                $comment->numOfComments = collect($children)
                    ->sum('numOfComments') + count($children);

                $commentTree[] = new CommentResource($comment);
            }
        }

        return $commentTree;
    }
}
