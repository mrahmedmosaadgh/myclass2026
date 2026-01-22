<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class SchoolLoginController extends Controller
{
    /**
     * Display the school-branded login page
     */
    public function show($slug)
    {
        // Find school by slug
        $school = School::where('is_active', true)
            ->get()
            ->first(function ($school) use ($slug) {
                return $school->school_slug === $slug;
            });

        if (!$school) {
            abort(404, 'School not found');
        }

        return Inertia::render('Auth/SchoolLogin', [
            'schoolSlug' => $slug,
            'branding' => [
                'school_name_en' => $school->branding['school_name_en'] ?? $school->name,
                'school_name_ar' => $school->branding['school_name_ar'] ?? $school->name_ar,
                'logo_url' => $school->logo_url,
                'background_url' => $school->background_url,
                'colors' => $school->branding['colors'] ?? [],
                'login_page_settings' => $school->branding['login_page_settings'] ?? [],
            ],
        ]);
    }

    /**
     * Get school branding data (API endpoint)
     */
    public function getBranding($slug)
    {
        $school = School::where('is_active', true)
            ->get()
            ->first(function ($school) use ($slug) {
                return $school->school_slug === $slug;
            });

        if (!$school) {
            return response()->json(['error' => 'School not found'], 404);
        }

        return response()->json([
            'school_name_en' => $school->branding['school_name_en'] ?? $school->name,
            'school_name_ar' => $school->branding['school_name_ar'] ?? $school->name_ar,
            'logo_url' => $school->logo_url,
            'background_url' => $school->background_url,
            'colors' => $school->branding['colors'] ?? [],
            'login_page_settings' => $school->branding['login_page_settings'] ?? [],
        ]);
    }

    /**
     * Authenticate user for school-specific login
     */
    public function authenticate(Request $request, $slug)
    {
        // Find school by slug
        $school = School::where('is_active', true)
            ->get()
            ->first(function ($school) use ($slug) {
                return $school->school_slug === $slug;
            });

        if (!$school) {
            throw ValidationException::withMessages([
                'email' => ['School not found.'],
            ]);
        }

        // Validate credentials
        $request->validate([
            'email' => 'required|string',
            'password' => 'required|string',
        ]);

        // Find user by email or username
        $user = User::where('email', $request->email)
            ->orWhere('name', $request->email)
            ->first();

        // Check if user exists and password is correct
        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Check if user is active
        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Your account has been deactivated.'],
            ]);
        }

        // Verify user belongs to this school
        $userSchoolId = $user->schoolId();
        
        if ($userSchoolId !== $school->id) {
            throw ValidationException::withMessages([
                'email' => ['You do not have access to this school.'],
            ]);
        }

        // Log the user in
        Auth::login($user, $request->boolean('remember'));

        $request->session()->regenerate();

        // Save school slug to session for future redirects
        $request->session()->put('last_school_slug', $slug);

        // Update last login
        $user->last_login = now();
        $user->save();

        // Determine redirect based on role
        $redirectUrl = $this->getRedirectUrlByRole($user);

        return redirect()->intended($redirectUrl);
    }

    /**
     * Get redirect URL based on user role
     */
    protected function getRedirectUrlByRole(User $user)
    {
        // Check role from user model
        $role = $user->role;

        return match ($role) {
            'admin', 'hr_admin' => route('dashboard'),
            'teacher' => route('dashboard'),
            'student' => route('dashboard'),
            default => route('dashboard'),
        };
    }
}
