<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiController extends Controller
{
    public function chat(Request $request)
    {
        try {
            $request->validate([
                'text' => 'required|string|max:2000',
            ]);

            // Replace with your actual API endpoint and key
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . config('services.openai.api_key'),
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    ['role' => 'user', 'content' => $request->text]
                ],
                'temperature' => 0.7,
                'max_tokens' => 500,
            ]);

            if (!$response->successful()) {
                Log::error('OpenAI API Error', [
                    'status' => $response->status(),
                    'response' => $response->json(),
                ]);
                
                return response()->json([
                    'error' => 'Failed to get response from AI service'
                ], 500);
            }

            return response()->json([
                'output' => $response->json()['choices'][0]['message']['content']
            ]);

        } catch (\Exception $e) {
            Log::error('AI Chat Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'An error occurred while processing your request'
            ], 500);
        }
    }
}