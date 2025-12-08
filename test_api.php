<?php

require_once 'vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Test database connection
try {
    $user = App\Models\User::first();
    echo "✅ Database connection successful\n";
    echo "First user: " . $user->name . " (ID: " . $user->id . ")\n";
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Test if resume answers exist
try {
    $answers = App\Models\ResumeAnswer::with('question')->take(3)->get();
    echo "✅ Found " . $answers->count() . " resume answers\n";
    
    foreach ($answers as $answer) {
        echo "  - Answer ID: {$answer->id}, Question: " . substr($answer->question->question_text, 0, 50) . "...\n";
    }
} catch (Exception $e) {
    echo "❌ Error fetching answers: " . $e->getMessage() . "\n";
}

// Test if comments exist
try {
    $comments = App\Models\ResumeQuestionComment::take(3)->get();
    echo "✅ Found " . $comments->count() . " comments\n";
} catch (Exception $e) {
    echo "❌ Error fetching comments: " . $e->getMessage() . "\n";
}

// Test if ratings exist
try {
    $ratings = App\Models\ResumeAnswerRating::take(3)->get();
    echo "✅ Found " . $ratings->count() . " ratings\n";
} catch (Exception $e) {
    echo "❌ Error fetching ratings: " . $e->getMessage() . "\n";
}

// Test if likes exist
try {
    $likes = App\Models\ResumeAnswerLike::take(3)->get();
    echo "✅ Found " . $likes->count() . " likes\n";
} catch (Exception $e) {
    echo "❌ Error fetching likes: " . $e->getMessage() . "\n";
}

echo "\n🔍 Testing API Controller Methods:\n";

// Test the controller method directly
try {
    $controller = new App\Http\Controllers\Api\ResumeAnswerController();
    
    // Create a mock request with a user
    $request = Request::create('/api/answers/4/comments', 'GET');
    $request->setUserResolver(function () use ($user) {
        return $user;
    });
    
    // Test getComments method
    $response = $controller->getComments(4);
    $data = $response->getData(true);
    
    echo "✅ getComments API method works\n";
    echo "  - Returned " . count($data) . " comments\n";
    
} catch (Exception $e) {
    echo "❌ Error testing getComments: " . $e->getMessage() . "\n";
    echo "  Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n✅ API Test Complete\n";
