<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class DeepAIController extends Controller
{
    public function generateText(Request $request)
    {
        try {
            $response = Http::withHeaders([
                'Api-Key' => env('DEEPAI_API_KEY'),
                'Content-Type' => 'application/json',
            ])->post('https://api.deepai.org/api/text-generator', [
                'text' => $request->input('text')
            ]);

            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }
}