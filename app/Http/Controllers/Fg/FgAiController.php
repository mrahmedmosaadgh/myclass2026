<?php

namespace App\Http\Controllers\Fg;

use App\Http\Controllers\Controller;
use App\Services\Fg\FgAiService;
use Illuminate\Http\Request;

class FgAiController extends Controller
{
    protected $aiService;

    public function __construct(FgAiService $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * AI Vent Endpoint
     * Take user free-form text and convert to tasks/notes
     */
    public function vent(Request $request)
    {
        $request->validate([
            'text' => 'required|string|max:5000',
        ]);

        $result = $this->aiService->parseVentText($request->input('text'));

        return response()->json($result);
    }
}
