<?php

namespace App\Services\Fg;

use Illuminate\Support\Facades\Log;

class FgVentParserService
{
    /**
     * Safely parse the AI output JSON string into the expected structure
     */
    public function parse(string $aiContent): array
    {
        // Strip markdown backticks if AI decided to wrap it anyway
        $cleanContent = preg_replace('/```json\s*(.*?)\s*```/s', '$1', $aiContent);
        $cleanContent = trim($cleanContent);

        $parsed = json_decode($cleanContent, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('Focus Grid Vent Parse Error', [
                'error' => json_last_error_msg(),
                'raw_content' => $aiContent
            ]);
            
            return [
                'tasks' => [],
                'notes' => [
                    ['body' => 'Failed to parse AI output. Raw: ' . $aiContent, 'tags' => ['error']]
                ]
            ];
        }

        // Validate structure
        $tasks = [];
        $notes = [];

        if (isset($parsed['tasks']) && is_array($parsed['tasks'])) {
            foreach ($parsed['tasks'] as $task) {
                if (!empty($task['title'])) {
                    $tasks[] = [
                        'title' => $task['title'],
                        'notes' => $task['notes'] ?? null,
                        'tags' => $task['tags'] ?? [],
                    ];
                }
            }
        }

        if (isset($parsed['notes']) && is_array($parsed['notes'])) {
            foreach ($parsed['notes'] as $note) {
                if (!empty($note['body'])) {
                    $notes[] = [
                        'body' => $note['body'],
                        'tags' => $note['tags'] ?? [],
                    ];
                }
            }
        }

        return [
            'tasks' => $tasks,
            'notes' => $notes,
        ];
    }
}
