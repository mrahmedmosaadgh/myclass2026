# Setting up Firebase Cloud Messaging in Laravel

## 1. Install Required Packages

First, install the Firebase Admin SDK for PHP:

```bash
composer require kreait/firebase-php
composer require kreait/laravel-firebase
```

## 2. Publish the Configuration

```bash
php artisan vendor:publish --provider="Kreait\Laravel\Firebase\ServiceProvider" --tag=config
```

## 3. Add Firebase Service Account Key

1. Go to your Firebase project settings
2. Navigate to "Service accounts" tab
3. Click "Generate new private key"
4. Download the JSON file
5. Save it in your Laravel project (e.g., `storage/app/firebase/firebase_credentials.json`)

## 4. Update .env File

Add the following to your `.env` file:

```
FIREBASE_CREDENTIALS=firebase/firebase_credentials.json
FIREBASE_DATABASE_URL=https://chatme-21ea6-default-rtdb.firebaseio.com
FIREBASE_SERVER_KEY=your_server_key_here
```

To get your server key:
1. Go to Firebase Console
2. Navigate to Project Settings
3. Go to Cloud Messaging tab
4. Copy the Server key

## 5. Create FCM Service

Create a new service class to handle FCM notifications:

```php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class FCMService
{
    public function sendNotification($token, $notification, $data = null)
    {
        $serverKey = config('services.firebase.server_key');
        
        $payload = [
            'to' => $token,
            'notification' => $notification,
        ];
        
        if ($data) {
            $payload['data'] = $data;
        }
        
        $response = Http::withHeaders([
            'Authorization' => 'key=' . $serverKey,
            'Content-Type' => 'application/json',
        ])->post('https://fcm.googleapis.com/fcm/send', $payload);
        
        return $response->json();
    }
    
    public function sendMultipleNotifications($tokens, $notification, $data = null)
    {
        $serverKey = config('services.firebase.server_key');
        
        $payload = [
            'registration_ids' => $tokens,
            'notification' => $notification,
        ];
        
        if ($data) {
            $payload['data'] = $data;
        }
        
        $response = Http::withHeaders([
            'Authorization' => 'key=' . $serverKey,
            'Content-Type' => 'application/json',
        ])->post('https://fcm.googleapis.com/fcm/send', $payload);
        
        return $response->json();
    }
}
```

## 6. Create Device Token Model and Migration

```bash
php artisan make:model DeviceToken -m
```

Update the migration file:

```php
public function up()
{
    Schema::create('device_tokens', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->string('token')->unique();
        $table->string('device_type')->nullable(); // 'android' or 'ios'
        $table->timestamps();
    });
}
```

Update the model:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeviceToken extends Model
{
    use HasFactory;
    
    protected $fillable = ['user_id', 'token', 'device_type'];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
```

## 7. Create API Endpoints for Device Token Management

```bash
php artisan make:controller API/DeviceTokenController
```

Implement the controller:

```php
<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\DeviceToken;
use Illuminate\Http\Request;

class DeviceTokenController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'token' => 'required|string',
            'device_type' => 'nullable|string|in:android,ios',
        ]);
        
        $user = auth()->user();
        
        // Update or create the device token
        DeviceToken::updateOrCreate(
            ['token' => $validated['token']],
            [
                'user_id' => $user->id,
                'device_type' => $validated['device_type'] ?? null,
            ]
        );
        
        return response()->json(['message' => 'Device token registered successfully']);
    }
    
    public function destroy(Request $request)
    {
        $validated = $request->validate([
            'token' => 'required|string',
        ]);
        
        $user = auth()->user();
        
        DeviceToken::where('user_id', $user->id)
            ->where('token', $validated['token'])
            ->delete();
        
        return response()->json(['message' => 'Device token removed successfully']);
    }
}
```

## 8. Add Routes

Add these routes to your `routes/api.php` file:

```php
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/device-tokens', [DeviceTokenController::class, 'store']);
    Route::delete('/device-tokens', [DeviceTokenController::class, 'destroy']);
});
```

## 9. Update User Model

Add a relationship to the User model:

```php
public function deviceTokens()
{
    return $this->hasMany(DeviceToken::class);
}
```

## 10. Create a Notification Helper

Create a helper class to send notifications to users:

```php
<?php

namespace App\Helpers;

use App\Services\FCMService;

class NotificationHelper
{
    protected $fcmService;
    
    public function __construct(FCMService $fcmService)
    {
        $this->fcmService = $fcmService;
    }
    
    public function sendNotificationToUser($user, $title, $body, $data = [])
    {
        $tokens = $user->deviceTokens()->pluck('token')->toArray();
        
        if (empty($tokens)) {
            return false;
        }
        
        $notification = [
            'title' => $title,
            'body' => $body,
            'sound' => 'default',
        ];
        
        return $this->fcmService->sendMultipleNotifications($tokens, $notification, $data);
    }
}
```
