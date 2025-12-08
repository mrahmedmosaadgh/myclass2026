<?php

require_once 'vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔍 Testing Authentication and API Access\n\n";

// Get a user
$user = App\Models\User::first();
echo "✅ Found user: {$user->name} (ID: {$user->id})\n";

// Create a personal access token for testing
$token = $user->createToken('test-token')->plainTextToken;
echo "✅ Created test token: " . substr($token, 0, 20) . "...\n";

// Test the API endpoint with authentication
$url = 'http://127.0.0.1:8000/api/answers/4/comments';
$headers = [
    'Authorization: Bearer ' . $token,
    'Accept: application/json',
    'Content-Type: application/json'
];

echo "\n🌐 Testing API endpoint: $url\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo "❌ cURL Error: $error\n";
} else {
    echo "✅ HTTP Status: $httpCode\n";
    echo "📄 Response: " . substr($response, 0, 200) . "...\n";
    
    if ($httpCode === 200) {
        $data = json_decode($response, true);
        if ($data) {
            echo "✅ JSON Response parsed successfully\n";
            echo "📊 Comments count: " . count($data) . "\n";
        }
    }
}

// Test POST request (add comment)
echo "\n🌐 Testing POST request (add comment)\n";

$postData = json_encode(['comment' => 'Test comment from API test']);
$postUrl = 'http://127.0.0.1:8000/api/answers/4/comments';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $postUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo "❌ cURL Error: $error\n";
} else {
    echo "✅ HTTP Status: $httpCode\n";
    echo "📄 Response: " . substr($response, 0, 200) . "...\n";
    
    if ($httpCode === 201) {
        echo "✅ Comment created successfully!\n";
    } elseif ($httpCode === 422) {
        echo "⚠️ Validation error (expected for test)\n";
    }
}

// Clean up - delete the test token
$user->tokens()->where('name', 'test-token')->delete();
echo "\n🧹 Test token cleaned up\n";

echo "\n✅ Authentication API Test Complete\n";
