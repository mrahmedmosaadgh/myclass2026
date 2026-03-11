<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BMAssessmentGuard
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Require active assessment session to submit a response
        if (!$request->session()->has('active_bm_assessment_id') && !$request->has('assessment_id')) {
            abort(403, 'No active Basic Math assessment found.');
        }

        return $next($request);
    }
}
