<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIController extends Controller
{
    /**
     * Handle AI completion request
     */
    public function complete(Request $request)
    {
        $request->validate([
            'prompt' => 'required|string|max:5000',
            'context' => 'nullable|array',
            'systemMessage' => 'nullable|string|max:1000',
            'temperature' => 'nullable|numeric|min:0|max:2',
            'maxTokens' => 'nullable|integer|min:1|max:4000',
            'model' => 'nullable|string|in:deepseek,gemini,github',
        ]);

        $prompt = $request->input('prompt');
        $context = $request->input('context', []);
        $systemMessage = $request->input('systemMessage', 'You are a helpful AI assistant for an educational platform. Provide clear, accurate, and educational responses.');
        $temperature = $request->input('temperature', 0.7);
        $maxTokens = $request->input('maxTokens', 2000);
        $model = $request->input('model', 'deepseek'); // Default to deepseek

        try {
            if ($model === 'gemini') {
                return $this->callGemini($prompt, $systemMessage, $temperature, $maxTokens, $context);
            } elseif ($model === 'github') {
                return $this->callGitHubModels($prompt, $systemMessage, $temperature, $maxTokens, $context);
            } else {
                return $this->callDeepSeek($prompt, $systemMessage, $temperature, $maxTokens, $context);
            }
        } catch (\Exception $e) {
            Log::error('AI completion error', [
                'model' => $model,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Failed to process AI request',
                'message' => config('app.debug') ? $e->getMessage() : 'An error occurred',
            ], 500);
        }
    }

    /**
     * Call DeepSeek API
     */
    private function callDeepSeek($prompt, $systemMessage, $temperature, $maxTokens, $context)
    {
        // Build messages array
        $messages = [
            ['role' => 'system', 'content' => $systemMessage]
        ];

        // Add context if provided
        if (!empty($context['previousMessages'])) {
            foreach ($context['previousMessages'] as $msg) {
                $messages[] = $msg;
            }
        }

        // Add user prompt
        $messages[] = ['role' => 'user', 'content' => $prompt];

        // Call DeepSeek API
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('services.deepseek.api_key'),
            'Content-Type' => 'application/json',
        ])->timeout(30)->post(config('services.deepseek.base_url') . '/chat/completions', [
            'model' => 'deepseek-chat',
            'messages' => $messages,
            'temperature' => $temperature,
            'max_tokens' => $maxTokens,
            'stream' => false,
        ]);

        if ($response->failed()) {
            Log::error('DeepSeek API error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return response()->json([
                'error' => 'AI service temporarily unavailable',
                'message' => 'Please try again later',
            ], 503);
        }

        $data = $response->json();

        return response()->json([
            'success' => true,
            'content' => $data['choices'][0]['message']['content'] ?? '',
            'usage' => $data['usage'] ?? null,
            'model' => 'deepseek',
        ]);
    }

    /**
     * Call Gemini API
     */
    private function callGemini($prompt, $systemMessage, $temperature, $maxTokens, $context)
    {
        // Combine system message and prompt for Gemini
        $fullPrompt = $systemMessage . "\n\n" . $prompt;

        // Add context if provided
        if (!empty($context['previousMessages'])) {
            $contextText = '';
            foreach ($context['previousMessages'] as $msg) {
                $contextText .= $msg['role'] . ': ' . $msg['content'] . "\n";
            }
            $fullPrompt = $contextText . "\n" . $fullPrompt;
        }

        // Call Gemini API
        $response = Http::timeout(30)->post(
            'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent',
            [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $fullPrompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => $temperature,
                    'maxOutputTokens' => $maxTokens,
                ]
            ],
            [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'query' => [
                    'key' => config('services.gemini.api_key')
                ]
            ]
        );

        if ($response->failed()) {
            Log::error('Gemini API error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return response()->json([
                'error' => 'AI service temporarily unavailable',
                'message' => 'Please try again later',
            ], 503);
        }

        $data = $response->json();

        return response()->json([
            'success' => true,
            'content' => $data['candidates'][0]['content']['parts'][0]['text'] ?? '',
            'usage' => $data['usageMetadata'] ?? null,
            'model' => 'gemini',
        ]);
    }

    /**
     * Call GitHub Models API (GPT-4o via Azure)
     */
    private function callGitHubModels($prompt, $systemMessage, $temperature, $maxTokens, $context)
    {
        // Build messages array (OpenAI format)
        $messages = [
            ['role' => 'system', 'content' => $systemMessage]
        ];

        // Add context if provided
        if (!empty($context['previousMessages'])) {
            foreach ($context['previousMessages'] as $msg) {
                $messages[] = $msg;
            }
        }

        // Add user prompt
        $messages[] = ['role' => 'user', 'content' => $prompt];

        // Call GitHub Models API (uses OpenAI-compatible endpoint)
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('services.github_models.api_key'),
            'Content-Type' => 'application/json',
        ])->timeout(30)->post(config('services.github_models.base_url') . '/chat/completions', [
            'model' => 'gpt-4o',
            'messages' => $messages,
            'temperature' => $temperature,
            'max_tokens' => $maxTokens,
            'stream' => false,
        ]);

        if ($response->failed()) {
            Log::error('GitHub Models API error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return response()->json([
                'error' => 'AI service temporarily unavailable',
                'message' => 'Please try again later',
            ], 503);
        }

        $data = $response->json();

        return response()->json([
            'success' => true,
            'content' => $data['choices'][0]['message']['content'] ?? '',
            'usage' => $data['usage'] ?? null,
            'model' => 'github (GPT-4o)',
        ]);
    }
}
