<?php

namespace App\Http\Controllers\Fg;

use App\Http\Controllers\Controller;
use App\Http\Requests\Fg\FgDomainRequest;
use App\Models\Fg\FgDomain;
use Illuminate\Http\Request;

class FgDomainController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $domains = FgDomain::where('user_id', $request->user()->id)
            ->orderBy('sort_order')
            ->get();
            
        return response()->json($domains);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(FgDomainRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;
        
        $domain = FgDomain::create($data);
        
        return response()->json($domain, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, FgDomain $domain)
    {
        if ($domain->user_id !== $request->user()->id) {
            abort(403);
        }
        
        return response()->json($domain);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(FgDomainRequest $request, FgDomain $domain)
    {
        if ($domain->user_id !== $request->user()->id) {
            abort(403);
        }
        
        // Simple version bump for conflict detection
        $data = $request->validated();
        $data['version'] = $domain->version + 1;
        
        $domain->update($data);
        
        return response()->json($domain);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, FgDomain $domain)
    {
        if ($domain->user_id !== $request->user()->id) {
            abort(403);
        }
        
        $domain->delete(); // Soft delete triggering FgDomain::booted
        
        return response()->json(null, 204);
    }
}
