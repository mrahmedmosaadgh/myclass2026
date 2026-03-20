<?php

namespace App\Services\Fg;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FgAiService
{
    protected $parserService;

    public function __construct(FgVentParserService $parserService)
    {
        $this->parserService = $parserService;
    }

    /**
     * Sends the free-form vent text to the AI and parses the result
     */
    public function parseVentText(string $ventText)
    {
        $systemPrompt = $this->getSystemPrompt();

        try {
            // Using the available OpenAI config pattern from the project
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . config('services.openai.api_key'),
                'Content-Type' => 'application/json',
            ])->timeout(45)->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-4o-mini', // Or gpt-3.5-turbo depending on what the user has
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $ventText]
                ],
                'temperature' => 0.3,
                'response_format' => ['type' => 'json_object'],
            ]);

            if (!$response->successful()) {
                Log::error('Focus Grid AI Vent Error', [
                    'status' => $response->status(),
                    'response' => $response->json(),
                ]);
                throw new \Exception('Failed to get response from AI service');
            }

            $content = $response->json()['choices'][0]['message']['content'];
            
            // The response_format guarantees JSON from OpenAI, but we use the parser 
            // to extract the tasks and notes safely and handle formatting
            return $this->parserService->parse($content);

        } catch (\Exception $e) {
            Log::error('Focus Grid AI Service Exception', [
                'message' => $e->getMessage(),
            ]);

            // Fallback: Just return it as one raw note if AI fails
            return [
                'tasks' => [],
                'notes' => [
                    [
                        'body' => $ventText,
                        'tags' => ['vent', 'raw']
                    ]
                ]
            ];
        }
    }

    protected function getSystemPrompt(): string
    {
        return <<<PROMPT
You are Focus Grid, a personal clarity and task extraction assistant. 
The user is doing a "mind dump" or "vent". They will ramble about things on their mind, tasks they need to do, anxieties, or random thoughts.

Your job is to read carefully and extract:
1. `tasks`: Actionable items they need to do. Provide a clear title, optional notes/context, and suggested tags.
2. `notes`: Non-actionable thoughts, observations, anxieties, or general information that should just be saved for reference.

Return the result STRICTLY as a JSON object matching this schema:
{
  "tasks": [
    {
      "title": "Clear action-oriented title",
      "notes": "Any context or sub-details mentioned",
      "tags": ["suggested", "tags"]
    }
  ],
  "notes": [
    {
      "body": "The core thought, observation, or anxiety",
      "tags": ["suggested", "tags"]
    }
  ]
}

DO NOT include markdown formatting outside the JSON block. Return ONLY raw JSON.
Keep tasks concise and start with a verb if possible. 
Be empathetic in notes extraction, capturing the essence of their thoughts.
PROMPT;
    }
}
