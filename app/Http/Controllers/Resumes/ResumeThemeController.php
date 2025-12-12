<?php

namespace App\Http\Controllers\Resumes;

use App\Http\Controllers\Controller;
use App\Models\Resumes\ResumeTheme;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ResumeThemeController extends Controller
{
    // List all themes (optionally filter by public/private, user, etc.)
    public function index(Request $request)
    {
        $query = ResumeTheme::query();
        if ($request->has('public')) {
            $query->where('is_public', $request->boolean('public'));
        }
        if ($request->has('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }
        return $query->latest()->get();
    }

    // Store a new theme
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'style' => 'required|array',
            'is_public' => 'boolean',
        ]);
        $data['user_id'] = Auth::id();
        $theme = ResumeTheme::create($data);
        return response()->json($theme, 201);
    }

    // Show a single theme
    public function show(ResumeTheme $resumeTheme)
    {
        return $resumeTheme;
    }

    // Update a theme
    public function update(Request $request, ResumeTheme $resumeTheme)
    {
        $this->authorize('update', $resumeTheme);
        $data = $request->validate([
            'name' => 'string|max:255',
            'style' => 'array',
            'is_public' => 'boolean',
        ]);
        $resumeTheme->update($data);
        return $resumeTheme;
    }

    // Delete a theme
    public function destroy(ResumeTheme $resumeTheme)
    {
        $this->authorize('delete', $resumeTheme);
        $resumeTheme->delete();
        return response()->noContent();
    }
}
