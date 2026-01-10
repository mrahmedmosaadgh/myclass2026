<?php

namespace App\Http\Controllers\AdminV2\SuperSystem;

use App\Http\Controllers\AdminV2\BaseV2Controller;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

class JobsController extends BaseV2Controller
{
    public function index()
    {
        // Get jobs from the jobs table
        $pendingJobs = DB::table('jobs')
            ->select('id', 'queue', 'payload', 'attempts', 'created_at')
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get()
            ->map(function ($job) {
                $payload = json_decode($job->payload, true);
                return [
                    'id' => $job->id,
                    'queue' => $job->queue,
                    'name' => $payload['displayName'] ?? 'Unknown Job',
                    'attempts' => $job->attempts,
                    'created_at' => $job->created_at,
                ];
            });

        // Get failed jobs
        $failedJobs = DB::table('failed_jobs')
            ->select('id', 'uuid', 'connection', 'queue', 'payload', 'exception', 'failed_at')
            ->orderBy('failed_at', 'desc')
            ->limit(20)
            ->get()
            ->map(function ($job) {
                $payload = json_decode($job->payload, true);
                $exception = substr($job->exception, 0, 200);
                return [
                    'id' => $job->id,
                    'uuid' => $job->uuid,
                    'queue' => $job->queue,
                    'name' => $payload['displayName'] ?? 'Unknown Job',
                    'exception' => $exception,
                    'failed_at' => $job->failed_at,
                ];
            });

        return Inertia::render('AdminV2/SuperSystem/Jobs', [
            'pendingJobs' => $pendingJobs,
            'failedJobs' => $failedJobs,
            'stats' => [
                'pending_count' => DB::table('jobs')->count(),
                'failed_count' => DB::table('failed_jobs')->count(),
            ]
        ]);
    }

    public function retry($id)
    {
        Artisan::call('queue:retry', ['id' => [$id]]);
        
        return redirect()->back()->with('success', 'Job queued for retry');
    }

    public function retryAll()
    {
        Artisan::call('queue:retry', ['id' => ['all']]);
        
        return redirect()->back()->with('success', 'All failed jobs queued for retry');
    }

    public function forget($id)
    {
        DB::table('failed_jobs')->where('id', $id)->delete();
        
        return redirect()->back()->with('success', 'Failed job deleted');
    }

    public function flush()
    {
        DB::table('failed_jobs')->truncate();
        
        return redirect()->back()->with('success', 'All failed jobs deleted');
    }
}
