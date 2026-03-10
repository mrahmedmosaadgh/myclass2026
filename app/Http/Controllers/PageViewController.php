<?php

namespace App\Http\Controllers;

use App\Models\PageView;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PageViewController extends Controller
{
    /**
     * Store a new page view record
     */
    public function increment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'page_name' => 'required|string|max:255',
            'referrer' => 'nullable|url',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Create a new page view record
        $pageView = PageView::create([
            'page_name' => $request->page_name,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'referrer' => $request->referrer,
            'metadata' => [
                'headers' => [
                    'accept_language' => $request->header('accept-language'),
                ]
            ]
        ]);

        return response()->json(['success' => true, 'view_id' => $pageView->id]);
    }

    /**
     * Get total views for a specific page
     */
    public function getCount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'page_name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $count = PageView::where('page_name', $request->page_name)->count();

        return response()->json([
            'page_name' => $request->page_name,
            'view_count' => $count
        ]);
    }
}