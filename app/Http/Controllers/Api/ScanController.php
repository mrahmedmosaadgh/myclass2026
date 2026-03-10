<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StudentAnswer;
use Illuminate\Support\Facades\Storage;

class ScanController extends Controller
{
    public function submit(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|string',
            'choice' => 'required|string|in:A,B,C,D',
            'photo' => 'nullable|string'
        ]);

        $photoPath = null;
        if (!empty($validated['photo'])) {
            // Save base64 image
            $image_parts = explode(";base64,", $validated['photo']);
            if (count($image_parts) == 2) {
                $image_type_aux = explode("image/", $image_parts[0]);
                $image_type = $image_type_aux[1];
                $image_base64 = base64_decode($image_parts[1]);
                $fileName = 'scans/' . $validated['student_id'] . '_' . time() . '.' . $image_type;
                
                Storage::disk('public')->put($fileName, $image_base64);
                $photoPath = '/storage/' . $fileName;
            }
        }

        $answer = StudentAnswer::create([
            'student_id' => $validated['student_id'],
            'choice' => $validated['choice'],
            'photo_path' => $photoPath
        ]);

        \App\Events\StudentAnswerScanned::dispatch($answer);

        return response()->json([
            'message' => 'Answer saved successfully', 
            'data' => $answer
        ]);
    }
}
