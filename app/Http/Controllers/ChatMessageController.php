<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Events\UserTyping;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ChatMessageController extends Controller
{
    /**
     * Store a newly created message in storage.
     */
    public function store(Request $request, Conversation $conversation)
    {
        // Check if the user is a participant
        if (!$conversation->users()->where('users.id', Auth::id())->exists()) {
            return response()->json(['message' => 'You are not a participant in this conversation.'], 403);
        }
        
        $validated = $request->validate([
            'body' => 'required_without:attachment|string|nullable',
            'attachment' => 'nullable|file|max:10240', // 10MB max
            'type' => 'nullable|string|in:text,image,file',
        ]);
        
        $type = $validated['type'] ?? 'text';
        $attachmentUrl = null;
        
        // Handle file upload
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $path = $file->store('chat_attachments', 'public');
            $attachmentUrl = Storage::url($path);
            
            // Determine type based on mime type if not provided
            if (!isset($validated['type'])) {
                $mime = $file->getMimeType();
                if (strpos($mime, 'image/') === 0) {
                    $type = 'image';
                } else {
                    $type = 'file';
                }
            }
        }
        
        // Create the message
        $message = $conversation->messages()->create([
            'user_id' => Auth::id(),
            'body' => $validated['body'] ?? '',
            'type' => $type,
            'attachment_url' => $attachmentUrl,
        ]);
        
        // Load the user relationship
        $message->load('user');
        
        // Broadcast the message
        broadcast(new MessageSent($message, Auth::user()))->toOthers();
        
        // Update the conversation's last_read_at for the sender
        Auth::user()->conversations()->updateExistingPivot($conversation->id, [
            'last_read_at' => now(),
        ]);
        
        return response()->json([
            'id' => $message->id,
            'body' => $message->body,
            'type' => $message->type,
            'attachment_url' => $message->attachment_url,
            'created_at' => $message->created_at,
            'user' => [
                'id' => $message->user->id,
                'name' => $message->user->name,
                'profile_photo_url' => $message->user->profile_photo_url,
            ],
        ]);
    }

    /**
     * Broadcast that the user is typing.
     */
    public function typing(Request $request, Conversation $conversation)
    {
        // Check if the user is a participant
        if (!$conversation->users()->where('users.id', Auth::id())->exists()) {
            return response()->json(['message' => 'You are not a participant in this conversation.'], 403);
        }
        
        // Broadcast typing event
        broadcast(new UserTyping($conversation, Auth::user()))->toOthers();
        
        return response()->json(['success' => true]);
    }

    /**
     * Mark messages as seen.
     */
    public function markAsSeen(Request $request, Conversation $conversation)
    {
        // Check if the user is a participant
        if (!$conversation->users()->where('users.id', Auth::id())->exists()) {
            return response()->json(['message' => 'You are not a participant in this conversation.'], 403);
        }
        
        $validated = $request->validate([
            'message_ids' => 'required|array',
            'message_ids.*' => 'exists:messages,id',
        ]);
        
        // Mark messages as seen
        Message::whereIn('id', $validated['message_ids'])
            ->where('user_id', '!=', Auth::id())
            ->update(['is_seen' => true]);
        
        return response()->json(['success' => true]);
    }
}
