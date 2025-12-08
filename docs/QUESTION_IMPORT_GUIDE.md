# Question Import Guide

## Overview

The Question Import feature allows teachers and administrators to bulk import quiz questions from CSV or Excel files. This guide explains the file format, validation rules, and best practices for importing questions.

## Supported File Formats

- **CSV** (.csv, .txt)
- **Excel** (.xlsx, .xls)

Maximum file size: 10MB

## File Structure

### Required Columns

The following columns are **required** in your import file:

1. `question_type` - The type of question (see Question Types below)
2. `grade_level` - The grade level for this question
3. `subject` - The subject area
4. `question_text` - The actual question text (max 5000 characters)

### Optional Columns

5. `topic` - Specific topic within the subject
6. `bloom_level` - Bloom's Taxonomy level (1-6)
7. `difficulty_level` - Difficulty rating (1-5)
8. `estimated_time_sec` - Estimated time to answer in seconds
9. `option_a` - First answer option (required for option-based questions)
10. `option_b` - Second answer option (required for option-based questions)
11. `option_c` - Third answer option
12. `option_d` - Fourth answer option
13. `option_e` - Fifth answer option
14. `option_f` - Sixth answer option
15. `correct_answer` - The correct answer(s) - see format below
16. `hints` - Semicolon-separated list of hints
17. `explanation` - Explanation text shown after answering
18. `status` - Question status: draft, active, archived, or review (default: draft)

## Question Types

The `question_type` column must contain one of the following slugs:

- `multiple_choice` - Single correct answer from multiple options
- `multi_select` - Multiple correct answers from multiple options
- `true_false` - True or False question (only 2 options)
- `fill_blank` - Fill in the blank (text input)
- `short_answer` - Short text answer
- `essay` - Long text answer

## Correct Answer Format

### Single Correct Answer (multiple_choice, true_false)
Use a single letter corresponding to the option:
```
A
```

### Multiple Correct Answers (multi_select)
Use comma-separated letters:
```
A,C,D
```

## Hints Format

Multiple hints should be separated by semicolons:
```
First hint here;Second hint here;Third hint here
```

## Bloom's Taxonomy Levels

1. Remember - Recall facts and basic concepts
2. Understand - Explain ideas or concepts
3. Apply - Use information in new situations
4. Analyze - Draw connections among ideas
5. Evaluate - Justify a decision or course of action
6. Create - Produce new or original work

## Difficulty Levels

1. Very Easy
2. Easy
3. Medium
4. Hard
5. Very Hard

## Example CSV File

```csv
question_type,grade_level,subject,topic,bloom_level,difficulty_level,estimated_time_sec,question_text,option_a,option_b,option_c,option_d,correct_answer,hints,explanation,status
multiple_choice,Grade 10,Mathematics,Algebra,3,2,120,"What is the solution to the equation 2x + 5 = 15?",x = 5,x = 10,x = 7.5,x = 2.5,A,"Isolate the variable x;Subtract 5 from both sides","To solve 2x + 5 = 15, first subtract 5 from both sides to get 2x = 10, then divide both sides by 2 to get x = 5.",active
true_false,Grade 10,Mathematics,Geometry,2,1,60,"The sum of angles in a triangle is always 180 degrees.",True,False,,,A,,"This is a fundamental property of triangles in Euclidean geometry.",active
multi_select,Grade 12,Chemistry,Organic Chemistry,5,4,240,"Which of the following are characteristics of alkanes?","Contain only single bonds","Are saturated hydrocarbons","Have the general formula CnH2n+2","Are highly reactive","A,B,C","Alkanes are saturated;They contain only C-C and C-H single bonds","Alkanes are saturated hydrocarbons with only single bonds and follow the formula CnH2n+2.",active
```

## API Endpoint

### Import Questions

**Endpoint:** `POST /api/questions/import`

**Authentication:** Required (Bearer token)

**Authorization:** Teacher or Admin role required

**Request:**
```http
POST /api/questions/import
Content-Type: multipart/form-data
Authorization: Bearer {token}

file: [CSV or Excel file]
```

**Success Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "total_rows": 10,
    "successful": 10,
    "failed": 0,
    "errors": []
  },
  "message": "Successfully imported 10 question(s)."
}
```

**Partial Success Response (207 Multi-Status):**
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
        "message": "Invalid question type 'multiple_choic'. Valid types: multiple_choice, multi_select, true_false, fill_blank, short_answer, essay",
        "data": { ... }
      },
      {
        "row": 7,
        "message": "Validation failed: The question text field is required.",
        "data": { ... }
      }
    ]
  },
  "message": "Imported 8 question(s) successfully. 2 question(s) failed. Please check the error details."
}
```

**Error Response (422 Unprocessable Entity):**
```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "Invalid request parameters",
    "details": {
      "file": ["The file field is required."]
    },
    "timestamp": "2024-01-15T10:30:00Z"
  }
}
```

## Validation Rules

### Question Text
- Required
- Maximum 5000 characters
- Can contain HTML for formatting

### Options (for option-based questions)
- Minimum 2 options required
- Maximum 1000 characters per option
- At least one option must be marked as correct

### Bloom Level
- Optional
- Must be between 1 and 6

### Difficulty Level
- Optional
- Must be between 1 and 5

### Estimated Time
- Optional
- Must be a positive integer (seconds)

### Status
- Must be one of: draft, active, archived, review
- Defaults to 'draft' if not specified

## Best Practices

1. **Start Small**: Test with a small file (5-10 questions) before importing large batches

2. **Use the Template**: Download and use the provided template file to ensure correct formatting

3. **Validate Data**: Check your data for:
   - Correct question type slugs
   - Valid option letters in correct_answer column
   - Proper semicolon separation in hints
   - No special characters that might break CSV parsing

4. **Grade Levels and Subjects**: 
   - The system will automatically create new grade levels and subjects if they don't exist
   - Use consistent naming (e.g., always "Grade 10" not "10th Grade")

5. **Review Imported Questions**: 
   - Import questions with status 'draft' first
   - Review them in the system before changing to 'active'

6. **Handle Errors**: 
   - The import will continue even if some rows fail
   - Review the error details to fix problematic rows
   - Re-import only the failed rows after fixing

7. **Character Encoding**: 
   - Use UTF-8 encoding for CSV files
   - This ensures special characters display correctly

## Troubleshooting

### Common Errors

**"Invalid question type"**
- Check that the question_type value exactly matches one of the supported slugs
- Question types are case-sensitive and use underscores (e.g., `multiple_choice`)

**"Question type requires options"**
- For multiple_choice, multi_select, and true_false questions, you must provide options
- Ensure option_a and option_b are filled at minimum

**"Correct answer not found in options"**
- The letter in correct_answer must match an option that exists
- If you specify "C" as correct, you must have an option_c column with a value

**"Missing required columns"**
- Ensure your file has headers for: question_type, grade_level, subject, question_text
- Column names are case-insensitive but must match exactly

**"File too large"**
- Maximum file size is 10MB
- Split large imports into multiple smaller files

### Tips for Excel Files

1. Save as .xlsx format (not .xls) for better compatibility
2. Avoid merged cells in the header row
3. Ensure all data is in the first sheet
4. Remove any empty rows between data rows

### Tips for CSV Files

1. Use UTF-8 encoding
2. Enclose fields containing commas in double quotes
3. Use proper line endings (LF or CRLF)
4. Avoid special characters in column headers

## Template File

A template CSV file is available at:
```
storage/app/templates/question_import_template.csv
```

Download this file to use as a starting point for your imports.

## Support

For additional help or to report issues with the import feature, please contact your system administrator.
