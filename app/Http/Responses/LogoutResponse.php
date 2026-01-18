<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\LogoutResponse as LogoutResponseContract;
use Illuminate\Http\Request;

class LogoutResponse implements LogoutResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        // Get the user's school before they're logged out
        $user = $request->user();
        $schoolSlug = null;

        if ($user) {
            // Try to get school from user's relationship
            $schoolId = $user->schoolId();
            
            if ($schoolId) {
                $school = \App\Models\School::find($schoolId);
                if ($school) {
                    $schoolSlug = $school->school_slug;
                    // Store in session for future redirects
                    session(['last_school_slug' => $schoolSlug]);
                }
            }
        }

        // If we have a school slug, redirect to school login
        if ($schoolSlug) {
            return $request->wantsJson()
                ? new JsonResponse('', 204)
                : redirect()->route('school.login', ['school_slug' => $schoolSlug]);
        }

        // Fallback to default login page
        return $request->wantsJson()
            ? new JsonResponse('', 204)
            : redirect('/login');
    }
}
