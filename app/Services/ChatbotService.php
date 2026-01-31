<?php

namespace App\Services;

use App\Models\ChatbotConversation;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ChatbotService
{
    /**
     * Start a new conversation.
     */
    public function startConversation(array $data)
    {
        $userId = auth()->id();
        $userEmail = null;

        // Only get email from authenticated user if they have one
        if (auth()->check() && isset(auth()->user()->email)) {
            $userEmail = auth()->user()->email;
        } elseif (isset($data['email'])) {
            $userEmail = $data['email'];
        }

        $virtualId = $data['virtual_id'] ?? null;
        $url = $data['url'] ?? null;

        // If no user ID, ensure virtual ID is set or create one
        if (! $userId && ! $virtualId) {
            $virtualId = (string) Str::uuid();
        }

        $conversation = ChatbotConversation::create([
            'user_id' => $userId,
            'virtual_id' => $virtualId,
            'email' => $userEmail,
            'type' => $data['type'] ?? 'question',
            'status' => 'new',
            'mode' => 'manual',
            'url' => $url,
        ]);

        // Add the initial message
        if (! empty($data['message']) || ! empty($data['images'])) {
            Log::info('Adding initial message with data:', [
                'message' => $data['message'],
                'images_count' => ! empty($data['images']) ? count($data['images']) : 0,
                'images' => $data['images'] ?? [],
            ]);
            $this->addMessage($conversation, $data['message'] ?? '', 'user', $userId, $data['images'] ?? []);
        }

        return $conversation;
    }

    /**
     * Add a message to a conversation.
     */
    public function addMessage(ChatbotConversation $conversation, string $messageText, string $senderType = 'user', $senderId = null, array $images = [])
    {
        Log::info('Creating message with:', [
            'message_text' => $messageText,
            'sender_type' => $senderType,
            'images_count' => count($images),
            'images_data' => $images,
        ]);

        $message = $conversation->messages()->create([
            'sender_type' => $senderType,
            'sender_id' => $senderId,
            'message' => $messageText,
            'images' => ! empty($images) ? $images : null,
        ]);

        if ($senderType === 'user') {
            $conversation->update(['status' => 'new']);
        } elseif ($senderType === 'admin') {
            $conversation->update(['status' => 'replied']);
        }

        return $message;
    }

    /**
     * Get conversation securely for user/guest.
     */
    public function getConversation($id, ?string $virtualId = null)
    {
        $query = ChatbotConversation::with(['messages' => function ($q) {
            $q->orderBy('created_at', 'asc');
        }]);

        if (auth()->check()) {
            $query->where('user_id', auth()->id());
        } elseif ($virtualId) {
            $query->where('virtual_id', $virtualId);
        } else {
            return null;
        }

        return $query->find($id);
    }

    /**
     * Get active conversation for user (latest not closed).
     */
    public function getLatestConversation(?string $virtualId = null)
    {
        $query = ChatbotConversation::with(['messages' => function ($q) {
            $q->orderBy('created_at', 'asc');
        }])->where('status', '!=', 'closed');

        if (auth()->check()) {
            $query->where('user_id', auth()->id());
        } elseif ($virtualId) {
            $query->where('virtual_id', $virtualId);
        } else {
            return null;
        }

        return $query->latest()->first();
    }
}
