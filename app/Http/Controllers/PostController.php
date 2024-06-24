<?php

namespace App\Http\Controllers;

use App\Http\Enums\ReactionEnum;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdateCommentRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Http\Resources\CommentResource;
use App\Http\Resources\PostResource;
use App\Models\Comments;
use App\Models\PostAttachments;
use App\Models\Posts;
use App\Models\Reactions;
use App\Models\User;
use App\Notifications\CommentCreated;
use App\Notifications\CommentDeleted;
use App\Notifications\PostCreated;
use App\Notifications\PostDeleted;
use App\Notifications\ReactionAddedOnComment;
use App\Notifications\ReactionAddedOnPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use OpenAI\Laravel\Facades\OpenAI;

class PostController extends Controller
{

    public function view(Posts $post)
    {
        $post->loadCount('reactions');
        $post->load([
            'comments' => fn ($query) => $query->withCount('reactions'),
        ]);
        return inertia('Post/View', [
            'post' => new PostResource($post),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePostRequest $request)
    {
        // Validasi data yang diterima dari permintaan HTTP menggunakan aturan yang didefinisikan di dalam StorePostRequest
        $data = $request->validated();
        // Mendapatkan objek pengguna yang saat ini terautentikasi
        $user = $request->user();
        // Memulai transaksi database
        DB::beginTransaction();
        $allFilePath = [];
        try {
            // Membuat entri baru dalam tabel Posts menggunakan data yang diterima dari permintaan HTTP
            $post = Posts::create($data);
            // Mendapatkan daftar lampiran yang diunggah dari data yang diterima atau menginisialisasi array kosong jika tidak ada lampiran yang diunggah
            $files = $data['attachments'] ?? [];
            foreach ($files as $file) {
                // Menyimpan file terlampir ke penyimpanan yang ditentukan ('public') dan mendapatkan jalur penyimpanannya
                $path = $file->store('attachments/' . $post->id, 'public');
                $allFilePath[] = $path;

                // Membuat entri baru dalam tabel PostAttachments untuk setiap lampiran yang diunggah
                PostAttachments::create([
                    'post_id' => $post->id,
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'mime' => $file->getClientMimeType(),
                    'size' => $file->getSize(),
                    'created_by' => $user->id,
                ]);
            }
            // Menyelesaikan transaksi database
            DB::commit();

            // Kirim notifikasi ke pengguna yang terautentikasi
            $group = $post->group;

            if ($group) {
                $users = $group->approvedUsers()->where('users.id', '!=', $user->id)->get();
                Notification::send($users, new PostCreated($post, $user, $group));
            }

            $followers = $user->followers;

            Notification::send($followers, new PostCreated($post, $user, null));
        } catch (\Exception $e) {
            // Jika terjadi pengecualian selama proses transaksi, hapus semua file yang telah diunggah
            foreach ($allFilePath as $path) {
                Storage::disk('public')->delete($path);
            }
            // Rollback transaksi database
            DB::rollBack();
            // Lepaskan pengecualian agar dapat ditangani lebih lanjut
            throw $e;
        }

        // Kembalikan pengguna ke halaman sebelumnya setelah berhasil menyimpan postingan
        return back();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePostRequest $request, Posts $post)
    {
        $user = $request->user();

        DB::beginTransaction();
        $allFilePath = [];
        try {
            $data = $request->validated();
            $post->update($data);

            $deleted_ids = $data['deleted_file_ids'] ?? [];

            $attachments = PostAttachments::query()
                ->where('post_id', $post->id)
                ->whereIn('id', $deleted_ids)
                ->get();

            foreach ($attachments as $attachment) {
                $attachment->delete();
            }

            /** @var \Illuminate\Http\UploadedFile[] $files */
            $files = $data['attachments'] ?? [];
            foreach ($files as $file) {
                $path = $file->store('attachments/' . $post->id, 'public');
                $allFilePath[] = $path;
                PostAttachments::create([
                    'post_id' => $post->id,
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'mime' => $file->getClientMimeType(),
                    'size' => $file->getSize(),
                    'created_by' => $user->id,
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            foreach ($allFilePath as $path) {
                Storage::disk('public')->delete($path);
            }
            DB::rollBack();
            throw $e;
        }

        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Posts $posts)
    {
        $id = Auth::id();
        if ($posts->isOwner($id) || $posts->group && $posts->group->isAdmin($id)) {
            $posts->delete();

            if (!$posts->isOwner($id)) {
                $posts->user->notify(new PostDeleted($posts->group));
            }

            return back();
        }

        return response('You are not allowed to delete this post', 403);
    }

    public function downloadAttachment(PostAttachments $attachment)
    {
        return response()
            ->download(
                storage_path('app/public/' . $attachment->path),
                $attachment->name
            );
    }

    public function postReaction(Request $request, Posts $post)
    {
        $data =  $request->validate([
            'reaction' => [Rule::enum(ReactionEnum::class)],
        ]);

        $userId = Auth::id();
        $reaction = Reactions::where('user_id', $userId)
            ->where('object_id', $post->id)
            ->where('object_type', Posts::class)
            ->first();

        if ($reaction) {
            $hasReaction = false;
            $reaction->delete();
        } else {
            $hasReaction = true;
            Reactions::create([
                'object_id' => $post->id,
                'object_type' => Posts::class,
                'user_id' => $userId,
                'type' => $data['reaction'],
            ]);

            if (!$post->isOwner($userId)) {
                $user = User::where('id', $userId)->first();
                $post->user->notify(new ReactionAddedOnPost($post, $user));
            }
        }

        $reactions = Reactions::where('object_id', $post->id)->where('object_type', Posts::class)->count();

        return response([
            'num_of_reactions' => $reactions,
            'curent_user_has_reactions' => $hasReaction,
        ]);
    }

    public function createComment(Request $request, Posts $post)
    {
        $data = $request->validate([
            'comment' => 'required',
            'parent_id' => 'nullable|exists:comments,id'
        ]);

        $comment = Comments::create([
            'post_id' => $post->id,
            'comment' => nl2br($data['comment']),
            'user_id' => Auth::id(),
            'parent_id' => $data['parent_id'] ?: null,
        ]);

        $post = $comment->post;
        $post->user->notify(new CommentCreated($comment, $post));

        return response(new CommentResource($comment), 201);
    }

    public function deleteComment(Comments $comment)
    {
        $posts = $comment->post;
        $id = Auth::id();
        if ($comment->isOwner($id) ||  $posts->isOwner($id)) {
            $comment->delete();

            if (!$comment->isOwner($id)) {
                $comment->user->notify(new CommentDeleted($comment, $posts));
            }

            return response('', 204);
        }

        return response('You are not allowed to delete this comment', 403);
    }

    public function updateComment(UpdateCommentRequest $request, Comments $comment)
    {
        $data = $request->validated();

        $comment->update([
            'comment' => nl2br($data['comment']),
        ]);

        return response(new CommentResource($comment), 200);
    }

    public function commentReaction(Request $request, Comments $comment)
    {
        $data =  $request->validate([
            'reaction' => [Rule::enum(ReactionEnum::class)],
        ]);

        $userId = Auth::id();
        $reaction = Reactions::where('user_id', $userId)
            ->where('object_id', $comment->id)
            ->where('object_type', Comments::class)
            ->first();

        if ($reaction) {
            $hasReaction = false;
            $reaction->delete();
        } else {
            $hasReaction = true;
            Reactions::create([
                'object_id' => $comment->id,
                'object_type' => Comments::class,
                'user_id' => $userId,
                'type' => $data['reaction'],
            ]);

            if (!$comment->isOwner($userId)) {
                $user = User::where('id', $userId)->first();
                $comment->user->notify(new ReactionAddedOnComment($comment->post, $comment, $user));
            }
        }

        $reactions = Reactions::where('object_id', $comment->id)->where('object_type', Comments::class)->count();

        return response([
            'num_of_reactions' => $reactions,
            'curent_user_has_reactions' => $hasReaction,
        ]);
    }

    public function generatePostContent(Request $request)
    {
        $prompt = $request->get('prompt');

        $result = OpenAI::chat()->create([
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                [
                    'role' => 'user',
                    'content' => "Please generate a post content sosial media based on the following propmpt. Generate formated content with multiple paragraphs. Put hastags after 2 lines from the main content:"
                        . PHP_EOL . PHP_EOL . $prompt,
                ],
            ],
        ]);

        return response(['content' => $result->choices[0]->message->content]);
    }

    public function fetchUrlPreview(Request $request)
    {
        $data = $request->validate([
            'url' => 'url'
        ]);

        $url = $data['url'];

        $html = file_get_contents($url);

        $dom = new \DOMDocument();

        libxml_use_internal_errors(true);

        $dom->loadHTML($html);

        libxml_use_internal_errors(false);

        $ogTags = [];

        $metaTags = $dom->getElementsByTagName('meta');

        foreach ($metaTags as $tag) {
            $property = $tag->getAttribute('property');
            if (str_starts_with($property, 'og:')) {
                $ogTags[$property] = $tag->getAttribute('content');
            }
        }

        return $ogTags;
    }

    public function pinUnpin(Request $request, Posts $post)
    {
        $forGroup = $request->get('forGroup', false);
        $group = $post->group;

        if ($forGroup && !$group) {
            return response('Invalid request', 400);
        }
        if ($forGroup && !$group->isAdmin(Auth::id())) {
            return response('You are not allowed to do this action', 403);
        }

        $pinned = false;
        if ($forGroup && $group->isAdmin(Auth::id())) {
            if ($group->pinned_post_id == $post->id) {
                $group->pinned_post_id = null;
            } else {
                $pinned = true;
                $group->pinned_post_id = $post->id;
            }
            $group->save();
        }

        if (!$forGroup) {
            $user = $request->user();
            if ($user->pinned_post_id == $post->id) {
                $user->pinned_post_id = null;
            } else {
                $pinned = true;
                $user->pinned_post_id = $post->id;
            }
            $user->save();
        }

        return back()->with('success', 'Post was ' . ($pinned ? 'pinned' : 'unpinned'));
    }
}
