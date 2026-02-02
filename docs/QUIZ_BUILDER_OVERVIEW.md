# Quiz Builder & Management System

## Overview
The **Quiz Builder** (`QuizBuilder.vue`) is a comprehensive interface for creating and editing quizzes. It allows teachers to assemble quizzes by selecting questions from the Question Bank, configuring quiz settings, and previewing the student experience.

**Location:** `resources/js/Pages/QuizManagement/QuizBuilder.vue`
**Route:** `/quizzes/{id}/edit` or `/quizzes/create`

## Architecture

### Component Structure
```
QuizManagement/
├── QuizDashboard.vue       # Main listing of quizzes
├── QuizBuilder.vue         # The Editor (3-panel layout)
├── QuizPreview.vue         # Student preview mode
└── components/
    ├── QuizNavigation.vue  # Tab bar navigation
    ├── QuestionCard.vue    # Reusable card for question display
    └── QuizStats.vue       # Statistics display components
```

### Data Flow
1. **Fetch Questions**: Loads available questions from `/api/questions` (filterable by grade, subject, type).
2. **Fetch Quiz**: Loads existing quiz data from `/api/quizzes/{id}`.
3. **Assembly**: Users drag/drop questions from **Pool** (Left) to **Canvas** (Center).
4. **Save**: Posts combined data (Metadata + Question IDs) to `/api/quizzes`.

## Features

### 1. Three-Panel Layout
- **Left Panel (Question Pool)**: 
  - Searchable/Filterable list of questions.
  - Filters: Type, Difficulty, Subject (implied by context).
  - Drag-and-drop source.
- **Center Panel (Canvas)**:
  - Drag-and-drop target.
  - Reorder questions.
  - Remove questions.
  - "Shuffle" button (randomizes current order).
- **Right Panel (Settings)**:
  - Quiz Name & Description.
  - Time Limit.
  - Status (Draft/Active/Archived).
  - **Advanced Options**: 
    - Shuffle Questions (Execution time).
    - Shuffle Options.
    - Allow Review.
  - **Live Stats**: Question count, Est. Time, Avg. Difficulty.

### 2. Interactions
- **Drag & Drop**: Uses `vuedraggable` for smooth sorting and addition.
- **Preview**: 
  - **Quiz Preview**: Full-screen modal simulating the quiz.
  - **Question Preview**: Individual question inspection.
- **Validation**: Prevents saving without Title or Questions.

### 3. Integration
- **Question Bank**: Directly pulls active questions.
- **Dashboard**: `QuizDashboard.vue` provides the entry point, listing quizzes with analytics (Active vs Total, Avg Completion).

## Missing Features / Gap Analysis

Based on the current implementation (`QuizBuilder.vue`), the following standard features appear to be **missing** or could be improved:

1. **Randomized Question Set**: 
   - *Current*: You select specific questions.
   - *Missing*: Option to "Pick 10 random questions from Topic X" dynamically per attempt.

2. **Points Configuration**:
   - *Current*: Questions seem to have inherent value (or default).
   - *Missing*: Ability to override points per question *within* this specific quiz (e.g., "Make this hard question worth 5 points").

3. **Sections / Pagination**:
   - *Current*: Flat list of questions.
   - *Missing*: Ability to group questions into "Page 1", "Section A", or add instruction blocks between questions.

4. **Bulk Addition**:
   - *Current*: Click or Drag one-by-one.
   - *Missing*: "Add All Filtered" or multi-select in the pool to add multiple questions at once.

5. **Version History**:
   - *Current*: Overwrites existing quiz.
   - *Missing*: Track changes or revert to previous versions of the quiz.

6. **Printing**:
   - *Current*: Screen only.
   - *Missing*: "Export to PDF" for offline testing.

## API Endpoints Used
- `GET /api/questions` (Pool)
- `GET /api/quizzes/{id}` (Load)
- `POST /api/quizzes` (Create)
- `PUT /api/quizzes/{id}` (Update)
- `GET /api/grades` & `/api/subjects` (Metadata)
