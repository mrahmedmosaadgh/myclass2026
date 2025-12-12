<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class VocabularyFlashcardsController extends Controller
{
    /**
     * Display the vocabulary flashcards page
     */
    public function index()
    {
        // Sample vocabulary data - in a real app, this would come from a database
        $vocabulary = [
            ['text' => 'water weeds', 'translation' => 'أعشاب مائية'],
            ['text' => 'The Nile River', 'translation' => 'نهر النيل'],
            ['text' => 'insects', 'translation' => 'حشرات'],
            ['text' => 'hunt', 'translation' => 'يصطاد'],
            ['text' => 'crocodile', 'translation' => 'تمساح'],
            ['text' => 'desert', 'translation' => 'صحراء'],
            ['text' => 'mountain', 'translation' => 'جبل'],
            ['text' => 'ocean', 'translation' => 'محيط'],
            ['text' => 'forest', 'translation' => 'غابة'],
            ['text' => 'butterfly', 'translation' => 'فراشة'],
            ['text' => 'elephant', 'translation' => 'فيل'],
            ['text' => 'rainbow', 'translation' => 'قوس قزح']
        ];

        return Inertia::render('VocabularyFlashcards/Index', [
            'vocabulary' => $vocabulary,
            'mode' => 'practice' // default mode
        ]);
    }

    /**
     * Store vocabulary data (for future use)
     */
    public function store(Request $request)
    {
        $request->validate([
            'vocabulary' => 'required|array',
            'vocabulary.*.text' => 'required|string|max:255',
            'vocabulary.*.translation' => 'required|string|max:255',
        ]);

        // In a real application, you would save this to a database
        // For now, we'll just return success
        return response()->json([
            'message' => 'Vocabulary saved successfully',
            'vocabulary' => $request->vocabulary
        ]);
    }
}