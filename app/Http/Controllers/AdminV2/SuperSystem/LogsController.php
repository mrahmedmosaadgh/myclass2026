<?php

namespace App\Http\Controllers\AdminV2\SuperSystem;

use App\Http\Controllers\AdminV2\BaseV2Controller;
use Inertia\Inertia;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;

class LogsController extends BaseV2Controller
{
    public function index(Request $request)
    {
        $logFile = storage_path('logs/laravel.log');
        $level = $request->get('level', 'all');
        $search = $request->get('search', '');
        
        $logs = [];
        
        if (File::exists($logFile)) {
            $content = File::get($logFile);
            $lines = explode("\n", $content);
            
            // Parse log entries (simplified parser)
            $currentLog = null;
            foreach (array_reverse($lines) as $line) {
                if (preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] \w+\.(\w+): (.+)$/', $line, $matches)) {
                    if ($currentLog) {
                        $logs[] = $currentLog;
                    }
                    $currentLog = [
                        'timestamp' => $matches[1],
                        'level' => strtoupper($matches[2]),
                        'message' => $matches[3],
                        'context' => '',
                    ];
                } elseif ($currentLog && trim($line)) {
                    $currentLog['context'] .= $line . "\n";
                }
                
                // Limit to 100 entries
                if (count($logs) >= 100) {
                    break;
                }
            }
            
            if ($currentLog) {
                $logs[] = $currentLog;
            }
            
            // Filter by level
            if ($level !== 'all') {
                $logs = array_filter($logs, function($log) use ($level) {
                    return strtolower($log['level']) === strtolower($level);
                });
            }
            
            // Filter by search
            if ($search) {
                $logs = array_filter($logs, function($log) use ($search) {
                    return stripos($log['message'], $search) !== false || 
                           stripos($log['context'], $search) !== false;
                });
            }
        }
        
        return Inertia::render('AdminV2/SuperSystem/Logs', [
            'logs' => array_values($logs),
            'filters' => [
                'level' => $level,
                'search' => $search,
            ],
            'stats' => [
                'total' => count($logs),
                'file_size' => File::exists($logFile) ? File::size($logFile) : 0,
            ]
        ]);
    }

    public function download()
    {
        $logFile = storage_path('logs/laravel.log');
        
        if (File::exists($logFile)) {
            return response()->download($logFile);
        }
        
        return redirect()->back()->with('error', 'Log file not found');
    }

    public function clear()
    {
        $logFile = storage_path('logs/laravel.log');
        
        if (File::exists($logFile)) {
            File::put($logFile, '');
            return redirect()->back()->with('success', 'Log file cleared');
        }
        
        return redirect()->back()->with('error', 'Log file not found');
    }
}
