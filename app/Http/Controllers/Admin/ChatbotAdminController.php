<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatbotConversation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ChatbotAdminController extends Controller
{
    /**
     * Display the inbox.
     */
    public function index(Request $request)
    {
        $status = $request->query('status', 'new');

        $conversations = ChatbotConversation::with(['user', 'messages' => function ($query) {
            $query->orderBy('created_at', 'desc')->take(1); // Get latest message for preview
        }])
            ->when($status !== 'all', function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->orderBy('updated_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/Chatbot/Inbox', [
            'conversations' => $conversations,
            'statusFilter' => $status,
        ]);
    }

    /**
     * Show a conversation.
     */
    public function show($id)
    {
        $conversation = ChatbotConversation::with(['user', 'messages' => function ($query) {
            $query->orderBy('created_at', 'asc');
        }])->findOrFail($id);

        return Inertia::render('Admin/Chatbot/ConversationView', [
            'conversation' => $conversation,
        ]);
    }

    /**
     * Admin reply.
     */
    public function reply(Request $request, $id)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        $conversation = ChatbotConversation::findOrFail($id);

        $conversation->messages()->create([
            'sender_type' => 'admin',
            'sender_id' => Auth::id(),
            'message' => $request->message,
            'images' => null, // Admin messages don't include images
        ]);

        $conversation->update(['status' => 'replied']);

        return back()->with('success', 'Reply sent successfully.');
    }

    /**
     * Update conversation status.
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:new,replied,closed',
        ]);

        $conversation = ChatbotConversation::findOrFail($id);
        $conversation->update(['status' => $request->status]);

        return back()->with('success', 'Status updated successfully.');
    }
}
