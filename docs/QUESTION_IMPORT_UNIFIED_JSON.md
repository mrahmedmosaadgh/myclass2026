# Question Import - Unified JSON Format

## Overview
All import methods (File Upload, Excel Paste, JSON Paste) now convert to JSON format on the frontend before sending to the backend. This simplifies the backend to only handle one format.

## Architecture

### Frontend Flow
```
User Input (File/Excel/JSON) → Parse to JSON → Send to Backend → Preview/Import
```

### Backend Flow
```
Receive JSON Array → Validate → Create Questions → Return Results
```

## Changes Made

### 1. Backend Controller (`app/Http/Controllers/QuestionController.php`)

**Before:** Accepted file uploads, JSON data, and Excel paste separately
**After:** Only accepts JSON array with metadata

```php
public function import(Request $request): JsonResponse
{
    // Validates:
    // - questions: array of question objects
    // - question_type_id, grade_id, subject_id, etc. (metadata)
    // - preview: boolean (true = validate only, false = create records)
    
    // Returns:
    // - total_rows, successful, failed, errors
    // - preview data if preview=true
}
```

### 2. Import Service (`app/Services/QuestionImportService.php`)

**New Method:** `importFromArray()`
- Accepts array of questions with default metadata
- Supports preview mode (validation only)
- Handles nullable grade/subject/topic IDs

**Updated Method:** `createQuestionWithOptions()`
- Now handles both ID and name formats for grade/subject/topic
- Supports nullable metadata fields

### 3. Frontend (`resources/js/Pages/QuestionManagement/QuestionImport.vue`)

**File Upload:**
- Reads file content
- Parses CSV to JSON array
- Sends JSON to backend

**Excel Paste:**
- Parses tab/comma-separated data
- Converts to JSON array
- Sends JSON to backend

**JSON Paste:**
- Validates JSON format
- Sends directly to backend

## API Request Format

### Preview Request
```json
POST /api/questions/import
{
  "questions": [
    {
      "question_text": "What is 2+2?",
      "option_a": "2",
      "option_b": "3",
      "option_c": "4",
      "option_d": "5",
      "correct_answer": "C"
    }
  ],
  "question_type_id": 1,
  "grade_id": 5,
  "subject_id": 2,
  "difficulty": "Medium",
  "bloom_level": 3,
  "status": "draft",
  "preview": true
}
```

### Import Request
```json
POST /api/questions/import
{
  "questions": [...],
  "question_type_id": 1,
  "grade_id": 5,
  "subject_id": 2,
  "difficulty": "Medium",
  "bloom_level": 3,
  "status": "draft",
  "preview": false
}
```

## API Response Format

### Success Response
```json
{
  "success": true,
  "data": {
    "total_rows": 10,
    "successful": 10,
    "failed": 0,
    "errors": [],
    "valid": 10,
    "warnings": 0
  },
  "message": "Successfully imported 10 question(s)."
}
```

### Partial Success Response
```json
{
  "success": true,
  "data": {
    "total_rows": 10,
    "successful": 8,
    "failed": 2,
    "errors": [
      {
        "row": 3,
        "message": "Correct answer 'E' not found in options",
        "data": {...}
      }
    ],
    "valid": 8,
    "warnings": 0
  },
  "message": "Imported 8 question(s) successfully. 2 question(s) failed."
}
```

### Error Response
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "questions": ["The questions field is required."],
    "question_type_id": ["The question type id field is required."]
  }
}
```

## Benefits

1. **Simplified Backend:** Only one import method to maintain
2. **Consistent Validation:** Same validation logic for all import types
3. **Better Error Handling:** Unified error format
4. **Easier Testing:** Single endpoint to test
5. **Frontend Flexibility:** Can add new import methods without backend changes

## Supported Question Fields

### Required
- `question_text`: The question text (max 5000 chars)

### Optional (for option-based questions)
- `option_a`, `option_b`, `option_c`, `option_d`, `option_e`, `option_f`: Answer options
- `correct_answer`: Correct answer key(s), e.g., "A" or "A,C" for multi-select

### Optional (metadata)
- `hints`: Semicolon-separated hints or array
- `explanation`: Explanation text or object

### Default Metadata (applied to all questions)
- `question_type_id`: Question type (required)
- `grade_id`: Grade level
- `subject_id`: Subject
- `topic_id`: Topic
- `difficulty`: Easy, Medium, or Hard (converted to 1, 3, 5)
- `bloom_level`: 1-6
- `status`: draft, active, archived, or review

## Migration Notes

### Old Code (File Upload)
```javascript
const formData = new FormData();
formData.append('file', file);
await axios.post('/api/questions/import', formData);
```

### New Code (Unified JSON)
```javascript
const questions = await parseFileToJson(file);
await axios.post('/api/questions/import', {
  questions,
  ...metadata
});
```

## Testing

Test all three import methods:

1. **File Upload (CSV)**
   - Upload a CSV file
   - Verify it's parsed to JSON
   - Check preview shows correct data

2. **Excel Paste**
   - Copy cells from Excel
   - Paste into textarea
   - Verify parsing and preview

3. **JSON Paste**
   - Paste JSON array
   - Verify validation
   - Check import works

## Error Handling

The system handles:
- Invalid JSON format
- Missing required fields
- Invalid question types
- Missing options for option-based questions
- Invalid correct answers
- Database errors during creation

All errors include:
- Row number
- Error message
- Original data for debugging
