<?php
namespace App\Http\Controllers\QudratQuantitative;

use App\Http\Controllers\Controller;
use App\Models\QudratQuantitative\QdratLessonCategory;
use Illuminate\Http\Request;

class QdratLessonCategoryController extends Controller
{
    public function index()
    {
        return QdratLessonCategory::with(['lessons'])->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string',
            'order' => 'nullable|integer',
        ]);

        $data['created_by'] = auth()->id();

        return QdratLessonCategory::create($data);
    }

    public function update(Request $request, QdratLessonCategory $qdratLessonCategory)
    {
        $data = $request->validate([
            'name' => 'sometimes|string',
            'description' => 'nullable|string',
            'order' => 'nullable|integer',
        ]);

        $qdratLessonCategory->update($data);
        return $qdratLessonCategory;
    }

    public function destroy(QdratLessonCategory $qdratLessonCategory)
    {
        $qdratLessonCategory->delete();
        return response()->noContent();
    }
}
