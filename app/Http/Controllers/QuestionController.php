<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\QuestionType;
use App\Services\QuestionImportService;
use App\Http\Requests\StoreQuestionRequest;
use App\Http\Requests\UpdateQuestionRequest;
use App\Http\Requests\ImportQuestionsRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class QuestionController extends Controller
{
    /**
     * Get a paginated list of questions with filters.
     * 
     * Supports filtering by question type, grade level, subject, topic,
     * difficulty, Bloom level, status, and author. Returns questions with
     * their options and related metadata.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Validate the request
            $validated = $request->validate([
                'question_type_id' => 'sometimes|integer|exists:question_types,id',
                'grade_id' => 'sometimes|integer|exists:grades,id',
                'subject_id' => 'sometimes|integer|exists:subjects,id',
                'topic_id' => 'sometimes|integer|exists:topics,id',
                'difficulty_level' => 'sometimes|integer|min:1|max:5',
                'bloom_level' => 'sometimes|integer|min:1|max:6',
                'status' => 'sometimes|string|in:draft,active,archived,review',
                'author_id' => 'sometimes|integer|exists:users,id',
                'search' => 'sometimes|string|max:255',
                'per_page' => 'sometimes|integer|min:1|max:100',
                'page' => 'sometimes|integer|min:1',
            ]);

            // Build the query
            $query = Question::with([
                'questionType',
                'options' => function ($query) {
                    $query->orderBy('order_index');
                },
                'grade:id,name',
                'subject:id,name',
                'topic:id,name',
                'author:id,name',
            ]);

            // Apply filters
            if (isset($validated['question_type_id'])) {
                $query->byType($validated['question_type_id']);
            }

            if (isset($validated['grade_id'])) {
                $query->byGrade($validated['grade_id']);
            }

            if (isset($validated['subject_id'])) {
                $query->bySubject($validated['subject_id']);
            }

            if (isset($validated['topic_id'])) {
                $query->where('topic_id', $validated['topic_id']);
            }

            if (isset($validated['difficulty_level'])) {
                $query->where('difficulty_level', $validated['difficulty_level']);
            }

            if (isset($validated['bloom_level'])) {
                $query->where('bloom_level', $validated['bloom_level']);
            }

            if (isset($validated['status'])) {
                $query->byStatus($validated['status']);
            }

            if (isset($validated['author_id'])) {
                $query->where('author_id', $validated['author_id']);
            }

            // Search in question text
            if (isset($validated['search'])) {
                $query->where('question_text', 'like', '%' . $validated['search'] . '%');
            }

            // Paginate results
            $perPage = $validated['per_page'] ?? 20;
            $questions = $query->paginate($perPage);

            Log::info('Questions retrieved', [
                'count' => $questions->count(),
                'filters' => $validated,
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'current_page' => $questions->currentPage(),
                    'per_page' => $questions->perPage(),
                    'total' => $questions->total(),
                    'last_page' => $questions->lastPage(),
                    'questions' => $questions->items(),
                ],
            ]);

        } catch (ValidationException $e) {
            Log::warning('Question index validation failed', [
                'errors' => $e->errors(),
            ]);

            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'Invalid request parameters',
                    'details' => $e->errors(),
                    'timestamp' => now()->toIso8601String(),
                ],
            ], 422);

        } catch (\Exception $e) {
            Log::error('Question index failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'QUESTION_INDEX_ERROR',
                    'message' => 'Failed to retrieve questions',
                    'details' => config('app.debug') ? $e->getMessage() : null,
                    'timestamp' => now()->toIso8601String(),
                ],
            ], 500);
        }
    }

    /**
     * Create a new question with options.
     * 
     * Creates a question record along with its options (if applicable).
     * Validates that the question type supports options when options are provided.
     * Requires appropriate authorization.
     * 
     * @param StoreQuestionRequest $request
     * @return JsonResponse
     */
    public function store(StoreQuestionRequest $request): JsonResponse
    {
        try {
            // Get validated data from Form Request
            $validated = $request->validated();

            // The following validation is now handled by StoreQuestionRequest
            /* $validated = $request->validate([
                'question_type_id' => 'required|integer|exists:question_types,id',
                'question_text' => 'required|string|max:5000',
                'grade_level_id' => 'required|integer|exists:grades,id',
                'subject_id' => 'required|integer|exists:subjects,id',
                'topic_id' => 'nullable|integer|exists:topics,id',
                'bloom_level' => 'nullable|integer|min:1|max:6',
                'difficulty_level' => 'nullable|integer|min:1|max:5',
                'estimated_time_sec' => 'nullable|integer|min:0',
                'status' => 'required|string|in:draft,active,archived,review',
                'hints' => 'nullable|array',
                'hints.*' => 'string|max:1000',
                'explanation' => 'nullable|array',
                'explanation.text' => 'nullable|string|max:5000',
                'explanation.revealed_after_attempt' => 'nullable|boolean',
                'options' => 'nullable|array|min:2',
                'options.*.option_key' => 'required|string|max:10',
                'options.*.option_text' => 'required|string|max:1000',
                'options.*.is_correct' => 'required|boolean',
                'options.*.distractor_strength' => 'nullable|numeric|min:0|max:1',
                'options.*.order_index' => 'required|integer|min:0',
            ]); */

            $user = $request->user();

            // Authorization and validation are now handled by StoreQuestionRequest

            // Create question and options in a transaction
            $question = DB::transaction(function () use ($validated, $user) {
                // Create the question
                $questionData = [
                    'question_type_id' => $validated['question_type_id'],
                    'question_text' => $validated['question_text'],
                    'grade_id' => $validated['grade_id'],
                    'subject_id' => $validated['subject_id'],
                    'topic_id' => $validated['topic_id'] ?? null,
                    'bloom_level' => $validated['bloom_level'] ?? null,
                    'difficulty_level' => $validated['difficulty_level'] ?? null,
                    'estimated_time_sec' => $validated['estimated_time_sec'] ?? null,
                    'author_id' => $user->id,
                    'status' => $validated['status'],
                    'hints' => $validated['hints'] ?? null,
                    'explanation' => $validated['explanation'] ?? null,
                    'usage_count' => 0,
                ];

                $question = Question::create($questionData);

                // Create options if provided
                if (isset($validated['options'])) {
                    foreach ($validated['options'] as $optionData) {
                        QuestionOption::create([
                            'question_id' => $question->id,
                            'option_key' => $optionData['option_key'],
                            'option_text' => $optionData['option_text'],
                            'is_correct' => $optionData['is_correct'],
                            'distractor_strength' => $optionData['distractor_strength'] ?? null,
                            'order_index' => $optionData['order_index'],
                        ]);
                    }
                }

                return $question;
            });

            // Load relationships for response
            $question->load(['questionType', 'options', 'grade', 'subject', 'topic', 'author']);

            Log::info('Question created', [
                'question_id' => $question->id,
                'author_id' => $user->id,
            ]);

            return response()->json([
                'success' => true,
                'data' => $question,
            ], 201);

        } catch (ValidationException $e) {
            Log::warning('Question creation validation failed', [
                'errors' => $e->errors(),
            ]);

            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'Invalid request parameters',
                    'details' => $e->errors(),
                    'timestamp' => now()->toIso8601String(),
                ],
            ], 422);

        } catch (\Exception $e) {
            Log::error('Question creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'QUESTION_CREATION_ERROR',
                    'message' => 'Failed to create question',
                    'details' => config('app.debug') ? $e->getMessage() : null,
                    'timestamp' => now()->toIso8601String(),
                ],
            ], 500);
        }
    }

    /**
     * Get a single question with all its details.
     * 
     * Returns a question with its options, question type, and curriculum alignment.
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $question = Question::with([
                'questionType',
                'options' => function ($query) {
                    $query->orderBy('order_index');
                },
                'grade:id,name',
                'subject:id,name',
                'topic:id,name',
                'author:id,name',
            ])->findOrFail($id);

            Log::info('Question retrieved', [
                'question_id' => $id,
            ]);

            return response()->json([
                'success' => true,
                'data' => $question,
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'NOT_FOUND',
                    'message' => 'Question not found',
                    'timestamp' => now()->toIso8601String(),
                ],
            ], 404);

        } catch (\Exception $e) {
            Log::error('Question retrieval failed', [
                'question_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'QUESTION_RETRIEVAL_ERROR',
                    'message' => 'Failed to retrieve question',
                    'details' => config('app.debug') ? $e->getMessage() : null,
                    'timestamp' => now()->toIso8601String(),
                ],
            ], 500);
        }
    }

    /**
     * Update an existing question.
     * 
     * Updates a question and its options. Validates authorization to ensure
     * only the author or admins can update questions.
     * 
     * @param UpdateQuestionRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateQuestionRequest $request, int $id): JsonResponse
    {
        try {
            // Get validated data from Form Request
            $validated = $request->validated();

            // The following validation is now handled by UpdateQuestionRequest
            /* $validated = $request->validate([
                'question_type_id' => 'sometimes|integer|exists:question_types,id',
                'question_text' => 'sometimes|string|max:5000',
                'grade_level_id' => 'sometimes|integer|exists:grades,id',
                'subject_id' => 'sometimes|integer|exists:subjects,id',
                'topic_id' => 'nullable|integer|exists:topics,id',
                'bloom_level' => 'nullable|integer|min:1|max:6',
                'difficulty_level' => 'nullable|integer|min:1|max:5',
                'estimated_time_sec' => 'nullable|integer|min:0',
                'status' => 'sometimes|string|in:draft,active,archived,review',
                'hints' => 'nullable|array',
                'hints.*' => 'string|max:1000',
                'explanation' => 'nullable|array',
                'explanation.text' => 'nullable|string|max:5000',
                'explanation.revealed_after_attempt' => 'nullable|boolean',
                'options' => 'nullable|array|min:2',
                'options.*.id' => 'nullable|integer|exists:question_options,id',
                'options.*.option_key' => 'required|string|max:10',
                'options.*.option_text' => 'required|string|max:1000',
                'options.*.is_correct' => 'required|boolean',
                'options.*.distractor_strength' => 'nullable|numeric|min:0|max:1',
                'options.*.order_index' => 'required|integer|min:0',
            ]); */

            $user = $request->user();

            // Find the question
            $question = Question::findOrFail($id);

            // Authorization and validation are now handled by UpdateQuestionRequest

            // Update question and options in a transaction
            $question = DB::transaction(function () use ($question, $validated) {
                // Update the question
                $updateData = array_filter([
                    'question_type_id' => $validated['question_type_id'] ?? null,
                    'question_text' => $validated['question_text'] ?? null,
                    'grade_id' => $validated['grade_id'] ?? null,
                    'subject_id' => $validated['subject_id'] ?? null,
                    'topic_id' => $validated['topic_id'] ?? null,
                    'bloom_level' => $validated['bloom_level'] ?? null,
                    'difficulty_level' => $validated['difficulty_level'] ?? null,
                    'estimated_time_sec' => $validated['estimated_time_sec'] ?? null,
                    'status' => $validated['status'] ?? null,
                    'hints' => $validated['hints'] ?? null,
                    'explanation' => $validated['explanation'] ?? null,
                ], function ($value) {
                    return $value !== null;
                });

                $question->update($updateData);

                // Update options if provided
                if (isset($validated['options'])) {
                    // Get existing option IDs
                    $existingOptionIds = $question->options->pluck('id')->toArray();
                    $providedOptionIds = collect($validated['options'])
                        ->pluck('id')
                        ->filter()
                        ->toArray();

                    // Delete options that are not in the provided list
                    $optionsToDelete = array_diff($existingOptionIds, $providedOptionIds);
                    if (!empty($optionsToDelete)) {
                        QuestionOption::whereIn('id', $optionsToDelete)->delete();
                    }

                    // Update or create options
                    foreach ($validated['options'] as $optionData) {
                        if (isset($optionData['id'])) {
                            // Update existing option
                            $option = QuestionOption::find($optionData['id']);
                            if ($option && $option->question_id === $question->id) {
                                $option->update([
                                    'option_key' => $optionData['option_key'],
                                    'option_text' => $optionData['option_text'],
                                    'is_correct' => $optionData['is_correct'],
                                    'distractor_strength' => $optionData['distractor_strength'] ?? null,
                                    'order_index' => $optionData['order_index'],
                                ]);
                            }
                        } else {
                            // Create new option
                            QuestionOption::create([
                                'question_id' => $question->id,
                                'option_key' => $optionData['option_key'],
                                'option_text' => $optionData['option_text'],
                                'is_correct' => $optionData['is_correct'],
                                'distractor_strength' => $optionData['distractor_strength'] ?? null,
                                'order_index' => $optionData['order_index'],
                            ]);
                        }
                    }
                }

                return $question;
            });

            // Load relationships for response
            $question->load(['questionType', 'options', 'grade', 'subject', 'topic', 'author']);

            Log::info('Question updated', [
                'question_id' => $question->id,
                'updated_by' => $user->id,
            ]);

            return response()->json([
                'success' => true,
                'data' => $question,
            ]);

        } catch (ValidationException $e) {
            Log::warning('Question update validation failed', [
                'errors' => $e->errors(),
            ]);

            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'Invalid request parameters',
                    'details' => $e->errors(),
                    'timestamp' => now()->toIso8601String(),
                ],
            ], 422);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'NOT_FOUND',
                    'message' => 'Question not found',
                    'timestamp' => now()->toIso8601String(),
                ],
            ], 404);

        } catch (\Exception $e) {
            Log::error('Question update failed', [
                'question_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'QUESTION_UPDATE_ERROR',
                    'message' => 'Failed to update question',
                    'details' => config('app.debug') ? $e->getMessage() : null,
                    'timestamp' => now()->toIso8601String(),
                ],
            ], 500);
        }
    }

    /**
     * Delete a question.
     * 
     * Soft deletes a question. Only the author or admins can delete questions.
     * Questions with associated quiz attempts cannot be deleted.
     * 
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        try {
            $user = $request->user();

            // Find the question
            $question = Question::findOrFail($id);

            // Check authorization - user must be the author or an admin
            if ($question->author_id !== $user->id && !$user->hasRole(['admin', 'super-admin'])) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'UNAUTHORIZED',
                        'message' => 'You do not have permission to delete this question',
                        'timestamp' => now()->toIso8601String(),
                    ],
                ], 403);
            }

            // Check if question has been used in quiz attempts
            $hasAttempts = DB::table('quiz_attempt_answers')
                ->where('question_id', $question->id)
                ->exists();

            if ($hasAttempts) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'QUESTION_IN_USE',
                        'message' => 'Cannot delete question that has been used in quiz attempts. Consider archiving instead.',
                        'timestamp' => now()->toIso8601String(),
                    ],
                ], 422);
            }

            // Delete the question (cascade will delete options)
            $question->delete();

            Log::info('Question deleted', [
                'question_id' => $id,
                'deleted_by' => $user->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Question deleted successfully',
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'NOT_FOUND',
                    'message' => 'Question not found',
                    'timestamp' => now()->toIso8601String(),
                ],
            ], 404);

        } catch (\Exception $e) {
            Log::error('Question deletion failed', [
                'question_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'QUESTION_DELETION_ERROR',
                    'message' => 'Failed to delete question',
                    'details' => config('app.debug') ? $e->getMessage() : null,
                    'timestamp' => now()->toIso8601String(),
                ],
            ], 500);
        }
    }

    /**
     * Import questions from JSON data.
     * 
     * Frontend converts all import methods (file, Excel paste, JSON) to JSON format.
     * This endpoint only accepts JSON array of questions with default metadata.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function import(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            // Validate input
            $validated = $request->validate([
                'questions' => 'required|array|min:1',
                'questions.*.question_text' => 'required|string|max:5000',
                'questions.*.option_a' => 'nullable|string|max:1000',
                'questions.*.option_b' => 'nullable|string|max:1000',
                'questions.*.option_c' => 'nullable|string|max:1000',
                'questions.*.option_d' => 'nullable|string|max:1000',
                'questions.*.option_e' => 'nullable|string|max:1000',
                'questions.*.option_f' => 'nullable|string|max:1000',
                'questions.*.correct_answer' => 'nullable|string|max:50',
                'questions.*.hints' => 'nullable|string',
                'questions.*.explanation' => 'nullable|string',
                'question_type_id' => 'required|integer|exists:question_types,id',
                'grade_id' => 'nullable|integer|exists:grades,id',
                'subject_id' => 'nullable|integer|exists:subjects,id',
                'topic_id' => 'nullable|integer|exists:topics,id',
                'difficulty' => 'nullable|string|in:Easy,Medium,Hard',
                'bloom_level' => 'nullable|integer|between:1,6',
                'status' => 'nullable|string|in:draft,active,archived,review',
                'preview' => 'nullable|boolean',
            ]);

            $isPreview = $validated['preview'] ?? false;

            Log::info('Question import started (JSON)', [
                'user_id' => $user->id,
                'question_count' => count($validated['questions']),
                'preview' => $isPreview,
            ]);

            // Initialize the import service
            $importService = new QuestionImportService();
            
            // Map difficulty to numeric value
            $difficultyMap = ['Easy' => 1, 'Medium' => 3, 'Hard' => 5];
            $difficultyLevel = isset($validated['difficulty']) ? $difficultyMap[$validated['difficulty']] : null;

            // Prepare default metadata
            $defaults = [
                'question_type_id' => $validated['question_type_id'],
                'grade_id' => $validated['grade_id'] ?? null,
                'subject_id' => $validated['subject_id'] ?? null,
                'topic_id' => $validated['topic_id'] ?? null,
                'difficulty_level' => $difficultyLevel,
                'bloom_level' => $validated['bloom_level'] ?? null,
                'status' => $validated['status'] ?? 'draft',
            ];

            // Import questions
            $results = $importService->importFromArray($validated['questions'], $user->id, $defaults, $isPreview);

            Log::info('Question import completed', [
                'user_id' => $user->id,
                'results' => $results,
            ]);

            // Determine response status based on results
            $statusCode = 200;
            if ($results['failed'] > 0 && $results['successful'] === 0) {
                $statusCode = 422; // All imports failed
            } elseif ($results['failed'] > 0) {
                $statusCode = 207; // Partial success (Multi-Status)
            }

            return response()->json([
                'success' => $results['successful'] > 0 || $isPreview,
                'data' => [
                    'total_rows' => $results['total_rows'],
                    'successful' => $results['successful'],
                    'failed' => $results['failed'],
                    'errors' => $results['errors'],
                    'valid' => $results['successful'],
                    'warnings' => 0,
                    'preview' => $results['preview'] ?? null,
                ],
                'message' => $this->getImportMessage($results),
            ], $statusCode);

        } catch (ValidationException $e) {
            Log::warning('Question import validation failed', [
                'errors' => $e->errors(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            Log::error('Question import failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'IMPORT_ERROR',
                    'message' => 'Failed to import questions',
                    'details' => config('app.debug') ? $e->getMessage() : 'An error occurred during import',
                    'timestamp' => now()->toIso8601String(),
                ],
            ], 500);
        }
    }

    /**
     * Duplicate an existing question.
     * 
     * Creates a copy of a question with all its options. The duplicated question
     * is set to 'draft' status and has '(Copy)' appended to the question text.
     * 
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function duplicate(Request $request, int $id): JsonResponse
    {
        try {
            $user = $request->user();

            // Find the original question with options
            $originalQuestion = Question::with('options')->findOrFail($id);

            // Create duplicate in a transaction
            $newQuestion = DB::transaction(function () use ($originalQuestion, $user) {
                // Replicate the question
                $newQuestion = $originalQuestion->replicate();
                $newQuestion->question_text = $originalQuestion->question_text . ' (Copy)';
                $newQuestion->status = 'draft';
                $newQuestion->author_id = $user->id;
                $newQuestion->usage_count = 0;
                $newQuestion->avg_success_rate = null;
                $newQuestion->discrimination_index = null;
                $newQuestion->save();

                // Replicate options
                foreach ($originalQuestion->options as $option) {
                    $newOption = $option->replicate();
                    $newOption->question_id = $newQuestion->id;
                    $newOption->save();
                }

                return $newQuestion;
            });

            // Load relationships for response
            $newQuestion->load(['questionType', 'options', 'grade', 'subject', 'topic', 'author']);

            Log::info('Question duplicated', [
                'original_id' => $id,
                'new_id' => $newQuestion->id,
                'user_id' => $user->id,
            ]);

            return response()->json([
                'success' => true,
                'data' => $newQuestion,
                'message' => 'Question duplicated successfully',
            ], 201);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'NOT_FOUND',
                    'message' => 'Question not found',
                    'timestamp' => now()->toIso8601String(),
                ],
            ], 404);

        } catch (\Exception $e) {
            Log::error('Question duplication failed', [
                'question_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'QUESTION_DUPLICATION_ERROR',
                    'message' => 'Failed to duplicate question',
                    'details' => config('app.debug') ? $e->getMessage() : null,
                    'timestamp' => now()->toIso8601String(),
                ],
            ], 500);
        }
    }

    /**
     * Update the status of a question.
     * 
     * Changes the status of a question (draft, active, archived, review).
     * Only the author or admins can change question status.
     * 
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'status' => 'required|string|in:draft,active,archived,review',
            ]);

            $user = $request->user();

            // Find the question
            $question = Question::findOrFail($id);

            // Check authorization - user must be the author or an admin
            if ($question->author_id !== $user->id && !$user->hasRole(['admin', 'super-admin'])) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'UNAUTHORIZED',
                        'message' => 'You do not have permission to change the status of this question',
                        'timestamp' => now()->toIso8601String(),
                    ],
                ], 403);
            }

            // Update the status
            $oldStatus = $question->status;
            $question->status = $validated['status'];
            $question->save();

            // Load relationships for response
            $question->load(['questionType', 'options', 'grade', 'subject', 'topic', 'author']);

            Log::info('Question status updated', [
                'question_id' => $id,
                'old_status' => $oldStatus,
                'new_status' => $validated['status'],
                'updated_by' => $user->id,
            ]);

            return response()->json([
                'success' => true,
                'data' => $question,
                'message' => 'Question status updated successfully',
            ]);

        } catch (ValidationException $e) {
            Log::warning('Question status update validation failed', [
                'errors' => $e->errors(),
            ]);

            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'Invalid request parameters',
                    'details' => $e->errors(),
                    'timestamp' => now()->toIso8601String(),
                ],
            ], 422);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'NOT_FOUND',
                    'message' => 'Question not found',
                    'timestamp' => now()->toIso8601String(),
                ],
            ], 404);

        } catch (\Exception $e) {
            Log::error('Question status update failed', [
                'question_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'STATUS_UPDATE_ERROR',
                    'message' => 'Failed to update question status',
                    'details' => config('app.debug') ? $e->getMessage() : null,
                    'timestamp' => now()->toIso8601String(),
                ],
            ], 500);
        }
    }

    /**
     * Export questions to Excel or CSV format.
     * 
     * Exports questions with filters applied. Returns a downloadable file
     * in the requested format (xlsx or csv).
     * 
     * @param Request $request
     * @return mixed
     */
    public function export(Request $request)
    {
        try {
            $validated = $request->validate([
                'format' => 'required|string|in:xlsx,csv',
                'question_type_id' => 'sometimes|integer|exists:question_types,id',
                'grade_id' => 'sometimes|integer|exists:grades,id',
                'subject_id' => 'sometimes|integer|exists:subjects,id',
                'topic_id' => 'sometimes|integer|exists:topics,id',
                'difficulty_level' => 'sometimes|integer|min:1|max:5',
                'bloom_level' => 'sometimes|integer|min:1|max:6',
                'status' => 'sometimes|string|in:draft,active,archived,review',
                'search' => 'sometimes|string|max:255',
            ]);

            // Build the query with same filters as index
            $query = Question::with([
                'questionType',
                'options' => function ($query) {
                    $query->orderBy('order_index');
                },
                'grade',
                'subject',
                'topic',
            ]);

            // Apply filters (same as index method)
            if (isset($validated['question_type_id'])) {
                $query->byType($validated['question_type_id']);
            }
            if (isset($validated['grade_id'])) {
                $query->byGrade($validated['grade_id']);
            }
            if (isset($validated['subject_id'])) {
                $query->bySubject($validated['subject_id']);
            }
            if (isset($validated['topic_id'])) {
                $query->where('topic_id', $validated['topic_id']);
            }
            if (isset($validated['difficulty_level'])) {
                $query->where('difficulty_level', $validated['difficulty_level']);
            }
            if (isset($validated['bloom_level'])) {
                $query->where('bloom_level', $validated['bloom_level']);
            }
            if (isset($validated['status'])) {
                $query->byStatus($validated['status']);
            }
            if (isset($validated['search'])) {
                $query->where('question_text', 'like', '%' . $validated['search'] . '%');
            }

            $questions = $query->get();

            if ($questions->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'NO_DATA',
                        'message' => 'No questions found to export',
                        'timestamp' => now()->toIso8601String(),
                    ],
                ], 404);
            }

            // Prepare data for export
            $exportData = [];
            $exportData[] = [
                'Question Type',
                'Question Text',
                'Grade Level',
                'Subject',
                'Topic',
                'Difficulty',
                'Bloom Level',
                'Time (sec)',
                'Status',
                'Option A',
                'Option B',
                'Option C',
                'Option D',
                'Option E',
                'Option F',
                'Correct Answer(s)',
                'Hints',
                'Explanation',
            ];

            foreach ($questions as $question) {
                $row = [
                    $question->questionType->slug ?? '',
                    $question->question_text,
                    $question->grade->name ?? '',
                    $question->subject->name ?? '',
                    $question->topic->name ?? '',
                    $question->difficulty_level ?? '',
                    $question->bloom_level ?? '',
                    $question->estimated_time_sec ?? '',
                    $question->status,
                ];

                // Add options (up to 6)
                $options = $question->options->sortBy('order_index');
                for ($i = 0; $i < 6; $i++) {
                    $row[] = $options[$i]->option_text ?? '';
                }

                // Add correct answers
                $correctAnswers = $options->filter(fn($opt) => $opt->is_correct)
                    ->pluck('option_key')
                    ->implode(',');
                $row[] = $correctAnswers;

                // Add hints
                $hints = is_array($question->hints) ? implode('|', $question->hints) : '';
                $row[] = $hints;

                // Add explanation
                $explanation = is_array($question->explanation) 
                    ? ($question->explanation['text'] ?? '') 
                    : '';
                $row[] = $explanation;

                $exportData[] = $row;
            }

            $format = $validated['format'];
            $filename = 'questions_export_' . date('Y-m-d_His') . '.' . $format;

            if ($format === 'csv') {
                // Generate CSV
                $handle = fopen('php://temp', 'r+');
                foreach ($exportData as $row) {
                    fputcsv($handle, $row);
                }
                rewind($handle);
                $csv = stream_get_contents($handle);
                fclose($handle);

                Log::info('Questions exported as CSV', [
                    'count' => $questions->count(),
                    'user_id' => $request->user()->id,
                ]);

                return response($csv, 200)
                    ->header('Content-Type', 'text/csv')
                    ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
            } else {
                // Generate Excel using PhpSpreadsheet
                $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
                $sheet = $spreadsheet->getActiveSheet();

                // Write data
                $sheet->fromArray($exportData, null, 'A1');

                // Style header row
                $headerStyle = [
                    'font' => ['bold' => true],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'E0E0E0'],
                    ],
                ];
                $sheet->getStyle('A1:R1')->applyFromArray($headerStyle);

                // Auto-size columns
                foreach (range('A', 'R') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }

                $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
                
                // Write to temp file
                $tempFile = tempnam(sys_get_temp_dir(), 'export_');
                $writer->save($tempFile);

                Log::info('Questions exported as Excel', [
                    'count' => $questions->count(),
                    'user_id' => $request->user()->id,
                ]);

                return response()->download($tempFile, $filename, [
                    'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                ])->deleteFileAfterSend(true);
            }

        } catch (ValidationException $e) {
            Log::warning('Question export validation failed', [
                'errors' => $e->errors(),
            ]);

            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'Invalid request parameters',
                    'details' => $e->errors(),
                    'timestamp' => now()->toIso8601String(),
                ],
            ], 422);

        } catch (\Exception $e) {
            Log::error('Question export failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'EXPORT_ERROR',
                    'message' => 'Failed to export questions',
                    'details' => config('app.debug') ? $e->getMessage() : null,
                    'timestamp' => now()->toIso8601String(),
                ],
            ], 500);
        }
    }

    /**
     * Generate a user-friendly message based on import results.
     * 
     * @param array $results Import results
     * @return string User-friendly message
     */
    protected function getImportMessage(array $results): string
    {
        if ($results['successful'] === 0 && $results['failed'] === 0) {
            return 'No questions were imported. The file may be empty.';
        }

        if ($results['failed'] === 0) {
            return "Successfully imported {$results['successful']} question(s).";
        }

        if ($results['successful'] === 0) {
            return "Failed to import all {$results['failed']} question(s). Please check the error details.";
        }

        return "Imported {$results['successful']} question(s) successfully. {$results['failed']} question(s) failed. Please check the error details.";
    }
}
