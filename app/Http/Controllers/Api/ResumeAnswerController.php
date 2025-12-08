<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ResumeAnswer;
use App\Models\ResumeQuestionComment;
use App\Models\ResumeAnswerRating;
use App\Models\ResumeAnswerLike;
use App\Models\ResumeCommentLike;
use App\Models\ResumeAnswerBookmark;
use App\Models\ResumeAnswerReport;
use Illuminate\Http\Request;

class ResumeAnswerController extends Controller
{
    public function index($questionId)
    {
        $answers = ResumeAnswer::with('user')
            ->withCount(['comments', 'likes', 'ratings'])
            ->where('question_id', $questionId)
            ->orderBy('created_at', 'desc')
            ->get();

        $userId = request()->user()->id ?? null;

        // Transform answers to include user interaction data
        $transformedAnswers = $answers->map(function ($answer) use ($userId) {
            $answerData = $answer->toArray();

            // Add user interaction data
            $answerData['user_rating'] = $userId ? $answer->getUserRating($userId) : null;
            $answerData['user_liked'] = $userId ? $answer->isLikedByUser($userId) : false;

            return $answerData;
        });

        return response()->json($transformedAnswers);
    }

    public function store(Request $request, $questionId)
    {
        $data = $request->validate([
            'answer_text' => 'required|string',
            'media_links' => 'nullable|array',
            'attachments' => 'nullable|array',
            'status' => 'nullable|in:draft,published,review,archived',
            'notes' => 'nullable|string',
            'is_public' => 'boolean',
        ]);

        $data['user_id'] = $request->user()->id;
        $data['question_id'] = $questionId;
        $data['status'] = $data['status'] ?? 'draft';

        // Handle file uploads if needed
        if ($request->hasFile('files')) {
            $uploadedFiles = [];
            foreach ($request->file('files') as $file) {
                $path = $file->store('resume_answers', 'public');
                $uploadedFiles[] = [
                    'name' => $file->getClientOriginalName(),
                    'type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                    'path' => $path
                ];
            }
            $data['attachments'] = $uploadedFiles;
        }

        $answer = ResumeAnswer::create($data);

        return response()->json($answer->load('user'), 201);
    }

    public function show($id)
    {
        $answer = ResumeAnswer::with(['user', 'question'])
            ->withCount(['comments', 'likes', 'ratings'])
            ->findOrFail($id);

        $userId = request()->user()->id ?? null;
        $answerData = $answer->toArray();

        // Add user interaction data
        $answerData['user_rating'] = $userId ? $answer->getUserRating($userId) : null;
        $answerData['user_liked'] = $userId ? $answer->isLikedByUser($userId) : false;

        return response()->json($answerData);
    }

    public function update(Request $request, $id)
    {
        $answer = ResumeAnswer::findOrFail($id);

        // Check if user can edit this answer (owner or admin)
        if ($answer->user_id !== $request->user()->id && !$request->user()->hasRole('admin')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $data = $request->validate([
            'answer_text' => 'sometimes|string',
            'media_links' => 'nullable|array',
            'attachments' => 'nullable|array',
            'status' => 'nullable|in:draft,published,review,archived',
            'notes' => 'nullable|string',
            'is_public' => 'sometimes|boolean',
        ]);

        // Handle file uploads if needed
        if ($request->hasFile('files')) {
            $uploadedFiles = $answer->attachments ?? [];
            foreach ($request->file('files') as $file) {
                $path = $file->store('resume_answers', 'public');
                $uploadedFiles[] = [
                    'name' => $file->getClientOriginalName(),
                    'type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                    'path' => $path
                ];
            }
            $data['attachments'] = $uploadedFiles;
        }

        $answer->update($data);

        return response()->json($answer->load('user'));
    }

    public function destroy($id)
    {
        $answer = ResumeAnswer::findOrFail($id);

        // Check if user can delete this answer (owner or admin)
        if ($answer->user_id !== request()->user()->id && !request()->user()->hasRole('admin')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $answer->delete();

        return response()->json(['success' => true]);
    }

    public function uploadMedia(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240', // 10MB max
        ]);

        $file = $request->file('file');
        $path = $file->store('resume_media', 'public');

        return response()->json([
            'success' => true,
            'path' => $path,
            'url' => asset('storage/' . $path),
            'name' => $file->getClientOriginalName(),
            'type' => $file->getMimeType(),
            'size' => $file->getSize()
        ]);
    }

    public function uploadVoiceNote(Request $request)
    {
        $request->validate([
            'voice_note' => 'required|file|mimes:wav,mp3,ogg,webm|max:5120', // 5MB max
            'answer_id' => 'nullable|exists:resume_answers,id'
        ]);

        $file = $request->file('voice_note');
        $path = $file->store('voice_notes', 'public');

        $response = [
            'success' => true,
            'path' => $path,
            'url' => asset('storage/' . $path),
            'name' => $file->getClientOriginalName(),
            'type' => $file->getMimeType(),
            'size' => $file->getSize()
        ];

        // If answer_id is provided, update the answer with voice note
        if ($request->answer_id) {
            $answer = ResumeAnswer::findOrFail($request->answer_id);
            $answer->update(['voice_note_path' => $path]);
            $response['answer_updated'] = true;
        }

        return response()->json($response);
    }

    public function uploadAttachment(Request $request)
    {
        $request->validate([
            'attachment' => 'required|file|max:10240', // 10MB max
            'answer_id' => 'nullable|exists:resume_answers,id'
        ]);

        $file = $request->file('attachment');
        $path = $file->store('attachments', 'public');

        $attachmentData = [
            'name' => $file->getClientOriginalName(),
            'type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'path' => $path,
            'url' => asset('storage/' . $path)
        ];

        $response = [
            'success' => true,
            'attachment' => $attachmentData
        ];

        // If answer_id is provided, add to answer attachments
        if ($request->answer_id) {
            $answer = ResumeAnswer::findOrFail($request->answer_id);
            $attachments = $answer->attachments ?? [];
            $attachments[] = $attachmentData;
            $answer->update(['attachments' => $attachments]);
            $response['answer_updated'] = true;
        }

        return response()->json($response);
    }

    // ===== COMMENTS METHODS =====

    public function getComments($answerId)
    {
        $answer = ResumeAnswer::findOrFail($answerId);
        $userId = request()->user()->id ?? null;

        $comments = ResumeQuestionComment::getCommentsWithReplies($answerId);

        // Transform comments to include user interaction data
        $transformedComments = $comments->map(function ($comment) use ($userId) {
            return [
                'id' => $comment->id,
                'text' => $comment->comment,
                'user_id' => $comment->user_id,
                'user' => [
                    'id' => $comment->user->id,
                    'name' => $comment->user->name,
                    'avatar' => $comment->user->avatar ?? null
                ],
                'created_at' => $comment->created_at->toISOString(),
                'updated_at' => $comment->updated_at->toISOString(),
                'likes_count' => $comment->likes()->count(),
                'user_liked' => $userId ? $comment->isLikedByUser($userId) : false,
                'is_edited' => $comment->created_at != $comment->updated_at,
                'media_type' => $comment->media_type,
                'media_path' => $comment->media_path,
                'replies' => $comment->replies->map(function ($reply) use ($userId) {
                    return [
                        'id' => $reply->id,
                        'text' => $reply->comment,
                        'user_id' => $reply->user_id,
                        'user' => [
                            'id' => $reply->user->id,
                            'name' => $reply->user->name,
                            'avatar' => $reply->user->avatar ?? null
                        ],
                        'created_at' => $reply->created_at->toISOString(),
                        'updated_at' => $reply->updated_at->toISOString(),
                        'likes_count' => $reply->likes()->count(),
                        'user_liked' => $userId ? $reply->isLikedByUser($userId) : false,
                        'is_edited' => $reply->created_at != $reply->updated_at,
                        'parent_comment_id' => $reply->parent_id
                    ];
                })
            ];
        });

        return response()->json($transformedComments);
    }

    public function addComment(Request $request, $answerId)
    {
        $request->validate([
            'text' => 'required|string|max:1000',
            'parent_comment_id' => 'nullable|exists:resume_question_comments,id'
        ]);

        $answer = ResumeAnswer::findOrFail($answerId);

        $comment = ResumeQuestionComment::create([
            'user_id' => $request->user()->id,
            'question_id' => $answer->question_id,
            'answer_id' => $answerId,
            'comment' => $request->text,
            'parent_id' => $request->parent_comment_id,
            'is_public' => true
        ]);

        // Update answer comments count
        $answer->updateCommentsCount();

        $comment->load('user');

        return response()->json([
            'id' => $comment->id,
            'text' => $comment->comment,
            'user_id' => $comment->user_id,
            'user' => [
                'id' => $comment->user->id,
                'name' => $comment->user->name,
                'avatar' => $comment->user->avatar ?? null
            ],
            'created_at' => $comment->created_at->toISOString(),
            'updated_at' => $comment->updated_at->toISOString(),
            'likes_count' => 0,
            'user_liked' => false,
            'is_edited' => false,
            'replies' => []
        ], 201);
    }

    public function updateComment(Request $request, $commentId)
    {
        $request->validate([
            'text' => 'required|string|max:1000'
        ]);

        $comment = ResumeQuestionComment::findOrFail($commentId);

        // Check if user can edit this comment
        if ($comment->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $comment->update([
            'comment' => $request->text
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Comment updated successfully',
            'comment' => $comment
        ]);
    }

    public function deleteComment($commentId)
    {
        $comment = ResumeQuestionComment::findOrFail($commentId);

        // Check if user can delete this comment
        if ($comment->user_id !== request()->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Update answer comments count
        if ($comment->answer) {
            $comment->answer->updateCommentsCount();
        }

        $comment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Comment deleted successfully'
        ]);
    }

    public function toggleCommentLike($commentId)
    {
        $comment = ResumeQuestionComment::findOrFail($commentId);
        $userId = request()->user()->id;

        $liked = ResumeCommentLike::toggleLike($userId, $commentId);
        $likesCount = ResumeCommentLike::getLikeCount($commentId);

        return response()->json([
            'success' => true,
            'liked' => $liked,
            'likes_count' => $likesCount
        ]);
    }

    // ===== RATINGS METHODS =====

    public function rateAnswer(Request $request, $answerId)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'review_comment' => 'nullable|string|max:500'
        ]);

        $answer = ResumeAnswer::findOrFail($answerId);
        $userId = $request->user()->id;

        // Update or create rating
        $rating = ResumeAnswerRating::updateOrCreate(
            ['user_id' => $userId, 'answer_id' => $answerId],
            [
                'rating' => $request->rating,
                'review_comment' => $request->review_comment
            ]
        );

        // Update answer rating statistics
        $answer->updateRatingStats();
        $answer->refresh();

        return response()->json([
            'success' => true,
            'rating' => $rating->rating,
            'average_rating' => $answer->average_rating,
            'ratings_count' => $answer->ratings_count
        ]);
    }

    public function getAnswerRatings($answerId)
    {
        $answer = ResumeAnswer::findOrFail($answerId);
        $userId = request()->user()->id ?? null;

        $userRating = null;
        if ($userId) {
            $userRatingRecord = $answer->ratings()->where('user_id', $userId)->first();
            $userRating = $userRatingRecord ? $userRatingRecord->rating : null;
        }

        return response()->json([
            'average_rating' => $answer->average_rating,
            'ratings_count' => $answer->ratings_count,
            'user_rating' => $userRating,
            'rating_breakdown' => ResumeAnswerRating::getRatingBreakdown($answerId)
        ]);
    }

    public function toggleAnswerLike($answerId)
    {
        $answer = ResumeAnswer::findOrFail($answerId);
        $userId = request()->user()->id;

        $liked = ResumeAnswerLike::toggleLike($userId, $answerId);

        // Update answer likes count
        $answer->updateLikesCount();
        $answer->refresh();

        return response()->json([
            'success' => true,
            'liked' => $liked,
            'likes_count' => $answer->likes_count
        ]);
    }

    // ===== BOOKMARKS METHODS =====

    public function toggleBookmark(Request $request, $answerId)
    {
        $request->validate([
            'bookmark_type' => 'nullable|in:favorite,important,reference',
            'notes' => 'nullable|string|max:500'
        ]);

        $answer = ResumeAnswer::findOrFail($answerId);
        $userId = $request->user()->id;
        $type = $request->bookmark_type ?? 'favorite';

        $bookmarked = ResumeAnswerBookmark::toggleBookmark(
            $userId,
            $answerId,
            $type,
            $request->notes
        );

        return response()->json([
            'success' => true,
            'bookmarked' => $bookmarked,
            'bookmark_type' => $type
        ]);
    }

    public function getUserBookmarks(Request $request)
    {
        $userId = $request->user()->id;
        $type = $request->query('type');

        $bookmarks = ResumeAnswerBookmark::getUserBookmarks($userId, $type);

        return response()->json($bookmarks);
    }

    // ===== REPORTS METHODS =====

    public function reportAnswer(Request $request, $answerId)
    {
        $request->validate([
            'report_type' => 'required|in:spam,inappropriate,copyright,harassment,misinformation,other',
            'reason' => 'required|string|max:1000'
        ]);

        $answer = ResumeAnswer::findOrFail($answerId);
        $userId = $request->user()->id;

        // Check if user has already reported this answer
        $existingReport = ResumeAnswerReport::where('user_id', $userId)
            ->where('answer_id', $answerId)
            ->where('status', 'pending')
            ->first();

        if ($existingReport) {
            return response()->json([
                'error' => 'You have already reported this answer'
            ], 422);
        }

        $report = ResumeAnswerReport::create([
            'user_id' => $userId,
            'answer_id' => $answerId,
            'report_type' => $request->report_type,
            'reason' => $request->reason
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Report submitted successfully',
            'report_id' => $report->id
        ], 201);
    }

    // ===== ADMIN METHODS =====

    public function getReports(Request $request)
    {
        // Only allow admins to view reports
        if (!$request->user()->hasRole('admin')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $status = $request->query('status', 'pending');
        $reports = ResumeAnswerReport::with(['user', 'answer', 'reviewer'])
            ->where('status', $status)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($reports);
    }

    public function updateReportStatus(Request $request, $reportId)
    {
        // Only allow admins to update report status
        if (!$request->user()->hasRole('admin')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'status' => 'required|in:reviewed,resolved,dismissed',
            'admin_notes' => 'nullable|string|max:1000'
        ]);

        $report = ResumeAnswerReport::findOrFail($reportId);

        switch ($request->status) {
            case 'reviewed':
                $report->markAsReviewed($request->user()->id, $request->admin_notes);
                break;
            case 'resolved':
                $report->markAsResolved($request->user()->id, $request->admin_notes);
                break;
            case 'dismissed':
                $report->markAsDismissed($request->user()->id, $request->admin_notes);
                break;
        }

        return response()->json([
            'success' => true,
            'message' => 'Report status updated successfully'
        ]);
    }
}
