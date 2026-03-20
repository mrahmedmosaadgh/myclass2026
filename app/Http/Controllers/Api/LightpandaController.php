<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\LightpandaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LightpandaController extends Controller
{
    public function __construct(protected LightpandaService $lightpanda) {}

    /**
     * Fetch rendered HTML from a URL.
     * POST /api/lightpanda/fetch
     */
    public function fetch(Request $request): JsonResponse
    {
        $request->validate(['url' => 'required|url']);

        $result = $this->lightpanda->fetchHtml($request->url);

        if ($result['error']) {
            return response()->json(['error' => $result['error']], 422);
        }

        return response()->json(['html' => $result['html']]);
    }

    /**
     * Extract plain text from a URL.
     * POST /api/lightpanda/extract-text
     */
    public function extractText(Request $request): JsonResponse
    {
        $request->validate(['url' => 'required|url']);

        $result = $this->lightpanda->extractText($request->url);

        if ($result['error']) {
            return response()->json(['error' => $result['error']], 422);
        }

        return response()->json(['text' => $result['text']]);
    }
}
