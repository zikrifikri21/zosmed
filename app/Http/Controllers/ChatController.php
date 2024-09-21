<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ChatController extends Controller
{

    public function index()
    {
        $friends = User::query()->where('id', '!=', auth()->id())->get();

        return Inertia::render('Chat/View', [
            'friends' => $friends,
        ]);
    }

    public function chatroom(User $friend)
    {
        return Inertia::render('Chat/ChatRoom', [
            'friend' => $friend,
        ]);
    }

    public function message(User $friend)
    {
        return ChatMessage::query()
            ->where(function ($query) use ($friend) {
                $query->where('sender_id', auth()->id())
                    ->where('receiver_id', $friend->id);
            })
            ->orWhere(function ($query) use ($friend) {
                $query->where('sender_id', $friend->id)
                    ->where('receiver_id', auth()->id());
            })
            ->with(['sender', 'receiver'])
            ->orderBy('id', 'asc')
            ->get();
    }

    public function sendMessage(User $friend)
    {
        $message = ChatMessage::create([
            'sender_id' => auth()->id(),
            'receiver_id' => $friend->id,
            'text' => request()->input('message'),
        ]);

        broadcast(new MessageSent($message));

        return $message;
    }

    public function sendFiles(User $friend)
    {
        request()->validate([
            'files' => 'required|array',
            'files.*' => 'required|file|mimes:gif,jpeg,jpg,png,mp4,mp3,pdf',
        ]);

        $files = request()->file('files');
        $filenames = [];

        foreach ($files as $file) {
            $extension = $file->getClientOriginalExtension();
            $filename = Str::random(10) . '_' . auth()->id() . '.' . $extension;
            $path = "cht_files/" . auth()->id() . "_to_{$friend->id}/";
            $file->storeAs('public/' . $path, $filename);
            $filenames[] = $filename;
        }

        $message = ChatMessage::create([
            'sender_id' => auth()->id(),
            'receiver_id' => $friend->id,
            'file' => $path . $filenames[0],
        ]);

        broadcast(new MessageSent($message));

        return $message;
    }

    public function downloadFile(ChatMessage $file)
    {
        try {
            // Pastikan file ada di storage
            if (!Storage::exists('public/' . $file->file)) {
                return abort(404, 'File not found');
            }

            $filename = str_replace('/', '_', $file->file);
            $filename = str_replace('\\', '_', $filename);
            $filename = Str::random(10) . '_' . auth()->id() . '.' . $filename;
            $filename = str_replace('_', Str::random(3), $filename);

            return response()->download(
                storage_path('app/public/' . $file->file),
                $filename
            );
        } catch (\Exception $exception) {
            return redirect()->back()->with('error', 'Error downloading file');
        }
    }

    public function sendFile(Request $request, User $friend)
    {
        try {
            $file = $request->file('file');

            $fileClient = $file->getClientOriginalExtension();
            $filename = $file->getClientOriginalName() . '.' . $fileClient;
            $file->storeAs('public', $filename);

            $sendfile = ChatMessage::create([
                'sender_id' => auth()->id(),
                'receiver_id' => $friend->id,
                'file' => $filename,
            ]);

            broadcast(new MessageSent($sendfile));

            return $sendfile;
        } catch (\Exception $exception) {
            return $exception;
        }
    }
}
