<?php

namespace App\Http\Controllers\Api\Cr;

use App\Http\Controllers\Controller;
use App\Models\CrCategoryMapping;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class CrCategoryMappingsController extends Controller
{
    protected function getSchoolId(): ?int
    {
        return Auth::user()?->currentSchoolId();
    }

    protected function assertSchool(CrCategoryMapping $mapping): void
    {
        if ($mapping->school_id !== $this->getSchoolId()) {
            abort(403, 'Unauthorized');
        }
    }

    public function index(): JsonResponse
    {
        $schoolId = $this->getSchoolId();

        $mappings = CrCategoryMapping::where('school_id', $schoolId)
            ->orderBy('sort_order')
            ->get();

        return response()->json(['mappings' => $mappings]);
    }

    public function store(Request $request): JsonResponse
    {
        $schoolId = $this->getSchoolId();

        $validated = $request->validate([
            'label' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'in:numeric,text,json'],
            'max_value' => ['required', 'integer', 'min:0'],
            'passing_value' => ['nullable', 'integer', 'min:0'],
            'default_value' => ['required', 'integer', 'min:0'],
            'sort_order' => ['required', 'integer'],
            'active' => ['required', 'boolean'],
            'icon' => ['nullable', 'string', 'max:100'],
            'color' => ['nullable', 'string', 'max:100'],
            'grade_id' => ['nullable', 'exists:classrooms,id'],
            'subject_id' => ['nullable', 'exists:subjects,id'],
        ]);

        $key = Str::slug($validated['label'], '_');
        $baseKey = $key;
        $counter = 1;

        while (CrCategoryMapping::where('school_id', $schoolId)->where('key', $key)->exists()) {
            $key = $baseKey . '_' . $counter++;
        }

        $mapping = CrCategoryMapping::create(array_merge($validated, [
            'school_id' => $schoolId,
            'key' => $key,
        ]));

        return response()->json($mapping);
    }

    public function update(Request $request, CrCategoryMapping $mapping): JsonResponse
    {
        $this->assertSchool($mapping);

        $schoolId = $this->getSchoolId();

        $validated = $request->validate([
            'label' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'in:numeric,text,json'],
            'max_value' => ['required', 'integer', 'min:0'],
            'passing_value' => ['nullable', 'integer', 'min:0'],
            'default_value' => ['required', 'integer', 'min:0'],
            'sort_order' => ['required', 'integer'],
            'active' => ['required', 'boolean'],
            'icon' => ['nullable', 'string', 'max:100'],
            'color' => ['nullable', 'string', 'max:100'],
            'grade_id' => ['nullable', 'exists:classrooms,id'],
            'subject_id' => ['nullable', 'exists:subjects,id'],
        ]);

        $mapping->update($validated);

        return response()->json($mapping);
    }

    public function destroy(CrCategoryMapping $mapping): JsonResponse
    {
        $this->assertSchool($mapping);

        $mapping->delete();

        return response()->json(['success' => true]);
    }
}
