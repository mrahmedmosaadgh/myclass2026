<?php

namespace App\Http\Controllers;

use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class SchoolBrandingController extends Controller
{
    /**
     * Display the school branding settings page
     */
    public function index(Request $request)
    {
        // Get user's school or all schools for super admin
        $user = $request->user();
        
        // For now, get all schools (can be filtered based on permissions later)
        $schools = School::select('id', 'name', 'name_ar', 'data')
            ->where('is_active', true)
            ->get()
            ->map(function ($school) {
                return [
                    'id' => $school->id,
                    'name' => $school->name,
                    'name_ar' => $school->name_ar,
                    'branding' => $school->branding,
                    'school_slug' => $school->school_slug,
                    'logo_url' => $school->logo_url,
                    'background_url' => $school->background_url,
                ];
            });

        return Inertia::render('Admin/SchoolBrandingSettings', [
            'schools' => $schools,
        ]);
    }

    /**
     * Update school branding settings
     */
    public function update(Request $request, School $school)
    {
        $validated = $request->validate([
            'school_name_en' => 'nullable|string|max:255',
            'school_name_ar' => 'nullable|string|max:255',
            'school_slug' => 'nullable|string|max:255|regex:/^[a-z0-9-]+$/',
            'colors.primary' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'colors.secondary' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'colors.accent' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'login_page_settings.show_particles' => 'nullable|boolean',
            'login_page_settings.animation_style' => 'nullable|string|in:fade,slide,zoom',
            'login_page_settings.card_style' => 'nullable|string|in:glassmorphism,solid,gradient',
        ]);

        // Update branding data
        $school->updateBranding($validated);

        return redirect()->back()->with('success', 'Branding updated successfully');
    }

    /**
     * Upload school logo
     */
    public function uploadLogo(Request $request, School $school)
    {
        $request->validate([
            'logo' => 'required|image|mimes:jpg,jpeg,png,svg|max:2048', // 2MB max
        ]);

        // Delete old logo if exists
        $currentBranding = $school->branding;
        if (!empty($currentBranding['logo_path'])) {
            Storage::disk('public')->delete($currentBranding['logo_path']);
        }

        // Store new logo
        $file = $request->file('logo');
        $filename = 'logo_' . time() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs(
            "school_branding/{$school->id}",
            $filename,
            'public'
        );

        // Update branding data
        $school->updateBranding([
            'logo_path' => $path,
        ]);

        return redirect()->back()->with('success', 'Logo uploaded successfully');
    }

    /**
     * Upload school background image
     */
    public function uploadBackground(Request $request, School $school)
    {
        $request->validate([
            'background' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120', // 5MB max
        ]);

        // Delete old background if exists
        $currentBranding = $school->branding;
        if (!empty($currentBranding['background_path'])) {
            Storage::disk('public')->delete($currentBranding['background_path']);
        }

        // Store new background
        $file = $request->file('background');
        $filename = 'background_' . time() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs(
            "school_branding/{$school->id}",
            $filename,
            'public'
        );

        // Update branding data
        $school->updateBranding([
            'background_path' => $path,
        ]);

        return redirect()->back()->with('success', 'Background uploaded successfully');
    }

    /**
     * Generate and return school login link
     */
    public function generateLoginLink(School $school)
    {
        $slug = $school->school_slug;
        $url = url("/login/{$slug}");

        return response()->json([
            'url' => $url,
            'slug' => $slug,
        ]);
    }

    /**
     * Get branding data for preview
     */
    public function preview(School $school)
    {
        return response()->json([
            'branding' => $school->branding,
            'logo_url' => $school->logo_url,
            'background_url' => $school->background_url,
            'school_slug' => $school->school_slug,
        ]);
    }
}
