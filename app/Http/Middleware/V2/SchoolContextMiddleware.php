<?php

namespace App\Http\Middleware\V2;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\School;

class SchoolContextMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $schoolId = $request->route('school_id');
        $schoolSlug = $request->route('school_slug');

        if (!$schoolId) {
            abort(404, 'School context required');
        }

        // Validate school exists
        $school = School::find($schoolId);
        
        if (!$school) {
            abort(404, 'School not found');
        }

        // Optional: Validate slug matches (SEO friendly URL check)
        // if ($schoolSlug && $school->slug !== $schoolSlug) {
        //     return redirect()->route($request->route()->getName(), array_merge($request->route()->parameters(), ['school_slug' => $school->slug]));
        // }

        // Store school in request/container for controllers to access easily
        $request->attributes->set('current_school', $school);
        app()->instance('current_school', $school);

        return $next($request);
    }
}
