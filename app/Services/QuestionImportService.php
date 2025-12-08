<?php

namespace App\Services;

use App\Models\Question;
use App\Models\QuestionType;
use App\Models\QuestionOption;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class QuestionImportService
{
    /**
     * Cache for question type slug to ID mapping
     */
    protected array $questionTypeMap = [];

    /**
     * Import results tracking
     */
    protected array $importResults = [
        'total_rows' => 0,
        'successful' => 0,
        'failed' => 0,
        'errors' => [],
    ];

    /**
     * Initialize the service and load question type mappings
     */
    public function __construct()
    {
        $this->loadQuestionTypeMap();
    }

    /**
     * Load question type slug to ID mapping into cache
     */
    protected function loadQuestionTypeMap(): void
    {
        $questionTypes = QuestionType::all();
        
        foreach ($questionTypes as $type) {
            $this->questionTypeMap[$type->slug] = $type->id;
        }
    }

    /**
     * Import questions from a CSV file
     * 
     * @param string $filePath Path to the CSV file
     * @param int $authorId ID of the user importing the questions
     * @return array Import results with success/error details
     */
    public function importFromCsv(string $filePath, int $authorId): array
    {
        $this->resetImportResults();

        if (!file_exists($filePath)) {
            throw new \InvalidArgumentException("File not found: {$filePath}");
        }

        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            throw new \RuntimeException("Unable to open file: {$filePath}");
        }

        try {
            // Read header row
            $headers = fgetcsv($handle);
            if ($headers === false) {
                throw new \RuntimeException("Unable to read CSV headers");
            }

            // Normalize headers (trim and lowercase)
            $headers = array_map(fn($h) => strtolower(trim($h)), $headers);

            // Validate required columns
            $this->validateHeaders($headers);

            $rowNumber = 1; // Start at 1 (header is row 0)

            // Process each data row
            while (($row = fgetcsv($handle)) !== false) {
                $rowNumber++;
                $this->importResults['total_rows']++;

                // Skip empty rows
                if (empty(array_filter($row))) {
                    continue;
                }

                // Combine headers with row data
                $data = array_combine($headers, $row);

                // Import the question
                $this->importQuestion($data, $authorId, $rowNumber);
            }

            fclose($handle);

            Log::info('CSV import completed', $this->importResults);

            return $this->importResults;
        } catch (\Exception $e) {
            fclose($handle);
            throw $e;
        }
    }

    /**
     * Import questions from an Excel file
     * 
     * @param string $filePath Path to the Excel file
     * @param int $authorId ID of the user importing the questions
     * @return array Import results with success/error details
     */
    public function importFromExcel(string $filePath, int $authorId): array
    {
        $this->resetImportResults();

        if (!file_exists($filePath)) {
            throw new \InvalidArgumentException("File not found: {$filePath}");
        }

        // Check if PhpSpreadsheet is available
        if (!class_exists(\PhpOffice\PhpSpreadsheet\IOFactory::class)) {
            throw new \RuntimeException("PhpSpreadsheet library is required for Excel import. Install it with: composer require phpoffice/phpspreadsheet");
        }

        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            if (empty($rows)) {
                throw new \RuntimeException("Excel file is empty");
            }

            // Read header row
            $headers = array_shift($rows);
            
            // Normalize headers (trim and lowercase)
            $headers = array_map(fn($h) => strtolower(trim($h ?? '')), $headers);

            // Validate required columns
            $this->validateHeaders($headers);

            $rowNumber = 1; // Start at 1 (header is row 0)

            // Process each data row
            foreach ($rows as $row) {
                $rowNumber++;
                $this->importResults['total_rows']++;

                // Skip empty rows
                if (empty(array_filter($row))) {
                    continue;
                }

                // Combine headers with row data
                $data = array_combine($headers, $row);

                // Import the question
                $this->importQuestion($data, $authorId, $rowNumber);
            }

            Log::info('Excel import completed', $this->importResults);

            return $this->importResults;
        } catch (\Exception $e) {
            Log::error('Excel import failed', [
                'error' => $e->getMessage(),
                'file' => $filePath,
            ]);
            throw $e;
        }
    }

    /**
     * Validate that all required headers are present
     * 
     * @param array $headers Array of header names
     * @throws ValidationException If required headers are missing
     */
    protected function validateHeaders(array $headers): void
    {
        $requiredHeaders = [
            'question_type',
            'grade_level',
            'subject',
            'question_text',
        ];

        $missingHeaders = array_diff($requiredHeaders, $headers);

        if (!empty($missingHeaders)) {
            throw ValidationException::withMessages([
                'headers' => 'Missing required columns: ' . implode(', ', $missingHeaders),
            ]);
        }
    }

    /**
     * Import a single question from row data
     * 
     * @param array $data Row data with column names as keys
     * @param int $authorId ID of the user importing the questions
     * @param int $rowNumber Row number for error reporting
     */
    protected function importQuestion(array $data, int $authorId, int $rowNumber): void
    {
        try {
            // Validate the row data
            $validatedData = $this->validateQuestionData($data, $rowNumber);

            // Create the question with options in a transaction
            DB::transaction(function () use ($validatedData, $authorId) {
                $this->createQuestionWithOptions($validatedData, $authorId);
            });

            $this->importResults['successful']++;
        } catch (\Exception $e) {
            $this->importResults['failed']++;
            $this->importResults['errors'][] = [
                'row' => $rowNumber,
                'message' => $e->getMessage(),
                'data' => $data,
            ];

            Log::warning('Question import failed for row', [
                'row' => $rowNumber,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Validate question data from a row
     * 
     * @param array $data Row data
     * @param int $rowNumber Row number for error reporting
     * @return array Validated and normalized data
     * @throws ValidationException If validation fails
     */
    protected function validateQuestionData(array $data, int $rowNumber): array
    {
        // Map question type slug to ID
        $questionTypeSlug = strtolower(trim($data['question_type'] ?? ''));
        
        if (!isset($this->questionTypeMap[$questionTypeSlug])) {
            throw new \InvalidArgumentException(
                "Invalid question type '{$questionTypeSlug}'. Valid types: " . 
                implode(', ', array_keys($this->questionTypeMap))
            );
        }

        $questionTypeId = $this->questionTypeMap[$questionTypeSlug];

        // Prepare question data
        $questionData = [
            'question_type_id' => $questionTypeId,
            'question_text' => trim($data['question_text'] ?? ''),
            'grade_level' => trim($data['grade_level'] ?? ''),
            'subject' => trim($data['subject'] ?? ''),
            'topic' => trim($data['topic'] ?? ''),
            'bloom_level' => !empty($data['bloom_level']) ? (int) $data['bloom_level'] : null,
            'difficulty_level' => !empty($data['difficulty_level']) ? (int) $data['difficulty_level'] : null,
            'estimated_time_sec' => !empty($data['estimated_time_sec']) ? (int) $data['estimated_time_sec'] : null,
            'status' => trim($data['status'] ?? 'draft'),
        ];

        // Validate question data
        $validator = Validator::make($questionData, [
            'question_type_id' => 'required|integer',
            'question_text' => 'required|string|max:5000',
            'grade_level' => 'required|string',
            'subject' => 'required|string',
            'topic' => 'nullable|string',
            'bloom_level' => 'nullable|integer|between:1,6',
            'difficulty_level' => 'nullable|integer|between:1,5',
            'estimated_time_sec' => 'nullable|integer|min:1',
            'status' => 'required|in:draft,active,archived,review',
        ]);

        if ($validator->fails()) {
            throw new \InvalidArgumentException(
                "Validation failed: " . implode(', ', $validator->errors()->all())
            );
        }

        // Extract options (A, B, C, D, etc.)
        $options = [];
        $optionKeys = ['a', 'b', 'c', 'd', 'e', 'f'];
        
        foreach ($optionKeys as $key) {
            $optionKey = 'option_' . $key;
            if (!empty($data[$optionKey])) {
                $options[strtoupper($key)] = trim($data[$optionKey]);
            }
        }

        // Parse correct answer(s)
        $correctAnswers = [];
        if (!empty($data['correct_answer'])) {
            $correctAnswerStr = strtoupper(trim($data['correct_answer']));
            // Support both single (A) and multiple (A,C) correct answers
            $correctAnswers = array_map('trim', explode(',', $correctAnswerStr));
        }

        // Validate that we have options and correct answers for option-based questions
        $questionType = QuestionType::find($questionTypeId);
        if ($questionType && $questionType->has_options) {
            if (empty($options)) {
                throw new \InvalidArgumentException("Question type '{$questionTypeSlug}' requires options");
            }
            if (empty($correctAnswers)) {
                throw new \InvalidArgumentException("Correct answer(s) must be specified");
            }
            // Validate that correct answers exist in options
            foreach ($correctAnswers as $correctAnswer) {
                if (!isset($options[$correctAnswer])) {
                    throw new \InvalidArgumentException("Correct answer '{$correctAnswer}' not found in options");
                }
            }
        }

        // Parse hints (semicolon-separated)
        $hints = [];
        if (!empty($data['hints'])) {
            $hints = array_map('trim', explode(';', $data['hints']));
            $hints = array_filter($hints); // Remove empty hints
        }

        // Parse explanation
        $explanation = null;
        if (!empty($data['explanation'])) {
            $explanation = [
                'text' => trim($data['explanation']),
                'revealed_after_attempt' => true,
            ];
        }

        return [
            'question' => $questionData,
            'options' => $options,
            'correct_answers' => $correctAnswers,
            'hints' => $hints,
            'explanation' => $explanation,
        ];
    }

    /**
     * Create a question with its options
     * 
     * @param array $validatedData Validated question data
     * @param int $authorId ID of the user creating the question
     * @return Question The created question
     */
    protected function createQuestionWithOptions(array $validatedData, int $authorId): Question
    {
        // This method should be called within a transaction
        
        // Resolve grade_level, subject, and topic IDs (if provided as names)
        $gradeLevelId = null;
        $subjectId = null;
        $topicId = null;

        // Check if grade_level is provided as name or ID
        if (!empty($validatedData['question']['grade_level'])) {
            $gradeLevelId = is_numeric($validatedData['question']['grade_level']) 
                ? $validatedData['question']['grade_level']
                : $this->resolveGradeLevelId($validatedData['question']['grade_level']);
        } elseif (!empty($validatedData['question']['grade_id'])) {
            $gradeLevelId = $validatedData['question']['grade_id'];
        }

        // Check if subject is provided as name or ID
        if (!empty($validatedData['question']['subject'])) {
            $subjectId = is_numeric($validatedData['question']['subject']) 
                ? $validatedData['question']['subject']
                : $this->resolveSubjectId($validatedData['question']['subject']);
        } elseif (!empty($validatedData['question']['subject_id'])) {
            $subjectId = $validatedData['question']['subject_id'];
        }

        // Check if topic is provided as name or ID
        if (!empty($validatedData['question']['topic'])) {
            $topicId = is_numeric($validatedData['question']['topic']) 
                ? $validatedData['question']['topic']
                : $this->resolveTopicId($validatedData['question']['topic']);
        } elseif (!empty($validatedData['question']['topic_id'])) {
            $topicId = $validatedData['question']['topic_id'];
        }

        // Create the question
        $question = Question::create([
            'question_type_id' => $validatedData['question']['question_type_id'],
            'question_text' => $validatedData['question']['question_text'],
            'grade_id' => $gradeLevelId,
            'subject_id' => $subjectId,
            'topic_id' => $topicId,
            'bloom_level' => $validatedData['question']['bloom_level'],
            'difficulty_level' => $validatedData['question']['difficulty_level'],
            'estimated_time_sec' => $validatedData['question']['estimated_time_sec'],
            'author_id' => $authorId,
            'status' => $validatedData['question']['status'],
            'usage_count' => 0,
            'hints' => $validatedData['hints'],
            'explanation' => $validatedData['explanation'],
        ]);

        // Create options if present
        if (!empty($validatedData['options'])) {
            $orderIndex = 0;
            foreach ($validatedData['options'] as $optionKey => $optionText) {
                QuestionOption::create([
                    'question_id' => $question->id,
                    'option_key' => $optionKey,
                    'option_text' => $optionText,
                    'is_correct' => in_array($optionKey, $validatedData['correct_answers']),
                    'order_index' => $orderIndex++,
                ]);
            }
        }

        return $question;
    }

    /**
     * Resolve grade level name to ID
     * 
     * @param string $gradeLevelName Grade level name
     * @return int Grade level ID
     */
    protected function resolveGradeLevelId(string $gradeLevelName): int
    {
        // Try to find by name
        $gradeLevel = \App\Models\Grade::where('name', $gradeLevelName)->first();
        
        if (!$gradeLevel) {
            // Create if doesn't exist
            $gradeLevel = \App\Models\Grade::create(['name' => $gradeLevelName]);
        }
        
        return $gradeLevel->id;
    }

    /**
     * Resolve subject name to ID
     * 
     * @param string $subjectName Subject name
     * @return int Subject ID
     */
    protected function resolveSubjectId(string $subjectName): int
    {
        // Try to find by name
        $subject = \App\Models\Subject::where('name', $subjectName)->first();
        
        if (!$subject) {
            // Create if doesn't exist
            $subject = \App\Models\Subject::create(['name' => $subjectName]);
        }
        
        return $subject->id;
    }

    /**
     * Resolve topic name to ID
     * 
     * @param string $topicName Topic name
     * @return int Topic ID
     */
    protected function resolveTopicId(string $topicName): int
    {
        // Try to find by name
        $topic = \App\Models\Topic::where('name', $topicName)->first();
        
        if (!$topic) {
            // Create if doesn't exist
            $topic = \App\Models\Topic::create(['name' => $topicName]);
        }
        
        return $topic->id;
    }

    /**
     * Reset import results for a new import
     */
    protected function resetImportResults(): void
    {
        $this->importResults = [
            'total_rows' => 0,
            'successful' => 0,
            'failed' => 0,
            'errors' => [],
        ];
    }

    /**
     * Import questions from an array of question data
     * 
     * @param array $questions Array of question data
     * @param int $authorId ID of the user importing the questions
     * @param array $defaults Default metadata to apply to all questions
     * @param bool $preview If true, only validate without creating records
     * @return array Import results with success/error details
     */
    public function importFromArray(array $questions, int $authorId, array $defaults = [], bool $preview = false): array
    {
        $this->resetImportResults();

        $rowNumber = 0;

        foreach ($questions as $questionData) {
            $rowNumber++;
            $this->importResults['total_rows']++;

            try {
                // Merge with defaults
                $mergedData = array_merge($defaults, $questionData);

                // Validate the question data
                $validatedData = $this->validateArrayQuestionData($mergedData, $rowNumber);

                if (!$preview) {
                    // Create the question with options in a transaction
                    DB::transaction(function () use ($validatedData, $authorId, $rowNumber) {
                        $question = $this->createQuestionWithOptions($validatedData, $authorId);
                        
                        Log::info('Question created successfully', [
                            'row' => $rowNumber,
                            'question_id' => $question->id,
                            'question_text' => $question->question_text,
                        ]);
                    });
                } else {
                    Log::info('Preview mode - question validated but not created', [
                        'row' => $rowNumber,
                        'question_text' => $validatedData['question']['question_text'] ?? 'N/A',
                    ]);
                }

                $this->importResults['successful']++;
            } catch (\Exception $e) {
                $this->importResults['failed']++;
                $this->importResults['errors'][] = [
                    'row' => $rowNumber,
                    'message' => $e->getMessage(),
                    'data' => $questionData,
                ];

                Log::warning('Question import failed for row', [
                    'row' => $rowNumber,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('Array import completed', $this->importResults);

        return $this->importResults;
    }

    /**
     * Validate question data from array format
     * 
     * @param array $data Question data
     * @param int $rowNumber Row number for error reporting
     * @return array Validated and normalized data
     * @throws \InvalidArgumentException If validation fails
     */
    protected function validateArrayQuestionData(array $data, int $rowNumber): array
    {
        // Validate required fields
        if (empty($data['question_type_id'])) {
            throw new \InvalidArgumentException("Question type is required");
        }

        if (empty($data['question_text'])) {
            throw new \InvalidArgumentException("Question text is required");
        }

        $questionTypeId = $data['question_type_id'];

        // Prepare question data
        $questionData = [
            'question_type_id' => $questionTypeId,
            'question_text' => trim($data['question_text']),
            'grade_id' => $data['grade_id'] ?? null,
            'subject_id' => $data['subject_id'] ?? null,
            'topic_id' => $data['topic_id'] ?? null,
            'bloom_level' => $data['bloom_level'] ?? null,
            'difficulty_level' => $data['difficulty_level'] ?? null,
            'estimated_time_sec' => $data['estimated_time_sec'] ?? null,
            'status' => $data['status'] ?? 'draft',
        ];

        // Validate question data
        $validator = Validator::make($questionData, [
            'question_type_id' => 'required|integer',
            'question_text' => 'required|string|max:5000',
            'grade_id' => 'nullable|integer',
            'subject_id' => 'nullable|integer',
            'topic_id' => 'nullable|integer',
            'bloom_level' => 'nullable|integer|between:1,6',
            'difficulty_level' => 'nullable|integer|between:1,5',
            'estimated_time_sec' => 'nullable|integer|min:1',
            'status' => 'required|in:draft,active,archived,review',
        ]);

        if ($validator->fails()) {
            throw new \InvalidArgumentException(
                "Validation failed: " . implode(', ', $validator->errors()->all())
            );
        }

        // Extract options (A, B, C, D, E, F)
        $options = [];
        $optionKeys = ['a', 'b', 'c', 'd', 'e', 'f'];
        
        foreach ($optionKeys as $key) {
            $optionKey = 'option_' . $key;
            if (!empty($data[$optionKey])) {
                $options[strtoupper($key)] = trim($data[$optionKey]);
            }
        }

        // Parse correct answer(s)
        $correctAnswers = [];
        if (!empty($data['correct_answer'])) {
            $correctAnswerStr = strtoupper(trim($data['correct_answer']));
            // Support both single (A) and multiple (A,C) correct answers
            $correctAnswers = array_map('trim', explode(',', $correctAnswerStr));
        }

        // Validate that we have options and correct answers for option-based questions
        $questionType = QuestionType::find($questionTypeId);
        if ($questionType && $questionType->has_options) {
            if (empty($options)) {
                throw new \InvalidArgumentException("Question type '{$questionType->slug}' requires options");
            }
            if (empty($correctAnswers)) {
                throw new \InvalidArgumentException("Correct answer(s) must be specified");
            }
            // Validate that correct answers exist in options
            foreach ($correctAnswers as $correctAnswer) {
                if (!isset($options[$correctAnswer])) {
                    throw new \InvalidArgumentException("Correct answer '{$correctAnswer}' not found in options");
                }
            }
        }

        // Parse hints (semicolon-separated or array)
        $hints = [];
        if (!empty($data['hints'])) {
            if (is_array($data['hints'])) {
                $hints = $data['hints'];
            } else {
                $hints = array_map('trim', explode(';', $data['hints']));
                $hints = array_filter($hints); // Remove empty hints
            }
        }

        // Parse explanation
        $explanation = null;
        if (!empty($data['explanation'])) {
            if (is_array($data['explanation'])) {
                $explanation = $data['explanation'];
            } else {
                $explanation = [
                    'text' => trim($data['explanation']),
                    'revealed_after_attempt' => true,
                ];
            }
        }

        return [
            'question' => $questionData,
            'options' => $options,
            'correct_answers' => $correctAnswers,
            'hints' => $hints,
            'explanation' => $explanation,
        ];
    }

    /**
     * Get the current import results
     * 
     * @return array Import results
     */
    public function getImportResults(): array
    {
        return $this->importResults;
    }
}
