# AI Lesson Plan Generator with Question Support

## Date: 2026-01-16

## Overview
Enhanced the AI Lesson Plan Generator to support generating complete lesson plans with both text slides and interactive question slides. The system now intelligently generates subject-appropriate questions with proper structure for the QuestionSlide component.

## What Was Implemented

### 1. Enhanced Prompt Generation (`resources/js/utils/lessonPlanPrompts.js`)
- **Subject-Specific Guidance**: Added intelligent question type suggestions based on subject:
  - **Mathematics**: Single choice for concepts, true/false for theorems, step-by-step problems
  - **Science**: Single choice for facts, true/false for statements, experiment-based questions
  - **Language Arts**: Grammar/vocabulary questions, comprehension checks, text analysis
  - **General**: Mixed question types for engagement

- **Question Structure Examples**: Provided detailed JSON examples for:
  - **Single Choice**: Most common, with options array and string correct_answer
  - **True/False**: Boolean correct_answer
  - **Multiple Choice**: Array of correct_answer IDs

- **Section-Based Guidelines**:
  - Objectives: 100% text slides
  - Learn/Content: 70% text, 30% questions
  - Practice: 70% questions, 30% text
  - Assessment/Review: 80-90% questions

### 2. Enhanced Validation
- Added comprehensive validation for question slides
- Validates question structure (id, type, text, options, correct_answer)
- Ensures proper data types for different question types
- Validates that question slides have at least 1 question

### 3. AI Lesson Plan Generator Component (`resources/js/Components/Common/ai/AILessonPlanGenerator.vue`)
- Multi-step wizard (Configure → Use AI → Paste → Preview)
- Embedded iframe for AI tools (Gemini, ChatGPT, Claude)
- Shows lesson context (subject, grade, title, sections)
- Preview shows total slides count per section
- Accepts and integrates generated slides into lesson

### 4. Integration with Lesson Editor (`resources/js/Pages/my_table_mnger/lesson_presentation/lesson_presentation.vue`)
- Added gradient "Generate with AI" button in toolbar
- Validates lesson has a title before opening generator
- Automatically adds all generated slides to appropriate sections
- Switches to first section after accepting plan
- Shows success notification with slide count

## Question Slide Structure

The AI generates questions in this format:

```json
{
  "slide_type": "question",
  "slide_content": {
    "questions": [
      {
        "id": "q_abc123",
        "type": "single_choice",
        "text": "What is 2 + 2?",
        "options": [
          {"id": "opt_1", "text": "3"},
          {"id": "opt_2", "text": "4"},
          {"id": "opt_3", "text": "5"}
        ],
        "correct_answer": "opt_2",
        "explanation": "2 + 2 equals 4",
        "timer": 30
      }
    ]
  }
}
```

## Supported Question Types

1. **single_choice**: One correct answer from multiple options
2. **multiple_choice**: Multiple correct answers (array of IDs)
3. **true_false**: Boolean true/false questions

## User Workflow

1. Open lesson editor
2. Set lesson title
3. Click "Generate with AI" button (purple gradient with sparkle icon)
4. Review lesson configuration (auto-filled from context)
5. Add custom instructions (optional)
6. Click "Generate Prompt"
7. Copy prompt and use AI tool (embedded or new tab)
8. Paste AI response
9. System validates JSON and question structure
10. Preview shows all sections and slides
11. Accept to add all slides to lesson
12. Save lesson to persist changes

## Benefits

- **Time-Saving**: Generate complete lesson content in minutes
- **Subject-Appropriate**: Questions tailored to subject type
- **Pedagogically Sound**: Follows best practices for section distribution
- **Interactive**: Mix of content delivery and assessment
- **Flexible**: Works with any AI tool (Gemini, ChatGPT, Claude, etc.)
- **Validated**: Ensures proper structure before acceptance

## Future Enhancements

1. Add support for more question types (short_answer, fill_blank, essay)
2. Allow editing questions in preview before acceptance
3. Generate images/diagrams for visual subjects
4. Support for multi-language content generation
5. Template library of successful AI-generated lessons
6. Direct API integration with AI providers (optional)
