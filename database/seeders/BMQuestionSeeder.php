<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BMQuestionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $domains = ['Addition', 'Subtraction', 'Multiplication', 'Division', 'Fractions'];
        $questions = [];

        foreach ($domains as $domain) {
            for ($i = 1; $i <= 20; $i++) {
                $difficulty = ceil($i / 2); // 1 to 10 scale
                
                $questions[] = [
                    'domain' => $domain,
                    'sub_skill' => "Basic $domain Lvl $difficulty",
                    'difficulty' => $difficulty,
                    'template' => "Sample template for $domain {a} and {b}",
                    'parameters_json' => json_encode(['a' => 'range(1,10)', 'b' => 'range(1,10)']),
                    'correct_answer' => 'calculated_on_fly',
                    'explanation' => "Step by step for $domain.",
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        \App\Models\Courses\bm\BMQuestion::insert($questions);
    }
}
