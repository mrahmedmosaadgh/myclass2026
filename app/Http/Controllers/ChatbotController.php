<?php

namespace App\Http\Controllers;

use App\Services\ChatbotService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ChatbotController extends Controller
{
    protected $chatbotService;

    public function __construct(ChatbotService $chatbotService)
    {
        $this->chatbotService = $chatbotService;
    }

    /**
     * Start a new conversation or get existing active one.
     */
    public function start(Request $request)
    {
        $rules = [
            'type' => 'required|in:bug,idea,question',
            'message' => 'nullable|string',
            'images' => 'nullable|array|max:3',
            'images.*.data' => 'required|string',
            'images.*.name' => 'required|string|max:255',
            'images.*.size' => 'required|integer|max:5242880', // 5MB max per image
            'virtual_id' => 'nullable|string',
            'url' => 'nullable|string',
        ];

        // Only require email validation for non-authenticated users
        if (! auth()->check()) {
            $rules['email'] = 'nullable|email';
        }

        $validated = $request->validate($rules);

        // Debug log to see if images are being received
        \Log::info('Chatbot start payload:', $validated);

        $conversation = $this->chatbotService->startConversation($validated);

        return response()->json([
            'status' => 'success',
            'conversation' => $conversation->load('messages'),
        ]);
    }

    /**
     * Send a message to an existing conversation.
     */
    public function send(Request $request)
    {
        $rules = [
            'conversation_id' => 'required|exists:chatbot_conversations,id',
            'message' => 'required_without:images|string',
            'images' => 'nullable|array|max:3',
            'images.*.data' => 'required|string',
            'images.*.name' => 'required|string|max:255',
            'images.*.size' => 'required|integer|max:5242880', // 5MB max per image
            'virtual_id' => 'nullable|string',
        ];

        $validated = $request->validate($rules);

        $conversation = $this->chatbotService->getConversation($validated['conversation_id'], $validated['virtual_id'] ?? null);

        if (! $conversation) {
            return response()->json(['error' => 'Conversation not found'], 404);
        }

        $message = $this->chatbotService->addMessage(
            $conversation,
            $validated['message'] ?? '',
            'user',
            auth()->id(),
            $validated['images'] ?? []
        );

        return response()->json([
            'status' => 'success',
            'message' => $message,
        ]);
    }

    /**
     * Get conversation history.
     */
    public function history(Request $request)
    {
        $virtualId = $request->query('virtual_id');

        $conversation = $this->chatbotService->getLatestConversation($virtualId);

        if (! $conversation) {
            return response()->json([
                'status' => 'success',
                'conversation' => null,
            ]);
        }

        return response()->json([
            'status' => 'success',
            'conversation' => $conversation,
        ]);
    }
}
