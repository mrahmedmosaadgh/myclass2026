<?php

namespace App\Http\Controllers\AdminV2\SuperSystem;

use App\Http\Controllers\AdminV2\BaseV2Controller;
use Inertia\Inertia;

class DashboardController extends BaseV2Controller
{
    public function index()
    {
        return Inertia::render('AdminV2/SuperSystem/Dashboard', [
            'metrics' => [
                'system_status' => 'Healthy',
                'active_jobs' => 12,
                'failed_jobs' => 0,
                'error_rate' => '0.01%',
                'cpu_usage' => '15%',
                'memory_usage' => '32%',
                'disk_usage' => '45%',
            ],
            'recentLogs' => [
                ['id' => 1, 'level' => 'info', 'message' => 'System backup completed successfully.', 'created_at' => now()->subMinutes(10)->diffForHumans()],
                ['id' => 2, 'level' => 'warning', 'message' => 'High memory usage detected in worker node 2.', 'created_at' => now()->subHour()->diffForHumans()],
                ['id' => 3, 'level' => 'info', 'message' => 'New school "Al-Falah" registered.', 'created_at' => now()->subHours(2)->diffForHumans()],
                ['id' => 4, 'level' => 'error', 'message' => 'Failed to sync external calendar for User #402.', 'created_at' => now()->subHours(5)->diffForHumans()],
            ]
        ]);
    }
}
