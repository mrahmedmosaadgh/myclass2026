<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        if (! $request->expectsJson()) {
            // Try to get user's school from session or cookie
            $user = $request->user();
            
            if ($user) {
                $schoolId = $user->schoolId();
                
                if ($schoolId) {
                    $school = \App\Models\School::find($schoolId);
                    if ($school) {
                        return route('school.login', ['school_slug' => $school->school_slug]);
                    }
                }
            }
            
            // Check if there's a last visited school in session
            if (session()->has('last_school_slug')) {
                return route('school.login', ['school_slug' => session('last_school_slug')]);
            }
            
            // Fallback to default login
            return route('login');
        }

        return null;
    }
}
