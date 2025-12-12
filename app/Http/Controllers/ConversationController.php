<?php

namespace App\Http\Controllers;

use App\Events\MessageRead;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ConversationController extends Controller
{
    /**
     * Display a listing of the conversations.
     */
    public function index()
    {
        $user = Auth::user();
        
        $conversations = $user->conversations()
            ->with(['users' => function ($query) use ($user) {
                $query->where('users.id', '!=', $user->id);
            }, 'latestMessage'])
            ->get()
            ->map(function ($conversation) use ($user) {
                $otherUser = $conversation->isPrivate() ? $conversation->getOtherUser($user) : null;
                
                return [
                    'id' => $conversation->id,
                    'name' => $conversation->is_group ? $conversation->name : ($otherUser ? $otherUser->name : 'Unknown'),
                    'is_group' => $conversation->is_group,
                    'users' => $conversation->users->map(function ($u) {
                        return [
                            'id' => $u->id,
                            'name' => $u->name,
                            'profile_photo_url' => $u->profile_photo_url,
                        ];
                    }),
                    'latest_message' => $conversation->latestMessage ? [
                        'id' => $conversation->latestMessage->id,
                        'body' => $conversation->latestMessage->body,
                        'created_at' => $conversation->latestMessage->created_at,
                        'is_mine' => $conversation->latestMessage->user_id === $user->id,
                    ] : null,
                    'unread_count' => $conversation->unreadMessagesCount($user),
                ];
            });
        
        return Inertia::render('Chat/Index', [
            'conversations' => $conversations,
        ]);
    }

    /**
     * Show the form for creating a new conversation.
     */
    public function create()
    {
        $users = User::where('id', '!=', Auth::id())->get(['id', 'name', 'profile_photo_url']);
        
        return Inertia::render('Chat/Create', [
            'users' => $users,
        ]);
    }

    /**
     * Store a newly created conversation in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'name' => 'nullable|string|max:255',
        ]);
        
        $userIds = $validated['user_ids'];
        $isGroup = count($userIds) > 1;
        $name = $isGroup ? ($validated['name'] ?? 'Group Chat') : null;
        
        // Check if a private conversation already exists
        if (!$isGroup) {
            $existingConversation = Auth::user()->conversations()
                ->whereHas('users', function ($query) use ($userIds) {
                    $query->where('users.id', $userIds[0]);
                })
                ->whereDoesntHave('users', function ($query) use ($userIds) {
                    $query->whereNotIn('users.id', array_merge($userIds, [Auth::id()]));
                })
                ->where('is_group', false)
                ->first();
            
            if ($existingConversation) {
                return redirect()->route('conversations.show', $existingConversation);
            }
        }
        
        // Create a new conversation
        $conversation = Conversation::create([
            'name' => $name,
            'is_group' => $isGroup,
        ]);
        
        // Add participants
        $conversation->users()->attach(array_merge($userIds, [Auth::id()]));
        
        return redirect()->route('conversations.show', $conversation);
    }

    /**
     * Display the specified conversation.
     */
    public function show(Conversation $conversation)
    {
        // Check if the user is a participant
        if (!$conversation->users()->where('users.id', Auth::id())->exists()) {
            abort(403, 'You are not a participant in this conversation.');
        }
        
        $user = Auth::user();
        
        // Mark conversation as read
        $this->markAsRead($conversation);
        
        // Get conversation details
        $conversationData = [
            'id' => $conversation->id,
            'name' => $conversation->is_group ? $conversation->name : $conversation->getOtherUser($user)->name,
            'is_group' => $conversation->is_group,
            'users' => $conversation->users->map(function ($u) {
                return [
                    'id' => $u->id,
                    'name' => $u->name,
                    'profile_photo_url' => $u->profile_photo_url,
                ];
            }),
        ];
        
        // Get messages
        $messages = $conversation->messages()
            ->with('user')
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($message) use ($user) {
                return [
                    'id' => $message->id,
                    'body' => $message->body,
                    'type' => $message->type,
                    'attachment_url' => $message->attachment_url,
                    'created_at' => $message->created_at,
                    'is_mine' => $message->user_id === $user->id,
                    'user' => [
                        'id' => $message->user->id,
                        'name' => $message->user->name,
                        'profile_photo_url' => $message->user->profile_photo_url,
                    ],
                ];
            });
        
        return Inertia::render('Chat/Show', [
            'conversation' => $conversationData,
            'messages' => $messages,
        ]);
    }

    /**
     * Mark the conversation as read for the authenticated user.
     */
    private function markAsRead(Conversation $conversation)
    {
        $user = Auth::user();
        $now = now();
        
        $user->conversations()->updateExistingPivot($conversation->id, [
            'last_read_at' => $now,
        ]);
        
        // Broadcast that the user has read the messages
        broadcast(new MessageRead($conversation, $user, $now->toIso8601String()))->toOthers();
    }
}
