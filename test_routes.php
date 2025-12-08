<?php

// Simple test script to check if our routes are registered
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

// Get all routes
$routes = $app->make('router')->getRoutes();

echo "Resume API Routes:\n";
echo "==================\n";

foreach ($routes as $route) {
    $uri = $route->uri();
    $methods = implode('|', $route->methods());
    
    // Filter for resume-related routes
    if (strpos($uri, 'resume-question') !== false || strpos($uri, 'resume-answer') !== false || strpos($uri, 'media/upload') !== false) {
        echo sprintf("%-8s %s\n", $methods, $uri);
    }
}

echo "\nDone!\n";
