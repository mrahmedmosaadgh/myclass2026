<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;


class VideoController extends Controller
{


    public function store(Request $request)
{
    $request->validate([
        'video' => 'required|file|mimetypes:video/mp4,video/webm,video/ogg|max:102400'
    ]);

    if ($request->hasFile('video')) {
        $path = $request->file('video')->store('teacher/14', 'public');
        return response()->json(['path' => $path]);
    }

    return response()->json(['error' => 'No file uploaded'], 400);
}
}

