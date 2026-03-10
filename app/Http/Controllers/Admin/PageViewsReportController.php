<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PageView;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PageViewsReportController extends Controller
{
    public function index()
    {
        // Get overall statistics
        $totalViews = PageView::count();
        $uniquePages = PageView::select('page_name')->distinct()->count();
        $viewsByPage = PageView::select('page_name', DB::raw('COUNT(*) as count'))
            ->groupBy('page_name')
            ->orderByDesc('count')
            ->get();
            
        $viewsByDate = PageView::select(
            DB::raw('DATE(created_at) as date'), 
            DB::raw('COUNT(*) as count')
        )
        ->groupBy(DB::raw('DATE(created_at)'))
        ->orderByDesc('date')
        ->limit(30) // Last 30 days
        ->get();
        
        $recentViews = PageView::with(['page_name'])
            ->latest()
            ->limit(20)
            ->get(['id', 'page_name', 'ip_address', 'created_at']);

        return inertia('Qudrat/Admin/PageViewsReport', [
            'totalViews' => $totalViews,
            'uniquePages' => $uniquePages,
            'viewsByPage' => $viewsByPage,
            'viewsByDate' => $viewsByDate,
            'recentViews' => $recentViews,
        ]);
    }
    
    public function export(Request $request)
    {
        // Export data as CSV
        $fileName = 'page-views-report-' . now()->format('Y-m-d') . '.csv';
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $columns = ['ID', 'Page Name', 'IP Address', 'User Agent', 'Referrer', 'Created At'];

        $callback = function() use ($columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            PageView::orderBy('created_at', 'desc')->chunk(1000, function($records) use ($file) {
                foreach ($records as $record) {
                    fputcsv($file, [
                        $record->id,
                        $record->page_name,
                        $record->ip_address,
                        $record->user_agent,
                        $record->referrer,
                        $record->created_at->format('Y-m-d H:i:s')
                    ]);
                }
            });

            fclose($file);
        };

        return response()->streamDownload($callback, $fileName, $headers);
    }
}