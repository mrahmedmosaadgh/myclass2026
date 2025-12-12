<?php
namespace App\Http\Controllers\QudratQuantitative;

use App\Http\Controllers\Controller;
use App\Models\QudratQuantitative\QdratQuestion;
use Illuminate\Http\Request;

class QdratQuestionController extends Controller
{
    public function index()
    {
        return QdratQuestion::with(['difficulty', 'skills'])->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'content' => 'required|string',
            'type' => 'nullable|string',
            'difficulty_level_id' => 'nullable|exists:qdrat_question_difficulties,id',
            'answer_text' => 'nullable|string',
            'options_json' => 'nullable|json',
        ]);

        $data['created_by'] = auth()->id();

        $question = QdratQuestion::create($data);

        if ($request->has('skills')) {
            $question->skills()->sync($request->input('skills'));
        }

        return $question;
    }

    public function update(Request $request, QdratQuestion $qdratQuestion)
    {
        $data = $request->validate([
            'content' => 'sometimes|string',
            'type' => 'nullable|string',
            'difficulty_level_id' => 'nullable|exists:qdrat_question_difficulties,id',
            'answer_text' => 'nullable|string',
            'options_json' => 'nullable|json',
        ]);

        $qdratQuestion->update($data);

        if ($request->has('skills')) {
            $qdratQuestion->skills()->sync($request->input('skills'));
        }

        return $qdratQuestion;
    }

    public function destroy(QdratQuestion $qdratQuestion)
    {
        $qdratQuestion->delete();
        return response()->noContent();
    }
}
