<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie', 'csrf-cookie', 'api/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => ['*'],  // In production, specify your actual domain
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => ['X-XSRF-TOKEN'],
    'max_age' => 0,
    'supports_credentials' => true,
];

