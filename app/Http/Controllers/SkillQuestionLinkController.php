<?php

namespace App\Http\Controllers;

use App\Models\Skill;
use App\Models\QuQuestion;
use App\Models\SkillQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SkillQuestionLinkController extends Controller
{
    /**
     * Link questions to a skill with difficulty levels.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $skillId
     * @return \Illuminate\Http\Response
     */
    public function linkQuestions(Request $request, $skillId)
    {
        $skill = Skill::find($skillId);
        
        if (!$skill) {
            return response()->json([
                'success' => false,
                'message' => 'Skill not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'questions' => 'required|array',
            'questions.*.question_id' => 'required|exists:qu_questions,id',
            'questions.*.difficulty_level' => 'required|integer|min:1|max:10',
            'questions.*.explanation' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $questions = $request->input('questions');
        
        foreach ($questions as $questionData) {
            // Create or update the pivot record
            SkillQuestion::updateOrCreate(
                [
                    'skill_id' => $skillId,
                    'qu_question_id' => $questionData['question_id']
                ],
                [
                    'difficulty_level' => $questionData['difficulty_level'],
                    'explanation' => $questionData['explanation'] ?? null
                ]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Questions linked to skill successfully',
            'data' => $skill->refresh()->load('questions')
        ]);
    }

    /**
     * Remove a question from a skill.
     *
     * @param  int  $skillId
     * @param  int  $questionId
     * @return \Illuminate\Http\Response
     */
    public function unlinkQuestion($skillId, $questionId)
    {
        $skill = Skill::find($skillId);
        $question = QuQuestion::find($questionId);
        
        if (!$skill || !$question) {
            return response()->json([
                'success' => false,
                'message' => 'Skill or question not found'
            ], 404);
        }

        $link = SkillQuestion::where('skill_id', $skillId)
            ->where('qu_question_id', $questionId)
            ->first();

        if ($link) {
            $link->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Question unlinked from skill successfully'
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Link not found'
            ], 404);
        }
    }

    /**
     * Get all questions linked to a skill.
     *
     * @param  int  $skillId
     * @return \Illuminate\Http\Response
     */
    public function getLinkedQuestions($skillId)
    {
        $skill = Skill::with(['questions' => function($q) {
            $q->withPivot(['difficulty_level', 'explanation']);
        }])->find($skillId);

        if (!$skill) {
            return response()->json([
                'success' => false,
                'message' => 'Skill not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $skill->questions
        ]);
    }
}