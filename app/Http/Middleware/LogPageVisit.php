<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\ActivityLog;

class LogPageVisit
{
    public function handle(Request $request, Closure $next)
    {
        // Log the page visit for authenticated users
        // Only log if explicitly enabled in config (defaults to false)
        if (\Auth::check() && config('activitylog.enabled', false)) {
            ActivityLog::create([
                'user_id' => \Auth::id(),
                'activity' => 'Visited a page',
                'page_url' => $request->url(),
            ]);
        }

        return $next($request);
    }
}


