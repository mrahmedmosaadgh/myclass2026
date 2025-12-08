<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImageUploadController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:2048', // 2MB max
        ]);

        $path = $request->file('image')->store('public/uploads');
        
        return response()->json([
            'url' => Storage::url($path)
        ]);
    }
}