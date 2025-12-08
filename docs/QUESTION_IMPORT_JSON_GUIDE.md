# Question Import - JSON Format Guide

## Overview

You can import questions using JSON format by sending a POST request to `/api/questions/import` with a JSON payload instead of a file upload.

## JSON Import Format

### Basic Structure

```json
{
  "questions": [
    {
      "question_type": "multiple_choice",
      "question_text": "What is 2+2?",
      "grade_id": 3,
      "subject_id": 2,
      "topic_id": 15,
      "difficulty_level": 1,
      "bloom_level": 2,
      "estimated_time_sec": 60,
      "status": "active",
      "options": [
        {
          "option_key": "A",
          "option_text": "2",
          "is_correct": false,
          "order_index": 0
        },
        {
          "option_key": "B",
          "option_text": "3",
          "is_correct": false,
          "order_index": 1
        },
        {
          "option_key": "C",
          "option_text": "4",
          "is_correct": true,
          "order_index": 2
        },
        {
          "option_key": "D",
          "option_text": "5",
          "is_correct": false,
          "order_index": 3
        }
      ],
      "hints": [
        "Think about basic addition",
        "What do you get when you add two and two?"
      ],
      "explanation": {
        "text": "2 + 2 equals 4. This is a basic addition problem.",
        "revealed_after_attempt": 1
      }
    }
  ]
}
```

## Field Descriptions

### Required Fields

| Field | Type | Description | Example |
|-------|------|-------------|---------|
| `question_type` | string | Question type slug | `"multiple_choice"`, `"true_false"`, `"short_answer"` |
| `question_text` | string | The question text | `"What is the capital of France?"` |

### Optional Fields

| Field | Type | Description | Example |
|-------|------|-------------|---------|
| `grade_id` | integer | Grade level ID | `3` |
| `subject_id` | integer | Subject ID | `2` |
| `topic_id` | integer | Topic ID | `15` |
| `difficulty_level` | integer | Difficulty (1-5) | `3` |
| `bloom_level` | integer | Bloom's taxonomy level (1-6) | `2` |
| `estimated_time_sec` | integer | Estimated time in seconds | `60` |
| `status` | string | Question status | `"draft"`, `"active"`, `"archived"`, `"review"` |
| `hints` | array | Array of hint strings | `["Hint 1", "Hint 2"]` |
| `explanation` | object | Explanation object | See below |
| `options` | array | Array of option objects | See below |

### Option Object Structure

For questions that have options (multiple choice, true/false, multi-select):

```json
{
  "option_key": "A",
  "option_text": "Paris",
  "is_correct": true,
  "order_index": 0
}
```

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `option_key` | string | Yes | Option identifier (A, B, C, D, etc.) |
| `option_text` | string | Yes | The option text |
| `is_correct` | boolean | Yes | Whether this is a correct answer |
| `order_index` | integer | Yes | Display order (0-based) |

### Explanation Object Structure

```json
{
  "text": "Paris is the capital and largest city of France.",
  "revealed_after_attempt": 1
}
```

| Field | Type | Description |
|-------|------|-------------|
| `text` | string | The explanation text |
| `revealed_after_attempt` | integer | When to reveal (1 = after first attempt) |

## Question Types

Available question type slugs:

- `multiple_choice` - Multiple choice (single answer)
- `multi_select` - Multiple choice (multiple answers)
- `true_false` - True/False question
- `fill_blank` - Fill in the blank
- `short_answer` - Short answer
- `essay` - Essay question
- `matching` - Matching question

## Complete Examples

### Example 1: Multiple Choice Question

```json
{
  "questions": [
    {
      "question_type": "multiple_choice",
      "question_text": "What is the capital of France?",
      "grade_id": 5,
      "subject_id": 3,
      "difficulty_level": 2,
      "bloom_level": 1,
      "estimated_time_sec": 30,
      "status": "active",
      "options": [
        {
          "option_key": "A",
          "option_text": "Paris",
          "is_correct": true,
          "order_index": 0
        },
        {
          "option_key": "B",
          "option_text": "London",
          "is_correct": false,
          "order_index": 1
        },
        {
          "option_key": "C",
          "option_text": "Berlin",
          "is_correct": false,
          "order_index": 2
        },
        {
          "option_key": "D",
          "option_text": "Madrid",
          "is_correct": false,
          "order_index": 3
        }
      ],
      "hints": [
        "Think about France",
        "It's a city known for the Eiffel Tower"
      ],
      "explanation": {
        "text": "Paris is the capital and largest city of France, known for landmarks like the Eiffel Tower and the Louvre Museum.",
        "revealed_after_attempt": 1
      }
    }
  ]
}
```

### Example 2: True/False Question

```json
{
  "questions": [
    {
      "question_type": "true_false",
      "question_text": "The Earth is flat.",
      "grade_id": 4,
      "subject_id": 5,
      "difficulty_level": 1,
      "bloom_level": 1,
      "status": "active",
      "options": [
        {
          "option_key": "A",
          "option_text": "True",
          "is_correct": false,
          "order_index": 0
        },
        {
          "option_key": "B",
          "option_text": "False",
          "is_correct": true,
          "order_index": 1
        }
      ],
      "explanation": {
        "text": "The Earth is not flat. It is an oblate spheroid, meaning it's roughly spherical but slightly flattened at the poles.",
        "revealed_after_attempt": 1
      }
    }
  ]
}
```

### Example 3: Short Answer Question

```json
{
  "questions": [
    {
      "question_type": "short_answer",
      "question_text": "What is the chemical symbol for water?",
      "grade_id": 6,
      "subject_id": 5,
      "difficulty_level": 2,
      "bloom_level": 1,
      "estimated_time_sec": 45,
      "status": "active",
      "hints": [
        "It consists of hydrogen and oxygen",
        "The formula has 2 hydrogen atoms and 1 oxygen atom"
      ],
      "explanation": {
        "text": "The chemical symbol for water is H₂O, which represents two hydrogen atoms bonded to one oxygen atom.",
        "revealed_after_attempt": 1
      }
    }
  ]
}
```

### Example 4: Multi-Select Question

```json
{
  "questions": [
    {
      "question_type": "multi_select",
      "question_text": "Which of the following are prime numbers?",
      "grade_id": 7,
      "subject_id": 2,
      "difficulty_level": 3,
      "bloom_level": 3,
      "estimated_time_sec": 90,
      "status": "active",
      "options": [
        {
          "option_key": "A",
          "option_text": "2",
          "is_correct": true,
          "order_index": 0
        },
        {
          "option_key": "B",
          "option_text": "4",
          "is_correct": false,
          "order_index": 1
        },
        {
          "option_key": "C",
          "option_text": "7",
          "is_correct": true,
          "order_index": 2
        },
        {
          "option_key": "D",
          "option_text": "9",
          "is_correct": false,
          "order_index": 3
        }
      ],
      "hints": [
        "Prime numbers are only divisible by 1 and themselves",
        "Check each number for divisibility"
      ],
      "explanation": {
        "text": "2 and 7 are prime numbers. 4 is divisible by 2, and 9 is divisible by 3, so they are not prime.",
        "revealed_after_attempt": 1
      }
    }
  ]
}
```

### Example 5: Bulk Import (Multiple Questions)

```json
{
  "questions": [
    {
      "question_type": "multiple_choice",
      "question_text": "What is 5 × 6?",
      "grade_id": 3,
      "subject_id": 2,
      "difficulty_level": 2,
      "status": "active",
      "options": [
        {"option_key": "A", "option_text": "25", "is_correct": false, "order_index": 0},
        {"option_key": "B", "option_text": "30", "is_correct": true, "order_index": 1},
        {"option_key": "C", "option_text": "35", "is_correct": false, "order_index": 2},
        {"option_key": "D", "option_text": "40", "is_correct": false, "order_index": 3}
      ]
    },
    {
      "question_type": "multiple_choice",
      "question_text": "What is 12 ÷ 3?",
      "grade_id": 3,
      "subject_id": 2,
      "difficulty_level": 2,
      "status": "active",
      "options": [
        {"option_key": "A", "option_text": "3", "is_correct": false, "order_index": 0},
        {"option_key": "B", "option_text": "4", "is_correct": true, "order_index": 1},
        {"option_key": "C", "option_text": "5", "is_correct": false, "order_index": 2},
        {"option_key": "D", "option_text": "6", "is_correct": false, "order_index": 3}
      ]
    },
    {
      "question_type": "true_false",
      "question_text": "10 is an even number.",
      "grade_id": 3,
      "subject_id": 2,
      "difficulty_level": 1,
      "status": "active",
      "options": [
        {"option_key": "A", "option_text": "True", "is_correct": true, "order_index": 0},
        {"option_key": "B", "option_text": "False", "is_correct": false, "order_index": 1}
      ]
    }
  ]
}
```

## How to Import

### Using cURL

```bash
curl -X POST http://your-domain.com/api/questions/import \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d @questions.json
```

### Using JavaScript/Axios

```javascript
const questions = {
  questions: [
    {
      question_type: "multiple_choice",
      question_text: "What is 2+2?",
      // ... rest of question data
    }
  ]
};

axios.post('/api/questions/import', questions, {
  headers: {
    'Content-Type': 'application/json'
  }
})
.then(response => {
  console.log('Import successful:', response.data);
})
.catch(error => {
  console.error('Import failed:', error.response.data);
});
```

## Response Format

### Success Response

```json
{
  "success": true,
  "data": {
    "imported": 5,
    "failed": 0,
    "errors": []
  },
  "message": "Successfully imported 5 questions"
}
```

### Partial Success Response

```json
{
  "success": true,
  "data": {
    "imported": 3,
    "failed": 2,
    "errors": [
      {
        "row": 2,
        "message": "Invalid question type: invalid_type"
      },
      {
        "row": 4,
        "message": "Missing required field: question_text"
      }
    ]
  },
  "message": "Imported 3 questions. 2 failed."
}
```

### Error Response

```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "Invalid request parameters",
    "details": {
      "questions": ["The questions field is required."]
    },
    "timestamp": "2025-11-25T11:31:21+00:00"
  }
}
```

## Tips

1. **Validate JSON:** Use a JSON validator before importing
2. **Start Small:** Test with 1-2 questions first
3. **Check IDs:** Ensure grade_id, subject_id, and topic_id exist in your database
4. **Question Types:** Verify question type slugs match your database
5. **Options:** Include options for multiple_choice, multi_select, and true_false types
6. **Status:** Use "draft" for testing, "active" for production
7. **Batch Size:** Import in batches of 50-100 questions for best performance

## Common Errors

| Error | Cause | Solution |
|-------|-------|----------|
| "Missing required columns" | Using CSV column names in JSON | Use JSON field names (question_text, not Question Text) |
| "Invalid question type" | Wrong type slug | Check available types: multiple_choice, true_false, etc. |
| "Grade/Subject not found" | Invalid ID | Verify IDs exist in your database |
| "At least one correct answer required" | No is_correct: true | Mark at least one option as correct |
| "Minimum 2 options required" | Too few options | Add at least 2 options for MCQ questions |

## Need Help?

- Check the API documentation
- Review example JSON files
- Contact support for assistance
