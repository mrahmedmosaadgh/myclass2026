<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ResumeQuestionComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ResumeQuestionCommentController extends Controller
{
    public function index(Request $request)
    {
        $questionId = $request->query('question_id');
        $comments = ResumeQuestionComment::with(['user', 'replies.user'])
            ->where('question_id', $questionId)
            ->whereNull('parent_id')
            ->orderBy('created_at')
            ->get();
        return response()->json($comments);
    }

    public function store(Request $request, $questionId)
    {
        $data = $request->all();
        $data['user_id'] = $request->user()->id;
        $data['question_id'] = $questionId;

        $validator = Validator::make($data, [
            'comment' => 'nullable|string',
            'media_type' => 'nullable|in:audio,video,image',
            'media_path' => 'nullable|string',
            'is_public' => 'boolean',
            'parent_id' => 'nullable|exists:resume_question_comments,id',
            'answer_id' => 'nullable|exists:resume_answers,id',
            'file' => 'nullable|file|max:20480|mimetypes:audio/mpeg,audio/wav,audio/x-wav,audio/mp4,video/mp4,video/webm,video/quicktime',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $mediaType = $file->getMimeType();
            $type = str_contains($mediaType, 'audio') ? 'audio' : (str_contains($mediaType, 'video') ? 'video' : null);
            $path = $file->store('resume_comments', 'public');
            $data['media_type'] = $type;
            $data['media_path'] = $path;
        }

        $comment = ResumeQuestionComment::create($data);
        return response()->json($comment->load('user'), 201);
    }
}
