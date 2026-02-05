<?php

namespace App\Http\Controllers;

use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class SitemapController extends Controller
{
    public function index()
    {
        $urls = [
            '/' => '2025-01-01', // Homepage
            '/register-school-admin' => '2025-01-01',
            // Add other static public pages here
        ];

        // Get active schools for dynamic routes
        try {
            // Note: Optimally this should be where('is_active', true)->get() if is_active is a column.
            // Based on SchoolLoginController, we'll fetch all and filter or just fetch active if column exists.
            // We'll trust the model has is_active scope or attribute.
            $schools = School::where('is_active', true)->get();
            
            foreach ($schools as $school) {
                // Check if school_slug exists
                $slug = $school->school_slug ?? ($school->slug ?? null);
                
                if ($slug) {
                    $urls['/login/' . $slug] = $school->updated_at ? $school->updated_at->format('Y-m-d') : date('Y-m-d');
                }
            }
        } catch (\Exception $e) {
            // If School model fails or strict mode, just ignore dynamic routes for now
            // Log::error($e->getMessage()); 
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        foreach ($urls as $path => $lastmod) {
            $xml .= '<url>';
            $xml .= '<loc>' . url($path) . '</loc>';
            $xml .= '<lastmod>' . $lastmod . '</lastmod>';
            $xml .= '<changefreq>weekly</changefreq>';
            $xml .= '<priority>' . ($path === '/' ? '1.0' : '0.8') . '</priority>';
            $xml .= '</url>';
        }

        $xml .= '</urlset>';

        return response($xml, 200)
            ->header('Content-Type', 'text/xml');
    }
}
