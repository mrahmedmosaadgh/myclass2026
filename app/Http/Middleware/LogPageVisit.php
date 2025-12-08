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
        if (\Auth::check()) {
            ActivityLog::create([
                'user_id' => \Auth::id(),
                'activity' => 'Visited a page',
                'page_url' => $request->url(),
            ]);
        }

        return $next($request);
    }
}


