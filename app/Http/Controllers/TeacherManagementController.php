<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TeacherManagementController extends Controller
{
    public function index()
    {
        $teachers = Teacher::with(['school', 'user'])
            ->orderBy('name')
            ->paginate(10);

        return response()->json($teachers);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            // 't_id' => 'required|unique:teachers,t_id',
            'school_id' => 'required|exists:schools,id',
            'schools_number' => 'integer|min:1',
            'school_extra_ids' => 'nullable|json',
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'name_cute' => 'nullable|string|max:255',
            'national_id' => 'nullable|string|unique:teachers,national_id',
            'email' => 'required|email|unique:users,email',
            'phone_number' => 'nullable|string|unique:teachers,phone_number',
            'whatsapp_number' => 'nullable|string|unique:teachers,whatsapp_number',
            'gender' => 'nullable|string|in:male,female',
            // 'date_of_birth' => 'nullable|date',
            'nationality' => 'nullable|string',
            'address' => 'nullable|string',
            'order_1' => 'nullable|integer',
            'order_2' => 'nullable|integer',
            'notes' => 'nullable|string',
            'data' => 'nullable|json'
        ]);

        try {
            DB::beginTransaction();
            $teacher = Teacher::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                // other fields...
            ]);
            // Create user first
            // $user = User::create([
            //     'name' => $validated['name'],
            //     // 'email' => $validated['email'],
            //     'password' => Hash::make('12345678') // Generate random password
            //     // 'password' => Hash::make(Str::random(10)) // Generate random password
            // ]);

            // // Create teacher
            // $teacher = Teacher::create([
            //     ...$validated,
            //     'user_id' => $user->id
            // ]);

            DB::commit();

            return response()->json([
                'message' => 'Teacher created successfully',
                'teacher' => $teacher->load('school', 'user')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error creating teacher', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, Teacher $teacher)
    {
        $validated = $request->validate([
            't_id' => ['required', Rule::unique('teachers')->ignore($teacher->id)],
            'school_id' => 'required|exists:schools,id',
            'schools_number' => 'integer|min:1',
            'school_extra_ids' => 'nullable|json',
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'name_cute' => 'nullable|string|max:255',
            'national_id' => ['nullable', 'string', Rule::unique('teachers')->ignore($teacher->id)],
            'email' => ['required', 'email', Rule::unique('users')->ignore($teacher->user_id)],
            'phone_number' => ['nullable', 'string', Rule::unique('teachers')->ignore($teacher->id)],
            'whatsapp_number' => ['nullable', 'string', Rule::unique('teachers')->ignore($teacher->id)],
            'gender' => 'nullable|string|in:male,female',
            'date_of_birth' => 'nullable|date',
            'nationality' => 'nullable|string',
            'address' => 'nullable|string',
            'order_1' => 'nullable|integer',
            'order_2' => 'nullable|integer',
            'notes' => 'nullable|string',
            'data' => 'nullable|json'
        ]);

        try {
            DB::beginTransaction();

            // Update user
            $teacher->user->update([
                'name' => $validated['name'],
                'email' => $validated['email']
            ]);

            // Update teacher
            $teacher->update($validated);

            DB::commit();

            return response()->json([
                'message' => 'Teacher updated successfully',
                'teacher' => $teacher->load('school', 'user')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error updating teacher', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy(Teacher $teacher)
    {
        try {
            DB::beginTransaction();

            // Delete user
            $teacher->user->delete();
            // Delete teacher
            $teacher->delete();

            DB::commit();

            return response()->json(['message' => 'Teacher deleted successfully']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error deleting teacher', 'error' => $e->getMessage()], 500);
        }
    }

    public function export()
    {
        $teachers = Teacher::with(['school', 'user'])->get();

        return response()->json($teachers);
    }
}
