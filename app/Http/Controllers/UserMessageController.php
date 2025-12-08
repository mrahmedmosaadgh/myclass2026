<?php

namespace App\Http\Controllers;

use App\Models\UserMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class UserMessageController extends Controller
{
    public function store(Request $request)
    {
        try {
            // Log the incoming request data
            Log::info('Message store attempt:', [
                'user_id' => Auth::user()->id,
                'request_data' => $request->all()
            ]);

            $validated = $request->validate([
                'content' => 'required|string|max:1000',
                'recipients' => 'required|array|min:1',
                'recipients.*' => 'exists:users,id'
            ]);

            // Double check sender_id is available
            $sender_id = Auth::id();
            if (!$sender_id) {
                Log::error('Auth::id() returned null when trying to send message');
                return response()->json([
                    'message' => 'Authentication error',
                    'error' => 'Could not determine sender ID'
                ], 500);
            }

            DB::beginTransaction();
            try {
                $message = UserMessage::create([
                    'sender_id' => $sender_id, // Explicitly set sender_id
                    'content' => $validated['content']
                ]);

                $message->recipients()->attach($validated['recipients']);

                DB::commit();

                // Load relationships and return
                $message->load(['sender', 'recipients']);

                return response()->json([
                    'message' => $message,
                    'status' => 'success'
                ], 201);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('Message creation failed:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'message' => 'Failed to send message',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred while sending the message'
            ], 500);
        }
    }

    public function index()
    {
        try {
            $messages = UserMessage::with(['sender', 'recipients'])
                ->whereHas('recipients', function ($query) {
                    $query->where('user_id', Auth::user()->id);
                })
                // Remove this line to hide messages where user is sender
                // ->orWhere('sender_id', Auth::user()->id)
                ->latest()
                ->take(10)
                ->get();

            return response()->json([
                'messages' => $messages,
                'status' => 'success'
            ]);
        } catch (\Throwable $th) {
            Log::error('Error fetching messages:', [
                'error' => $th->getMessage(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'message' => 'Failed to fetch messages',
                'error' => config('app.debug') ? $th->getMessage() : 'An error occurred'
            ], 500);
        }
    }

    public function markAsRead(UserMessage $message)
    {
        $message->recipients()
            ->updateExistingPivot(Auth::id(), ['read_at' => now()]);

        return response()->json(['success' => true]);
    }

    public function getUsers()
    {
        return response()->json([
            'users' => User::where('id', '!=', Auth::id())->get()
        ]);
    }
}
