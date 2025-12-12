<?php
namespace App\Http\Controllers\QudratQuantitative;

use App\Http\Controllers\Controller;
use App\Models\QudratQuantitative\QdratQuestionDifficulty;
use Illuminate\Http\Request;

class QdratQuestionDifficultyController extends Controller
{
    public function index()
    {
        return QdratQuestionDifficulty::with(['questions'])->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string',
            'order' => 'nullable|integer',
        ]);

        $data['created_by'] = auth()->id();

        return QdratQuestionDifficulty::create($data);
    }

    public function update(Request $request, QdratQuestionDifficulty $qdratQuestionDifficulty)
    {
        $data = $request->validate([
            'name' => 'sometimes|string',
            'description' => 'nullable|string',
            'order' => 'nullable|integer',
        ]);

        $qdratQuestionDifficulty->update($data);
        return $qdratQuestionDifficulty;
    }

    public function destroy(QdratQuestionDifficulty $qdratQuestionDifficulty)
    {
        $qdratQuestionDifficulty->delete();
        return response()->noContent();
    }
}
