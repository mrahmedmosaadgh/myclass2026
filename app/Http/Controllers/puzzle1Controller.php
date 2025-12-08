<?php

namespace App\Http\Controllers;

use App\Models\ScheduleTiming;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class puzzle1Controller extends Controller
{
    public function index()
    {
         return Inertia::render('my_class/teacher/schedule/timelinev2.3/connect_points/UserPuzzle', [
            // 'records' => $semesters,
 
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'school_id' => 'required|exists:schools,id',
            'options' => 'required|array',
            'options.timeSlots' => 'required|array',
            'options.defaultStartTime' => 'required|string',
            'options.defaultEndTime' => 'required|string',
            'timing' => 'required|array'
        ]);

        try {
            $scheduleTiming = ScheduleTiming::updateOrCreate(
                ['school_id' => $validated['school_id']],
                [
                    'options' => $validated['options'],
                    'timing' => $validated['timing']
                ]
            );

            return response()->json([
                'message' => 'Schedule timing saved successfully',
                'data' => $scheduleTiming
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to save schedule timing: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to save schedule timing',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show_data2(Request $request)
    {
         $school_id= $request->school_id;

        try {
        //   return  $timing = ScheduleTiming::all();
             $timing = ScheduleTiming::where('school_id', $school_id)->first();

            if (!$timing) {
                return response()->json([
                    'message' => 'No timing configuration found',
                    'data' => null
                ]);
            }

            return response()->json([
                'message' => 'Timing configuration retrieved successfully',
                'data' => [
                    'options' => $timing->options,
                    'timing' => $timing->timing
                ]
            ]);
        } catch (\Exception $e) {
             Log::error('Failed to retrieve timing configuration: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to retrieve timing configuration',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, ScheduleTiming $scheduleTiming)
    {
        $validator = Validator::make($request->all(), [
            'school_id' => 'sometimes|required|exists:schools,id',
            'options' => 'nullable|json',
            'timing' => 'nullable|json',
            'notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $scheduleTiming->update($request->all());
        return response()->json($scheduleTiming);
    }

    public function destroy(ScheduleTiming $scheduleTiming)
    {
        $scheduleTiming->delete();
        return response()->json(['message' => 'Schedule timing deleted successfully']);
    }
}
