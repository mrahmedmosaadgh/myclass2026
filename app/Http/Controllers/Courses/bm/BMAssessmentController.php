<?php

namespace App\Http\Controllers\Courses\bm;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Courses\bm\BMAssessment;
use App\Models\Courses\bm\BMAssessmentResponse;
use App\Services\Courses\bm\BMQuestionGenerator;
use App\Services\Courses\bm\BMAdaptiveEngine;
use App\Services\Courses\bm\BMScoringService;
use App\Services\Courses\bm\BMGapAnalyzer;
use App\Services\Courses\bm\BMBadgeService;

class BMAssessmentController extends Controller
{
    public function index()
    {
        return Inertia::render('Courses/bm/Student/AssessmentWelcome');
    }

    public function start(Request $request, BMQuestionGenerator $generator)
    {
        // 1. Create a new Assessment instance for user
        $assessment = BMAssessment::create([
            'user_id' => $request->user()->id,
            'type' => 'placement',
            'status' => 'in_progress',
        ]);

        // 2. Setup the session state
        $request->session()->put('active_bm_assessment_id', $assessment->id);
        $request->session()->put('bm_question_count', 1);
        $request->session()->put('bm_responses', []);

        // 3. Generate first basic question
        $question = $generator->generate('Addition', 1);

        // 4. Return Inertia view directly to start game loop
        $request->session()->put('current_question_id', $question['id']);
        $request->session()->put('current_correct_answer', $question['correct_answer']);
        $request->session()->put('current_question_html', $question['htmlText']);
        
        return Inertia::render('Courses/bm/Student/AssessmentQuestion', [
            'assessmentId' => $assessment->id,
            'question' => $question,
            'questionIndex' => 1,
            'totalQuestions' => 10
        ]);
    }

    public function submit(Request $request, BMQuestionGenerator $generator, BMAdaptiveEngine $engine)
    {
        $request->validate([
            'assessment_id' => 'required|integer',
            'question_id' => 'required|integer',
            'answer' => 'required|string',
            'time_taken_ms' => 'required|integer'
        ]);

        $assessmentId = $request->session()->get('active_bm_assessment_id') ?? $request->assessment_id;
        $questionCount = $request->session()->get('bm_question_count', 1);
        $responses = $request->session()->get('bm_responses', []);

        // Fetch question to check validity
        $questionModel = \App\Models\Courses\bm\BMQuestion::findOrFail($request->question_id);
        
        // Evaluate answer
        $correctAnswer = $request->session()->get('current_correct_answer');
        $questionHtml  = $request->session()->get('current_question_html');
        $isCorrect = (trim(strtolower($request->answer)) === trim(strtolower($correctAnswer)));
        
        // Save Response
        $responseModel = BMAssessmentResponse::create([
            'bm_assessment_id' => $assessmentId,
            'bm_question_id' => $request->question_id,
            'user_answer' => $request->answer,
            'correct_answer' => $correctAnswer,
            'is_correct' => $isCorrect,
            'time_taken_ms' => $request->time_taken_ms,
            'difficulty_level' => $questionModel->difficulty,
            'domain' => $questionModel->domain,
        ]);

        // Push to local array
        $responses[] = $responseModel->toArray();
        $request->session()->put('bm_responses', $responses);

        // Progress check
        $questionCount++;
        $request->session()->put('bm_question_count', $questionCount);

        if ($questionCount > 10) {
            // Assessment Complete
            BMAssessment::where('id', $assessmentId)->update(['status' => 'completed']);
            return redirect()->route('bm.assessment.results', ['id' => $assessmentId]);
        }

        // CAT Logic
        $nextDiff = $engine->getNextDifficulty($questionModel->difficulty, $isCorrect, $request->time_taken_ms);
        
        // Setup next domain (simplified rotating)
        $domains = ['Addition', 'Subtraction', 'Multiplication', 'Division', 'Fractions'];
        $nextDomain = $domains[($questionCount - 1) % 5];
        
        $nextQuestion = $generator->generate($nextDomain, $nextDiff);
        $request->session()->put('current_question_id', $nextQuestion['id']);
        $request->session()->put('current_correct_answer', $nextQuestion['correct_answer']);
        $request->session()->put('current_question_html', $nextQuestion['htmlText']);

        return Inertia::render('Courses/bm/Student/AssessmentQuestion', [
            'assessmentId' => $assessmentId,
            'question' => $nextQuestion,
            'questionIndex' => $questionCount,
            'totalQuestions' => 10,
            'feedback' => [
                'isCorrect' => $isCorrect,
                'correctAnswer' => (string)$correctAnswer,
                'userAnswer' => $request->answer,
                'questionHtml' => $questionHtml,
                'timeTaken' => $request->time_taken_ms,
            ]
        ]);
    }

    public function results(
        $id, 
        Request $request, 
        BMScoringService $scorer, 
        BMGapAnalyzer $analyzer,
        BMBadgeService $badgeService
    ) {
        $assessmentId = $id;

        // Ensure real data exists
        $responses = BMAssessmentResponse::where('bm_assessment_id', $assessmentId)->get()->toArray();
        
        if (empty($responses)) {
            // Handle edge case or dev testing where session responses exist
            $responses = $request->session()->get('bm_responses', []);
        }

        $calc = $scorer->calculateFinalScore($responses);
        $recommendations = $analyzer->getRecommendedLessons($calc['domains']);

        $newBadges = [];

        // Save Score and Award Badges
        if ($request->user()) {
            \App\Models\Courses\bm\BMScore::updateOrCreate(
                ['bm_assessment_id' => $assessmentId, 'user_id' => $request->user()->id],
                ['final_score' => $calc['total'], 'details_json' => $calc]
            );

            $newBadges = $badgeService->awardBadges($request->user()->id, $assessmentId, $responses, $calc);
        }

        // Clear session
        $request->session()->forget(['active_bm_assessment_id', 'bm_question_count', 'bm_responses', 'current_question_id', 'current_correct_answer']);

        return Inertia::render('Courses/bm/Student/AssessmentResults', [
            'score' => ['final_score' => $calc['total'], 'level' => $calc['level']],
            'domainScores' => $calc['domains'],
            'recommendations' => $recommendations,
            'newBadges' => $newBadges
        ]);
    }

    public function history(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return redirect()->route('login');
        }

        $scores = \App\Models\Courses\bm\BMScore::where('user_id', $user->id)
            ->orderBy('updated_at', 'desc')
            ->get();

        $badges = \App\Models\Courses\bm\BMBadge::where('user_id', $user->id)
            ->orderBy('earned_at', 'desc')
            ->get();

        return Inertia::render('Courses/bm/Student/AssessmentHistory', [
            'history' => $scores,
            'badges' => $badges
        ]);
    }
}
