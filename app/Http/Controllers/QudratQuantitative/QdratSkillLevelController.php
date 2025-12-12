<?php
namespace App\Http\Controllers\QudratQuantitative;

use App\Http\Controllers\Controller;
use App\Models\QudratQuantitative\QdratSkillLevel;
use Illuminate\Http\Request;

class QdratSkillLevelController extends Controller
{
    public function index()
    {
        return QdratSkillLevel::with(['skills'])->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string',
            'order' => 'nullable|integer',
        ]);

        $data['created_by'] = auth()->id();

        return QdratSkillLevel::create($data);
    }

    public function update(Request $request, QdratSkillLevel $qdratSkillLevel)
    {
        $data = $request->validate([
            'name' => 'sometimes|string',
            'description' => 'nullable|string',
            'order' => 'nullable|integer',
        ]);

        $qdratSkillLevel->update($data);
        return $qdratSkillLevel;
    }

    public function destroy(QdratSkillLevel $qdratSkillLevel)
    {
        $qdratSkillLevel->delete();
        return response()->noContent();
    }
}
