<?php

namespace App\Services\Courses\bm;

class BMQuestionGenerator
{
    /**
     * Generate a question based on domain, sub-skill, and difficulty level.
     */
    public function generate(string $domain, int $difficulty): ?array
    {
        // Find a question matching criteria
        $questionModel = \App\Models\Courses\bm\BMQuestion::where('domain', $domain)
            ->where('difficulty', $difficulty)
            ->inRandomOrder()
            ->first();

        // Fallback to closest difficulty if exact match not found
        if (!$questionModel) {
            $questionModel = \App\Models\Courses\bm\BMQuestion::where('domain', $domain)
                ->orderByRaw('ABS(difficulty - ?)', [$difficulty])
                ->first();
        }

        if (!$questionModel) {
            return null;
        }

        $params = $questionModel->parameters_json ?? [];
        $values = [];

        // Parse 'range(min,max)' to array of values
        foreach ($params as $key => $rule) {
            if (preg_match('/range\((\d+),(\d+)\)/', $rule, $matches)) {
                $values[$key] = rand((int)$matches[1], (int)$matches[2]);
            } else {
                $values[$key] = rand(1, 10);
            }
        }

        $htmlText = $questionModel->template;
        $explanation = $questionModel->explanation;
        
        foreach ($values as $key => $val) {
            $htmlText = str_replace('{' . $key . '}', $val, $htmlText);
            $explanation = str_replace('{' . $key . '}', $val, $explanation);
        }

        $correctAnswer = 0;
        $a = $values['a'] ?? 0;
        $b = $values['b'] ?? 0;

        switch (strtolower($domain)) {
            case 'addition':
                $correctAnswer = $a + $b;
                $htmlText = "$a + $b = ?";
                break;
            case 'subtraction':
                // Ensure no negative answers for basic math
                if ($a < $b) {
                    $tmp = $a; $a = $b; $b = $tmp;
                }
                $correctAnswer = $a - $b;
                $htmlText = "$a - $b = ?";
                break;
            case 'multiplication':
                $correctAnswer = $a * $b;
                $htmlText = "$a × $b = ?";
                break;
            case 'division':
                // Ensure clean division
                $correctAnswer = $a;
                $a = $a * $b;
                $htmlText = "$a ÷ $b = ?";
                break;
            case 'fractions':
                // Generate a proper fraction addition question
                // Ensure b is between 2 and 10 (denominator)
                $b = max(2, min($b, 10));
                // a is the numerator, ensure a < b for a proper fraction
                $a = rand(1, $b - 1);
                $c = rand(1, $b - 1);
                $correctAnswer = $a + $c;
                $htmlText = "$a/$b + $c/$b = ?/$b";
                break;
        }

        return [
            'id' => $questionModel->id,
            'domain' => $domain,
            'difficulty' => $questionModel->difficulty,
            'htmlText' => $htmlText,
            'correct_answer' => (string)$correctAnswer,
            'explanation' => $explanation,
        ];
    }
}
