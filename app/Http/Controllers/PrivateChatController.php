<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\User;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PrivateChatController extends Controller
{
    /**
     * Display the private chat interface with user selection.
     */
    public function index()
    {
        // Get all users except the current user
        $users = User::where('id', '!=', Auth::id())
            ->select('id', 'name', 'email', 'profile_photo_path')
            ->get()
            ->map(function ($user) {
                // Add the profile_photo_url attribute
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'profile_photo_url' => $user->profile_photo_url ?? '/img/default-avatar.png'
                ];
            });

        // Get existing private conversations for the current user
        try {
            // Get conversations directly from the conversation_user pivot table
            $userConversations = DB::table('conversation_user')
                ->where('user_id', Auth::id())
                ->pluck('conversation_id');

            $conversations = [];

            if ($userConversations->count() > 0) {
                $conversationModels = Conversation::whereIn('id', $userConversations)
                    ->where('is_group', false)
                    ->with(['users', 'latestMessage'])
                    ->get();

                foreach ($conversationModels as $conversation) {
                    // Find the other user in the conversation
                    $otherUser = $conversation->users->where('id', '!=', Auth::id())->first();

                    if (!$otherUser) {
                        continue;
                    }

                    // Get unread messages count
                    $lastRead = DB::table('conversation_user')
                        ->where('conversation_id', $conversation->id)
                        ->where('user_id', Auth::id())
                        ->value('last_read_at');

                    $unreadCount = 0;
                    if ($lastRead) {
                        $unreadCount = Message::where('conversation_id', $conversation->id)
                            ->where('user_id', '!=', Auth::id())
                            ->where('created_at', '>', $lastRead)
                            ->count();
                    } else {
                        $unreadCount = Message::where('conversation_id', $conversation->id)
                            ->where('user_id', '!=', Auth::id())
                            ->count();
                    }

                    $conversations[] = [
                        'id' => $conversation->id,
                        'user' => [
                            'id' => $otherUser->id,
                            'name' => $otherUser->name,
                            'email' => $otherUser->email,
                            'profile_photo_url' => $otherUser->profile_photo_url ?? '/img/default-avatar.png',
                        ],
                        'latest_message' => $conversation->latestMessage ? [
                            'body' => $conversation->latestMessage->body,
                            'created_at' => $conversation->latestMessage->created_at,
                        ] : null,
                        'unread_count' => $unreadCount,
                    ];
                }
            }
        } catch (\Exception $e) {
            // Log the error
            Log::error('Error fetching conversations: ' . $e->getMessage());

            // Return empty array if there's an error
            $conversations = [];
        }

        return Inertia::render('PrivateChat/Index', [
            'users' => $users,
            'conversations' => $conversations,
        ]);
    }

    /**
     * Start or continue a private chat with a specific user.
     */
    public function chat($userId)
    {
        // Find the user
        $user = User::findOrFail($userId);

        // Check if a conversation already exists between these users
        $currentUserId = Auth::id();

        // Find conversations where both users are participants
        $conversationIds = DB::table('conversation_user')
            ->where('user_id', $currentUserId)
            ->pluck('conversation_id');

        $sharedConversationIds = DB::table('conversation_user')
            ->whereIn('conversation_id', $conversationIds)
            ->where('user_id', $userId)
            ->pluck('conversation_id');

        $conversation = Conversation::whereIn('id', $sharedConversationIds)
            ->where('is_group', false)
            ->first();

        // If no conversation exists, create one
        if (!$conversation) {
            $conversation = Conversation::create([
                'is_group' => false,
            ]);

            // Add both users to the conversation
            $conversation->users()->attach([$currentUserId, $userId]);
        }

        // Mark conversation as read
        DB::table('conversation_user')
            ->where('conversation_id', $conversation->id)
            ->where('user_id', $currentUserId)
            ->update(['last_read_at' => now()]);

        // Get messages
        $messages = Message::where('conversation_id', $conversation->id)
            ->with('user')
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($message) use ($currentUserId) {
                $messageUser = $message->user;
                return [
                    'id' => $message->id,
                    'body' => $message->body,
                    'type' => $message->type,
                    'attachment_url' => $message->attachment_url,
                    'created_at' => $message->created_at,
                    'is_mine' => $message->user_id === $currentUserId,
                    'user' => [
                        'id' => $messageUser->id,
                        'name' => $messageUser->name,
                        'profile_photo_url' => $messageUser->profile_photo_url ?? '/img/default-avatar.png',
                    ],
                ];
            });

        return Inertia::render('PrivateChat/Chat', [
            'conversation' => [
                'id' => $conversation->id,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'profile_photo_url' => $user->profile_photo_url ?? '/img/default-avatar.png',
                ],
            ],
            'messages' => $messages,
        ]);
    }

    /**
     * Send a message in a private chat.
     */
    public function sendMessage(Request $request, $conversationId)
    {
        // Validate the request
        $validated = $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        // Check if the conversation exists
        $conversation = Conversation::findOrFail($conversationId);
        $currentUserId = Auth::id();

        // Check if the user is a participant
        $isParticipant = DB::table('conversation_user')
            ->where('conversation_id', $conversationId)
            ->where('user_id', $currentUserId)
            ->exists();

        if (!$isParticipant) {
            return response()->json(['error' => 'You are not a participant in this conversation'], 403);
        }

        // Create the message
        $message = Message::create([
            'conversation_id' => $conversationId,
            'user_id' => $currentUserId,
            'body' => $validated['message'],
            'type' => 'text',
        ]);

        // Load the user relationship
        $message->load('user');

        // Update the conversation's last_read_at for the sender
        DB::table('conversation_user')
            ->where('conversation_id', $conversationId)
            ->where('user_id', $currentUserId)
            ->update(['last_read_at' => now()]);

        // Get the recipient user ID (the other user in the conversation)
        $recipientId = DB::table('conversation_user')
            ->where('conversation_id', $conversationId)
            ->where('user_id', '!=', $currentUserId)
            ->value('user_id');

        // Send Firebase notification
        if ($recipientId) {
            $this->sendFirebaseNotification($message, $conversationId, $currentUserId, $recipientId);
        }

        // Return the message
        return response()->json([
            'id' => $message->id,
            'body' => $message->body,
            'type' => $message->type,
            'created_at' => $message->created_at,
            'user' => [
                'id' => $message->user->id,
                'name' => $message->user->name,
                'profile_photo_url' => $message->user->profile_photo_url ?? '/img/default-avatar.png',
            ],
        ]);
    }

    /**
     * Get messages for a conversation.
     */
    public function getMessages($conversationId)
    {
        $currentUserId = Auth::id();

        // Check if the user is a participant
        $isParticipant = DB::table('conversation_user')
            ->where('conversation_id', $conversationId)
            ->where('user_id', $currentUserId)
            ->exists();

        if (!$isParticipant) {
            return response()->json(['error' => 'You are not a participant in this conversation'], 403);
        }

        // Get messages
        $messages = Message::where('conversation_id', $conversationId)
            ->with('user')
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($message) use ($currentUserId) {
                $messageUser = $message->user;
                return [
                    'id' => $message->id,
                    'body' => $message->body,
                    'type' => $message->type,
                    'attachment_url' => $message->attachment_url,
                    'created_at' => $message->created_at,
                    'is_mine' => $message->user_id === $currentUserId,
                    'user' => [
                        'id' => $messageUser->id,
                        'name' => $messageUser->name,
                        'profile_photo_url' => $messageUser->profile_photo_url ?? '/img/default-avatar.png',
                    ],
                ];
            });

        // Mark conversation as read
        DB::table('conversation_user')
            ->where('conversation_id', $conversationId)
            ->where('user_id', $currentUserId)
            ->update(['last_read_at' => now()]);

        return response()->json($messages);
    }

    /**
     * Send a Firebase notification for a new message.
     */
    private function sendFirebaseNotification($message, $conversationId, $senderId, $recipientId)
    {
        try {
            // Get Firebase configuration
            $databaseUrl = config('services.firebase.database_url');
            $apiKey = config('services.firebase.api_key');

            if (!$databaseUrl || !$apiKey) {
                Log::warning('Firebase configuration missing. Cannot send notification.');
                return;
            }

            // Get sender information
            $sender = User::find($senderId);

            // Create the notification data
            $notificationData = [
                'message_id' => $message->id,
                'sender_id' => $senderId,
                'sender_name' => $sender->name,
                'conversation_id' => $conversationId,
                'message_preview' => substr($message->body, 0, 50),
                'timestamp' => time() * 1000, // Current time in milliseconds
                'is_read' => false
            ];

            // Path in Firebase where the notification will be stored
            $path = "private_chat_notifications/{$recipientId}/{$conversationId}";

            // Create a Firebase database reference URL
            $url = "{$databaseUrl}/{$path}.json?auth={$apiKey}";

            // Initialize cURL
            $ch = curl_init();

            // Set cURL options
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($notificationData));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

            // Execute the request
            $response = curl_exec($ch);

            // Check for errors
            if (curl_errno($ch)) {
                Log::error('Firebase notification error: ' . curl_error($ch));
            }

            // Close cURL
            curl_close($ch);

            Log::info("Firebase notification sent to user {$recipientId} for conversation {$conversationId}");
        } catch (\Exception $e) {
            Log::error('Error sending Firebase notification: ' . $e->getMessage());
        }
    }
}
