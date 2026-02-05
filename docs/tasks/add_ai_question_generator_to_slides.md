---
description: Add AI Question Generator to Lesson Presentation Slide
---

# Task: Add AI Question Generator to Lesson Presentation

## Context
The user wants to add an "AI Generate" button next to the "Add Question" button in the `QuestionSlide` component of the Lesson Presentation editor. This button should open a dialog allowing the user to generate questions using AI, similar to the existing implementation in the Question Bank.

## Requirements
1.  **AI Button**: Add an "AI Generate" button in `QuestionSlide.vue`.
2.  **AI Dialog**: Reuse or adapt `QuAIGeneratorDialog.vue` to allow generating questions.
    *   Support an "emit" mode (`emitDataOnly` prop) where it returns the generated questions instead of posting to the server.
    *   Allow passing `subjectName` and `gradeName` directly (optional) or handle missing `subjects` list gracefully.
3.  **Default Values**: Pre-fill the AI prompt with the current Lesson's subject and grade.
4.  **Integration**:
    *   When questions are generated and "inserted", add them to the `QuestionSlide`'s question list.
    *   Map the AI response format (snake_case like `question_text`) to the `QuestionSlide` format (often matches but check for differences like `text` vs `question_text`).

## Implementation Plan

### 1. Update `QuAIGeneratorDialog.vue`
- Add `emitDataOnly` prop (Boolean, default false).
- Add `subjectName` prop (String, optional) as a fallback if `subjects` array is not provided.
- Update `generatePrompt` to use `subjectName` if available.
- Update `bulkInsert` to check `emitDataOnly`:
    - If true, emit `imported` event with the questions and close the dialog.
    - If false, proceed with existing router post.

### 2. Update `lesson_presentation.vue`
- Pass `defaultContext` to the dynamic slide component.
    - `<component ... :context="defaultContext" />`

### 3. Update `QuestionSlide.vue`
- Import `QuAIGeneratorDialog`.
- Add `context` prop to receive the passed context.
- Add "AI Generate" button next to "Add Question".
- Implement `handleAIQuestions` method:
    - Map returned questions to the component's internal structure.
        - `id`: Generate new ID.
        - `type`: `mcq` -> `single_choice` (check `EnhancedQuestionEditor` types).
        - `text`: `question_text`.
        - `options`: Transform object/array to the needed format.
        - `correct_answer`: Ensure compatibility.
    - Push to `modelValue.questions`.
- Add the `QuAIGeneratorDialog` to the template.

## Verification
- AI Prompt generation works with defaults.
- Questions are generated and validated.
- "Insert" adds them to the slide editor.
- Editing works on the newly added questions.
