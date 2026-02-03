<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Smart Score Settings
    |--------------------------------------------------------------------------
    |
    | Defines the thresholds for different mastery levels and scoring parameters
    |
    */

    'mastery_thresholds' => [
        'beginner' => ['min' => 0, 'max' => 19],
        'developing' => ['min' => 20, 'max' => 49],
        'proficient' => ['min' => 50, 'max' => 79],
        'advanced' => ['min' => 80, 'max' => 99],
        'master' => ['min' => 100, 'max' => null], // No upper limit
    ],

    /*
    |--------------------------------------------------------------------------
    | Smart Scoring Parameters
    |--------------------------------------------------------------------------
    |
    | Parameters that control how the smart scoring algorithm works
    |
    */

    'scoring' => [
        // Points gained/lost for correct/incorrect answers at different difficulty levels
        'easy_correct' => 2,
        'medium_correct' => 5,
        'hard_correct' => 8,
        'easy_incorrect' => -1,
        'medium_incorrect' => -3,
        'hard_incorrect' => -5,

        // Bonus points for consecutive correct answers (streaks)
        'streak_bonus' => 2,
        'max_streak_bonus' => 10, // Maximum bonus that can be accumulated through streaks

        // Time-based bonuses/penalties
        'optimal_response_time_low' => 5000,  // 5 seconds - minimum for optimal time bonus
        'optimal_response_time_high' => 30000, // 30 seconds - maximum for optimal time bonus
        'time_bonus' => 1, // Points added for answering in optimal time
        'slow_response_penalty' => -1, // Penalty for taking too long (guessing)
    ],

    /*
    |--------------------------------------------------------------------------
    | Adaptive Question Selection
    |--------------------------------------------------------------------------
    |
    | Parameters that control how questions are selected adaptively
    |
    */

    'adaptive_selection' => [
        // How many recent questions to exclude from selection
        'exclude_recent_count' => 5,

        // Minimum number of questions a skill should have
        'min_questions_per_skill' => 3,

        // How far from the target difficulty to expand search if needed
        'difficulty_search_range' => 2,
    ],

    /*
    |--------------------------------------------------------------------------
    | Awards and Badges
    |--------------------------------------------------------------------------
    |
    | Configuration for achievement badges and awards
    |
    */

    'awards' => [
        'streak_5' => [
            'name' => 'Consistency King/Queen',
            'description' => 'Achieved a streak of 5+ correct answers',
            'criteria' => ['min_streak' => 5]
        ],
        'first_mastery' => [
            'name' => 'Mastery Achieved',
            'description' => 'First time reaching master level in a skill',
            'criteria' => ['min_smart_score' => 100]
        ],
        'rapid_responder' => [
            'name' => 'Quick Thinker',
            'description' => 'Consistently answers questions quickly',
            'criteria' => ['max_avg_response_time' => 10] // in seconds
        ],
        'accuracy_master' => [
            'name' => 'Accuracy Master',
            'description' => 'Maintains high accuracy rate',
            'criteria' => ['min_accuracy_rate' => 85] // in percent
        ],
        'fast_finisher' => [
            'name' => 'Fast Finisher',
            'description' => 'Completes practice sessions quickly',
            'criteria' => ['max_avg_session_time' => 600] // in seconds
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Practice Session Settings
    |--------------------------------------------------------------------------
    |
    | Configuration options for practice sessions
    |
    */

    'session' => [
        // Default number of questions per practice session (0 for unlimited)
        'default_question_count' => 10,
        
        // Minimum time before allowing skip (to prevent random clicking)
        'minimum_time_before_skip' => 3000, // in milliseconds
        
        // Time limits for practice sessions (null for unlimited)
        'max_session_duration' => null, // in minutes, null for unlimited
        
        // Auto-save interval during practice session
        'autosave_interval' => 60, // in seconds
    ],

    /*
    |--------------------------------------------------------------------------
    | Difficulty Levels
    |--------------------------------------------------------------------------
    |
    | Definitions for how difficulty levels are categorized
    |
    */

    'difficulty_levels' => [
        '1' => ['name' => 'Very Easy', 'color' => '#10B981'], // emerald-500
        '2' => ['name' => 'Easy', 'color' => '#34D399'],     // emerald-400
        '3' => ['name' => 'Rather Easy', 'color' => '#6EE7B7'], // emerald-300
        '4' => ['name' => 'Lower Medium', 'color' => '#9AE8DE'], // custom
        '5' => ['name' => 'Medium', 'color' => '#FBBF24'],   // amber-400
        '6' => ['name' => 'Higher Medium', 'color' => '#FCD34D'], // amber-300
        '7' => ['name' => 'Rather Difficult', 'color' => '#FCA5A5'], // rose-300
        '8' => ['name' => 'Difficult', 'color' => '#F87171'], // rose-400
        '9' => ['name' => 'Hard', 'color' => '#EF4444'],     // red-500
        '10' => ['name' => 'Very Hard', 'color' => '#DC2626'], // red-600
    ],
];