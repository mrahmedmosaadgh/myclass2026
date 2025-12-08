<?php

namespace App\Http\Controllers;
use App\Models\TreeStructure;
use Illuminate\Http\Request;

class TreeStructureController extends Controller
{
    public function fetch()
    {
        $tree = TreeStructure::first();
        if (!$tree) {
            return response()->json(['tree' => []]);
        }
        return response()->json(['tree' => $tree->tree_data]);
    }

    public function save(Request $request)
    {
        $request->validate([
            'tree' => 'required|array',
        ]);

        $tree = TreeStructure::firstOrCreate(['name' => 'LMS Main Tree']);
        $tree->tree_data = $request->tree;
        $tree->save();

        return response()->json(['message' => 'Tree saved successfully!']);
    }

}
