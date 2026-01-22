<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\School;
use Illuminate\Http\Request;

class LoginRedirectController extends Controller
{
    /**
     * Detect user's school and redirect to school-specific login
     */
    public function detectSchool(Request $request)
    {
        $request->validate([
            'email' => 'required|string',
        ]);

        // Find user by email or username
        $user = User::where('email', $request->email)
            ->orWhere('name', $request->email)
            ->first();

        if (!$user) {
            return response()->json([
                'error' => 'User not found',
                'redirect' => null,
            ], 404);
        }

        // Get user's school - try direct column first, then method
        $schoolId = $user->school_id ?? null;
        
        // If no direct school_id, try the schoolId() method
        if (!$schoolId && method_exists($user, 'schoolId')) {
            $schoolId = $user->schoolId();
        }
        
        if (!$schoolId) {
            return response()->json([
                'error' => 'No school associated with this user',
                'redirect' => null,
            ], 404);
        }

        $school = School::find($schoolId);
        
        if (!$school || !$school->is_active) {
            return response()->json([
                'error' => 'School not found or inactive',
                'redirect' => null,
            ], 404);
        }

        // Return school login URL
        $loginUrl = route('school.login', ['school_slug' => $school->school_slug]);

        return response()->json([
            'redirect' => $loginUrl,
            'school_name' => $school->name,
            'school_slug' => $school->school_slug,
        ]);
    }

    /**
     * Get school information by slug (for localStorage-based detection)
     */
    public function getSchoolBySlug(Request $request)
    {
        $request->validate([
            'slug' => 'required|string',
        ]);

        $school = School::where('is_active', true)
            ->get()
            ->first(function ($school) use ($request) {
                return $school->school_slug === $request->slug;
            });

        if (!$school) {
            return response()->json([
                'error' => 'School not found or inactive',
                'valid' => false,
            ], 404);
        }

        // Save to session for future redirects
        session()->put('last_school_slug', $school->school_slug);

        return response()->json([
            'valid' => true,
            'school_name' => $school->name,
            'school_slug' => $school->school_slug,
            'login_url' => route('school.login', ['school_slug' => $school->school_slug]),
        ]);
    }
}
