<?php
namespace App\Http\Controllers\QudratQuantitative;

use App\Http\Controllers\Controller;
use App\Models\QudratQuantitative\QdratLesson;
use Illuminate\Http\Request;

class QdratLessonController extends Controller
{
    public function index()
    {
        return QdratLesson::with(['category', 'skill'])->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string',
            'video_url' => 'nullable|string',
            'content' => 'nullable|string',
            'skill_id' => 'nullable|exists:qdrat_skills,id',
            'category_id' => 'nullable|exists:qdrat_lesson_categories,id',
            'type' => 'nullable|string',
            'order' => 'nullable|integer',
        ]);

        $data['created_by'] = auth()->id();

        return QdratLesson::create($data);
    }

    public function update(Request $request, QdratLesson $qdratLesson)
    {
        $data = $request->validate([
            'title' => 'sometimes|string',
            'video_url' => 'nullable|string',
            'content' => 'nullable|string',
            'skill_id' => 'nullable|exists:qdrat_skills,id',
            'category_id' => 'nullable|exists:qdrat_lesson_categories,id',
            'type' => 'nullable|string',
            'order' => 'nullable|integer',
        ]);

        $qdratLesson->update($data);
        return $qdratLesson;
    }

    public function destroy(QdratLesson $qdratLesson)
    {
        $qdratLesson->delete();
        return response()->noContent();
    }
}
