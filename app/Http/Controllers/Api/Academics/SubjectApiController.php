<?php

namespace App\Http\Controllers\Api\Academics;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SubjectApiController extends Controller
{
    public function index()
    {
        return response()->json([
            'data' => [
                ['id' => 1, 'name' => 'Mathematics', 'code' => 'MATH101'],
                ['id' => 2, 'name' => 'Science', 'code' => 'SCI101'],
            ]
        ]);
    }

    public function store(Request $request)
    {
        return response()->json(['message' => 'Subject created successfully'], 201);
    }
}
