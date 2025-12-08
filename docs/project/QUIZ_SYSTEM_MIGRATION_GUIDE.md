# Quiz System Migration Guide

This guide explains how to migrate existing lesson presentation questions to the new enterprise quiz system.

## Overview

The new quiz system provides:
- Enhanced question management with the QuizEngine component
- Better accessibility (WCAG 2.1 AA compliant)
- Comprehensive analytics and reporting
- Type-safe TypeScript interfaces
- Improved user experience with progress tracking

## Migration Process

### Step 1: Backup Your Data

Before running the migration, create a backup of your database:

```bash
php artisan db:backup
```

Or manually export your `lesson_presentation_slides` table.

### Step 2: Run the Migration

Execute the migration script to transfer questions to the new schema:

```bash
php artisan migrate --path=database/migrations/2025_11_26_000000_migrate_lesson_questions_to_quiz_system.php
```

This migration will:
- Read all question slides from lesson presentations
- Convert compatible question types (single_choice, multiple_choice, true_false)
- Create records in the `questions` and `question_options` tables
- Preserve hints and explanations
- Skip incompatible or already migrated questions

### Step 3: Validate Migrated Data

Run the validation command to check for any issues:

```bash
php artisan quiz:validate-migration
```

This will check:
- All questions have valid question types
- All questions have at least one correct option
- Single choice questions have exactly one correct option
- All questions have at least 2 options

### Step 4: Review Migration Logs

Check the Laravel log file for migration details:

```bash
tail -f storage/logs/laravel.log
```

Look for entries like:
```
Question Migration Results: {
  "migrated": 150,
  "skipped": 25,
  "errors": []
}
```

## What Gets Migrated

### Compatible Question Types

The following question types are automatically migrated:
- **single_choice**: Multiple choice with one correct answer
- **multiple_choice**: Multiple choice with multiple correct answers
- **true_false**: True/False questions

### Question Data Preserved

- Question text
- Answer options
- Correct answers
- Hints (stored as JSON array)
- Explanations (stored as JSON object)
- Bloom level (if set)
- Difficulty level (if set)
- Timer/estimated time (if set)

### What Doesn't Get Migrated

The following question types are NOT migrated automatically:
- fill_blank
- short_answer
- essay
- step_by_step
- Interactive question types (labelled-diagram, match-up, etc.)

These questions will continue to use the legacy UniversalQuestionPlayer component.

## How the Integration Works

### QuestionSlide Component

The updated `QuestionSlide` component now supports two modes:

1. **Edit Mode** (`mode="edit"`): Shows the question editor interface
2. **Play Mode** (`mode="play"`): Renders questions for students

In play mode, the component automatically detects:
- If questions are compatible with QuizEngine → uses QuizEngine
- If questions are incompatible → falls back to UniversalQuestionPlayer

### LessonPlayer Integration

The `LessonPlayer` component now:
- Uses QuestionSlide with `mode="play"`
- Handles quiz completion events
- Saves quiz attempts to the database via API
- Tracks individual answer submissions
- Updates progress indicators

### Data Flow

```
Student answers question
    ↓
QuizEngine emits 'answer-selected' event
    ↓
QuestionSlide forwards to LessonPlayer
    ↓
LessonPlayer saves to database via API
    ↓
Quiz completion triggers 'quiz-completed' event
    ↓
Full results saved to quiz_attempts table
```

## API Endpoints

The integration uses these API endpoints:

### Save Individual Answer
```
POST /api/quiz-attempts/answers
{
  "presentation_id": 123,
  "slide_id": 456,
  "question_id": "q_abc123",
  "selected_option_id": "opt_1",
  "is_correct": true,
  "time_spent_sec": 45,
  "answered_at": "2025-11-26T10:30:00Z"
}
```

### Save Quiz Attempt
```
POST /api/quiz-attempts
{
  "presentation_id": 123,
  "slide_id": 456,
  "attempt_id": "attempt_123_456_1732618200000",
  "total_questions": 10,
  "correct_answers": 8,
  "percentage": 80.00,
  "completed_at": "2025-11-26T10:35:00Z",
  "answers": [...],
  "metadata": {...}
}
```

## Backward Compatibility

The system maintains full backward compatibility:

1. **Existing lessons continue to work**: No changes required to existing lesson presentations
2. **Legacy questions still supported**: Non-migrated questions use UniversalQuestionPlayer
3. **Gradual migration**: You can migrate questions incrementally
4. **No data loss**: Original questions remain in lesson_presentation_slides

## Testing the Migration

### 1. Test in Preview Mode

Open a lesson with migrated questions in preview mode:
- Questions should render using QuizEngine
- Progress bar should update correctly
- Answer feedback should display properly
- Quiz completion should show results

### 2. Test Student View

Have a test student complete a quiz:
- Verify answers are saved to database
- Check quiz_attempts table for completion record
- Verify quiz_attempt_answers table has individual answers
- Confirm progress tracking works correctly

### 3. Test Mixed Content

Create a lesson with both:
- Migrated questions (single_choice, multiple_choice, true_false)
- Non-migrated questions (other types)

Verify both render correctly in the same lesson.

## Troubleshooting

### Questions Not Rendering

**Problem**: Questions show as blank or don't render

**Solution**: 
1. Check browser console for errors
2. Verify question data format in database
3. Run validation command: `php artisan quiz:validate-migration`

### Answers Not Saving

**Problem**: Quiz completes but results aren't saved

**Solution**:
1. Check API endpoints are accessible
2. Verify database tables exist (questions, question_options, quiz_attempts)
3. Check Laravel logs for API errors
4. Ensure user has proper permissions

### Migration Skipped Questions

**Problem**: Some questions weren't migrated

**Solution**:
1. Check migration logs for skip reasons
2. Verify question types are compatible
3. Check for duplicate questions (already migrated)
4. Manually review skipped questions

### Validation Errors

**Problem**: Validation command reports errors

**Solution**:
1. Review specific error messages
2. Check question_options table for missing data
3. Verify correct_answer values in original questions
4. Manually fix data issues in database

## Rollback

If you need to rollback the migration:

```bash
php artisan migrate:rollback --path=database/migrations/2025_11_26_000000_migrate_lesson_questions_to_quiz_system.php
```

**Note**: The rollback does NOT delete migrated questions to prevent data loss. Manual cleanup may be required.

## Support

For issues or questions:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Run validation: `php artisan quiz:validate-migration`
3. Review this guide
4. Contact the development team

## Next Steps

After successful migration:
1. Test thoroughly in preview mode
2. Have teachers review migrated questions
3. Conduct pilot testing with students
4. Monitor quiz completion rates and analytics
5. Gradually migrate remaining lessons

## Benefits of the New System

- **Better UX**: Modern, accessible interface
- **Analytics**: Track question performance and student progress
- **Type Safety**: TypeScript interfaces prevent errors
- **Extensibility**: Easy to add new question types
- **Accessibility**: WCAG 2.1 AA compliant
- **Performance**: Optimized rendering and caching
