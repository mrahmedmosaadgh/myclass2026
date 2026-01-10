<?php

namespace App\Http\Controllers\AdminV2\SuperSystem;

use App\Http\Controllers\AdminV2\BaseV2Controller;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class ConfigController extends BaseV2Controller
{
    public function index()
    {
        $envPath = base_path('.env');
        $envContent = File::exists($envPath) ? File::get($envPath) : '';
        
        // Parse .env file into key-value pairs
        $envVars = [];
        foreach (explode("\n", $envContent) as $line) {
            $line = trim($line);
            if ($line && !str_starts_with($line, '#')) {
                $parts = explode('=', $line, 2);
                if (count($parts) === 2) {
                    $key = trim($parts[0]);
                    $value = trim($parts[1]);
                    
                    // Mask sensitive values
                    if (in_array($key, ['DB_PASSWORD', 'APP_KEY', 'AWS_SECRET_ACCESS_KEY', 'MAIL_PASSWORD'])) {
                        $value = '********';
                    }
                    
                    $envVars[] = [
                        'key' => $key,
                        'value' => $value,
                        'is_sensitive' => in_array($key, ['DB_PASSWORD', 'APP_KEY', 'AWS_SECRET_ACCESS_KEY', 'MAIL_PASSWORD']),
                    ];
                }
            }
        }
        
        return Inertia::render('AdminV2/SuperSystem/Config', [
            'envVars' => $envVars,
            'maintenanceMode' => app()->isDownForMaintenance(),
            'cacheStatus' => [
                'config' => File::exists(base_path('bootstrap/cache/config.php')),
                'routes' => File::exists(base_path('bootstrap/cache/routes-v7.php')),
                'views' => File::exists(storage_path('framework/views')),
            ]
        ]);
    }

    public function clearCache(Request $request)
    {
        $type = $request->get('type', 'all');
        
        switch ($type) {
            case 'config':
                Artisan::call('config:clear');
                break;
            case 'routes':
                Artisan::call('route:clear');
                break;
            case 'views':
                Artisan::call('view:clear');
                break;
            case 'all':
            default:
                Artisan::call('optimize:clear');
                break;
        }
        
        return redirect()->back()->with('success', ucfirst($type) . ' cache cleared successfully');
    }

    public function cacheConfig()
    {
        Artisan::call('config:cache');
        Artisan::call('route:cache');
        Artisan::call('view:cache');
        
        return redirect()->back()->with('success', 'Configuration cached successfully');
    }

    public function toggleMaintenance()
    {
        if (app()->isDownForMaintenance()) {
            Artisan::call('up');
            $message = 'Application is now live';
        } else {
            Artisan::call('down');
            $message = 'Application is now in maintenance mode';
        }
        
        return redirect()->back()->with('success', $message);
    }
}
