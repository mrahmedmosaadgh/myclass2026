<?php

namespace App\Http\Controllers;

use App\Models\QuestionType;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;

class QuestionTypeController extends Controller
{
    /**
     * Get all question types.
     * 
     * Returns all available question types with their properties.
     * 
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $questionTypes = QuestionType::all();

            return response()->json([
                'success' => true,
                'data' => $questionTypes,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'QUESTION_TYPES_ERROR',
                    'message' => 'Failed to retrieve question types',
                    'details' => config('app.debug') ? $e->getMessage() : null,
                    'timestamp' => now()->toIso8601String(),
                ],
            ], 500);
        }
    }
}