<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'deepai' => [
        'api_key' => env('DEEPAI_API_KEY'),
    ],

    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
    ],

    'deepseek' => [
        'api_key' => env('DEEPSEEK_API_KEY'),
        'base_url' => env('DEEPSEEK_BASE_URL', 'https://api.deepseek.com'),
    ],

    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
    ],

    'github_models' => [
        'api_key' => env('GITHUB_MODELS_TOKEN'),
        'base_url' => 'https://models.inference.ai.azure.com',
    ],

    'lightpanda' => [
        'binary'  => env('LIGHTPANDA_BINARY', 'lightpanda'),
        'timeout' => env('LIGHTPANDA_TIMEOUT', 30),
    ],

    'print_pdf' => [
        'node_binary' => env('PRINT_PDF_NODE_BINARY', 'node'),
        'script_path' => env('PRINT_PDF_SCRIPT_PATH', base_path('tools/print-calibration/render-pdf.js')),
        'working_dir' => env('PRINT_PDF_WORKING_DIR', base_path('tools/print-calibration')),
        'timeout' => env('PRINT_PDF_TIMEOUT', 60),
        'max_pages' => env('PRINT_PDF_MAX_PAGES', 60),
        'strict_environment_check' => env('PRINT_PDF_STRICT_ENV_CHECK', true),
        'metrics_path' => env('PRINT_PDF_METRICS_PATH', base_path('tools/print-calibration/metrics.v1.json')),
    ],

    'firebase' => [
        'api_key' => env('VITE_FIREBASE_API_KEY'),
        'auth_domain' => env('VITE_FIREBASE_AUTH_DOMAIN'),
        'database_url' => env('FIREBASE_DATABASE_URL', env('VITE_FIREBASE_DATABASE_URL')),
        'project_id' => env('VITE_FIREBASE_PROJECT_ID'),
        'storage_bucket' => env('VITE_FIREBASE_STORAGE_BUCKET'),
        'messaging_sender_id' => env('VITE_FIREBASE_MESSAGING_SENDER_ID'),
        'app_id' => env('VITE_FIREBASE_APP_ID'),
        'measurement_id' => env('VITE_FIREBASE_MEASUREMENT_ID'),
    ],
];


