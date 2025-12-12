# Quiz System API Quick Reference

## Base URL
All endpoints are prefixed with `/api/quiz`

## Authentication
All endpoints require authentication using Laravel Sanctum:
```
Authorization: Bearer {token}
```

---

## Quick Start: Taking a Quiz

### 1. Fetch Quiz Questions
```http
GET /api/quiz/fetch?subject_id=1&grade_level_id=5&limit=10&shuffle=true
```

**Response:**
```json
{
  "success": true,
  "data": {
    "questions": [
      {
        "id": 1,
        "questionNumber": 1,
        "questionTypeId": 1,
        "questionType": {
          "id": 1,
          "slug": "multiple_choice",
          "name": "Multiple Choice",
          "hasOptions": true,
          "supportsHints": true,
          "supportsExplanation": true
        },
        "question": "What is 2 + 2?",
        "answerOptions": [
          {
            "id": 1,
            "text": "3",
            "isCorrect": false,
            "distractorStrength": 0.3
          },
          {
            "id": 2,
            "text": "4",
            "isCorrect": true,
            "distractorStrength": null
          }
        ],
        "explanation": {
          "text": "2 + 2 equals 4",
          "revealed_after_attempt": true
        },
        "hints": ["Think about basic addition"],
        "bloomLevel": 1,
        "difficultyLevel": 1,
        "estimatedTimeSec": 30
      }
    ],
    "total": 10
  }
}
```

### 2. Start Quiz Attempt
```http
POST /api/quiz/attempts
Content-Type: application/json

{
  "question_ids": [1, 2, 3, 4, 5],
  "metadata": {
    "source": "practice_quiz"
  }
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "attempt_id": 123,
    "started_at": "2025-01-15T10:30:00Z",
    "total_questions": 5
  }
}
```

### 3. Submit Answers
```http
POST /api/quiz/attempts/123/answers
Content-Type: application/json

{
  "question_id": 1,
  "selected_option_id": 2,
  "time_spent_sec": 15
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "answer_id": 456,
    "is_correct": true,
    "answered_at": "2025-01-15T10:30:15Z"
  }
}
```

### 4. Complete Quiz
```http
PUT /api/quiz/attempts/123/complete
```

**Response:**
```json
{
  "success": true,
  "data": {
    "attempt_id": 123,
    "completed_at": "2025-01-15T10:35:00Z",
    "total_questions": 5,
    "correct_answers": 4,
    "percentage": 80.00
  }
}
```

### 5. Get Results
```http
GET /api/quiz/attempts/123/results
```

**Response:**
```json
{
  "success": true,
  "data": {
    "attempt_id": 123,
    "total_questions": 5,
    "correct_answers": 4,
    "percentage": 80.00,
    "started_at": "2025-01-15T10:30:00Z",
    "completed_at": "2025-01-15T10:35:00Z",
    "answers": [
      {
        "question_id": 1,
        "question_text": "What is 2 + 2?",
        "selected_option_id": 2,
        "selected_text": "4",
        "is_correct": true,
        "correct_option_text": "4",
        "time_spent_sec": 15,
        "answered_at": "2025-01-15T10:30:15Z"
      }
    ],
    "metadata": {
      "question_ids": [1, 2, 3, 4, 5],
      "source": "practice_quiz"
    }
  }
}
```

---

## Question Management

### List Questions
```http
GET /api/quiz/questions?subject_id=1&status=active&per_page=20&page=1
```

### Create Question
```http
POST /api/quiz/questions
Content-Type: application/json

{
  "question_type_id": 1,
  "question_text": "What is the capital of France?",
  "grade_level_id": 5,
  "subject_id": 2,
  "topic_id": 10,
  "bloom_level": 1,
  "difficulty_level": 2,
  "estimated_time_sec": 30,
  "status": "active",
  "hints": ["It's a European city"],
  "explanation": {
    "text": "Paris is the capital and largest city of France",
    "revealed_after_attempt": true
  },
  "options": [
    {
      "option_key": "A",
      "option_text": "London",
      "is_correct": false,
      "distractor_strength": 0.4,
      "order_index": 0
    },
    {
      "option_key": "B",
      "option_text": "Paris",
      "is_correct": true,
      "order_index": 1
    },
    {
      "option_key": "C",
      "option_text": "Berlin",
      "is_correct": false,
      "distractor_strength": 0.3,
      "order_index": 2
    }
  ]
}
```

### Update Question
```http
PUT /api/quiz/questions/1
Content-Type: application/json

{
  "question_text": "What is the capital city of France?",
  "difficulty_level": 1
}
```

### Delete Question
```http
DELETE /api/quiz/questions/1
```

---

## Filter Options

### Quiz Fetch Filters
- `question_ids[]` - Specific question IDs
- `grade_level_id` - Grade level
- `subject_id` - Subject
- `topic_id` - Topic
- `difficulty_level` - 1-5
- `bloom_level` - 1-6
- `status` - draft, active, archived, review
- `limit` - 1-100 (default: 20)
- `shuffle` - true/false

### Question List Filters
- `question_type_id` - Question type
- `grade_level_id` - Grade level
- `subject_id` - Subject
- `topic_id` - Topic
- `difficulty_level` - 1-5
- `bloom_level` - 1-6
- `status` - draft, active, archived, review
- `author_id` - Question author
- `search` - Search in question text
- `per_page` - 1-100 (default: 20)
- `page` - Page number

---

## Error Responses

All errors follow this format:
```json
{
  "success": false,
  "error": {
    "code": "ERROR_CODE",
    "message": "Human-readable message",
    "details": {},
    "timestamp": "2025-01-15T10:30:00Z"
  }
}
```

### Common Error Codes
- `VALIDATION_ERROR` (422) - Invalid input
- `UNAUTHORIZED` (403) - Insufficient permissions
- `UNAUTHORIZED_ACCESS` (403) - Not your resource
- `NOT_FOUND` (404) - Resource doesn't exist
- `ATTEMPT_COMPLETED` (422) - Quiz already completed
- `INVALID_QUESTION` (422) - Question not in quiz
- `NO_CORRECT_OPTION` (422) - Missing correct answer
- `QUESTION_IN_USE` (422) - Cannot delete used question

---

## Frontend Integration Example

```typescript
// Fetch quiz questions
const fetchQuiz = async (filters: QuizFilters) => {
  const params = new URLSearchParams(filters);
  const response = await fetch(`/api/quiz/fetch?${params}`, {
    headers: {
      'Authorization': `Bearer ${token}`,
      'Accept': 'application/json'
    }
  });
  return response.json();
};

// Start quiz attempt
const startQuiz = async (questionIds: number[]) => {
  const response = await fetch('/api/quiz/attempts', {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json',
      'Accept': 'application/json'
    },
    body: JSON.stringify({ question_ids: questionIds })
  });
  return response.json();
};

// Submit answer
const submitAnswer = async (attemptId: number, answer: Answer) => {
  const response = await fetch(`/api/quiz/attempts/${attemptId}/answers`, {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json',
      'Accept': 'application/json'
    },
    body: JSON.stringify(answer)
  });
  return response.json();
};

// Complete quiz
const completeQuiz = async (attemptId: number) => {
  const response = await fetch(`/api/quiz/attempts/${attemptId}/complete`, {
    method: 'PUT',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Accept': 'application/json'
    }
  });
  return response.json();
};

// Get results
const getResults = async (attemptId: number) => {
  const response = await fetch(`/api/quiz/attempts/${attemptId}/results`, {
    headers: {
      'Authorization': `Bearer ${token}`,
      'Accept': 'application/json'
    }
  });
  return response.json();
};
```

---

## Rate Limiting

Consider implementing rate limiting for:
- Quiz attempts: 10 per hour per user
- Answer submissions: 100 per hour per user
- Question creation: 50 per hour per user

---

## Best Practices

1. **Always validate on frontend** before sending requests
2. **Handle errors gracefully** with user-friendly messages
3. **Cache quiz questions** to reduce API calls
4. **Track time spent** accurately on frontend
5. **Implement retry logic** for network failures
6. **Use TypeScript interfaces** for type safety
7. **Log important events** for debugging
8. **Test with various question types** before production

---

## Security Notes

- All endpoints require authentication
- Users can only access their own quiz attempts
- Only teachers/admins can create/edit questions
- Only authors/admins can delete questions
- Questions used in attempts cannot be deleted
- All inputs are validated and sanitized
- Database operations use transactions
- Sensitive data is not exposed in errors (when debug=false)
