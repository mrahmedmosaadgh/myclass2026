<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AIProxyController extends Controller
{
    protected $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.deepai.api_key');
        $this->middleware('throttle:ai-api');
    }

    public function proxyRequest(Request $request)
    {
        try {
            // Validate the request
            $request->validate([
                'text' => 'required|string|max:1000',
                'model' => 'sometimes|string',
                'max_tokens' => 'sometimes|integer|min:1|max:512'
            ]);

            // Generate cache key
            $cacheKey = 'ai_response_' . md5($request->input('text'));

            // Check cache
            if ($cachedResponse = Cache::get($cacheKey)) {
                return response()->json([
                    'output' => $cachedResponse,
                    'cached' => true
                ]);
            }

            // Make API request
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(30)->post('https://api.deepai.org/api/text-generator', [
                'text' => $request->input('text'),
                'max_tokens' => $request->input('max_tokens', 256)
            ]);

            // Log the response for debugging
            Log::info('DeepAI Response', [
                'status' => $response->status(),
                'body' => $response->json()
            ]);

            // Check if request was successful
            if (!$response->successful()) {
                throw new \Exception('DeepAI API returned status: ' . $response->status());
            }

            $output = $response->json('output', 'Sorry, I could not generate a response.');

            // Cache successful response
            Cache::put($cacheKey, $output, now()->addHours(24));

            return response()->json([
                'output' => $output,
                'cached' => false
            ]);

        } catch (\Exception $e) {
            Log::error('AI Proxy Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);

            return response()->json([
                'error' => true,
                'message' => 'An error occurred while processing your request. Please try again later.'
            ], 500);
        }
    }
}

