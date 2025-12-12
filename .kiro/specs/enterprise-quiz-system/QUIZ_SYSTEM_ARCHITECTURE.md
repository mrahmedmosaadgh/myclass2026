# Quiz System Architecture

## System Overview

The quiz system consists of two main parts:
1. **Question Management** - Individual questions with types, options, and metadata
2. **Quiz Collections** - Curated sets of questions that can be assigned to lessons

## Database Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                     QUESTION MANAGEMENT                          │
└─────────────────────────────────────────────────────────────────┘

┌──────────────────┐
│ question_types   │
│ ──────────────── │
│ • id             │
│ • slug           │◄─────┐
│ • name           │      │
│ • has_options    │      │
│ • supports_hints │      │
└──────────────────┘      │
                          │
┌──────────────────┐      │
│ questions        │      │
│ ──────────────── │      │
│ • id             │      │
│ • question_type_id├─────┘
│ • question_text  │
│ • grade_level_id │
│ • subject_id     │
│ • topic_id       │
│ • bloom_level    │
│ • difficulty     │
│ • hints (JSON)   │
│ • explanation    │
│ • status         │
│ • analytics...   │
└──────────────────┘
         │
         │ 1:N
         ▼
┌──────────────────┐
│ question_options │
│ ──────────────── │
│ • id             │
│ • question_id    │
│ • option_key     │
│ • option_text    │
│ • is_correct     │
│ • order_index    │
└──────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│                     QUIZ COLLECTIONS (NEW)                       │
└─────────────────────────────────────────────────────────────────┘

┌──────────────────┐
│ quizzes          │
│ ──────────────── │
│ • id             │
│ • name           │
│ • description    │
│ • school_id      │
│ • grade_id       │
│ • subject_id     │
│ • created_by_id  │
│ • time_limit     │
│ • shuffle_*      │
│ • allow_review   │
│ • status         │
└──────────────────┘
         │
         │ N:M (via quiz_question)
         ▼
┌──────────────────┐
│ quiz_question    │
│ ──────────────── │
│ • quiz_id        │
│ • question_id    │
│ • order_index    │
└──────────────────┘
         │
         └──────────► questions (reuses existing questions)

┌─────────────────────────────────────────────────────────────────┐
│                     STUDENT ATTEMPTS                             │
└─────────────────────────────────────────────────────────────────┘

┌──────────────────┐
│ quiz_attempts    │
│ ──────────────── │
│ • id             │
│ • user_id        │
│ • quiz_id        │◄──── Links to quiz collection
│ • started_at     │
│ • completed_at   │
│ • total_questions│
│ • correct_answers│
│ • percentage     │
└──────────────────┘
         │
         │ 1:N
         ▼
┌──────────────────────┐
│ quiz_attempt_answers │
│ ──────────────────── │
│ • attempt_id         │
│ • question_id        │
│ • selected_option_id │
│ • is_correct         │
│ • time_spent_sec     │
└──────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│                     LESSON INTEGRATION                           │
└─────────────────────────────────────────────────────────────────┘

┌──────────────────────┐
│ lesson_presentations │
│ ──────────────────── │
│ • id                 │
│ • name               │
│ • quiz_id            │◄──── Links to quiz collection
│ • grade_id           │
│ • ...                │
└──────────────────────┘
```

## Data Flow

### Creating a Quiz

```
Teacher → Quiz Selector → Create Quiz Modal
                              ↓
                         POST /api/quizzes
                              ↓
                         QuizController
                              ↓
                         Quiz Model
                              ↓
                    [quizzes table created]
                              ↓
                    [quiz_question pivot records]
                              ↓
                         Return quiz data
                              ↓
                    Update lesson.quiz_id
```

### Assigning Quiz to Lesson

```
Teacher → Lesson Editor → Quiz Selector Dropdown
                              ↓
                         GET /api/quizzes
                              ↓
                    Display available quizzes
                              ↓
                    Teacher selects quiz
                              ↓
                    lesson.quiz_id = selected_quiz_id
                              ↓
                         Save Lesson
```

### Student Taking Quiz

```
Student → Lesson Player → Quiz Section
                              ↓
                    GET /api/quizzes/{id}
                              ↓
                    Display quiz info
                              ↓
                    Click "Start Quiz"
                              ↓
                    Launch QuizEngine
                              ↓
                    Load quiz questions
                              ↓
                    Student answers
                              ↓
                    POST /api/quiz-attempts
                              ↓
                    Track answers
                              ↓
                    Complete quiz
                              ↓
                    Save results
```

## Key Relationships

### Quiz → Questions (Many-to-Many)
- A quiz contains multiple questions
- A question can be in multiple quizzes
- Order is maintained via `order_index` in pivot table

### Quiz → Lesson (One-to-Many)
- A quiz can be assigned to multiple lessons
- A lesson has one quiz (or none)
- Stored as `quiz_id` in `lesson_presentations` table

### Quiz → Attempts (One-to-Many)
- A quiz can have multiple attempts (different students, different times)
- An attempt belongs to one quiz
- Tracks student performance

## Benefits of This Architecture

1. **Reusability** - Questions can be reused across multiple quizzes
2. **Flexibility** - Quizzes can be assigned to multiple lessons
3. **Maintainability** - Update a question once, affects all quizzes using it
4. **Analytics** - Track performance at both question and quiz level
5. **Scalability** - Easy to add new question types or quiz features

## Example Usage

### Creating a Math Quiz

```javascript
// 1. Create quiz
POST /api/quizzes
{
  name: "Algebra Basics Quiz",
  description: "Test your algebra skills",
  school_id: 1,
  grade_id: 8,
  subject_id: 2, // Math
  time_limit_minutes: 30,
  shuffle_questions: true,
  question_ids: [101, 102, 103, 104, 105] // Existing questions
}

// 2. Assign to lesson
lesson.quiz_id = quiz.id

// 3. Student takes quiz
GET /api/quizzes/1
// Returns quiz with all questions loaded

// 4. Track attempt
POST /api/quiz-attempts
{
  quiz_id: 1,
  user_id: 42
}
```

## Future Enhancements

1. **Quiz Builder UI** - Drag-and-drop interface for adding questions
2. **Question Preview** - Preview questions before adding to quiz
3. **Quiz Templates** - Save quiz configurations as templates
4. **Adaptive Quizzes** - Adjust difficulty based on student performance
5. **Quiz Analytics Dashboard** - Visualize quiz performance metrics
6. **Question Pools** - Randomly select N questions from a pool
7. **Quiz Versioning** - Track changes to quizzes over time
