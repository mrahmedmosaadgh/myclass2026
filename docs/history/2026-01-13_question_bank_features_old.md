# Question Bank Management System Features

**URL:** `http://127.0.0.1:8000/admin/question-banks`
**Component:** `resources/js/Pages/my_class/admin/QuestionBanks/Index.vue`

## Overview
The Question Bank Management System is a comprehensive module designed for teachers and administrators to create, manage, and organize educational questions. It allows for advanced filtering, bulk importing, and the generation of printable tests and worksheets.

## Key Features

### 1. Question Management (CRUD)
- **Create Questions**: Detailed form to add new questions with:
  - **Question Text**: Rich text support (via parsing/rendering).
  - **Question Type**: Multiple Choice (MCQ), True/False, Fill in the Blank.
  - **Metadata**: Score, Difficulty (Easy/Medium/Hard), Notes.
  - **Options**: 
    - Add multiple answer options.
    - Mark correct answers.
    - Add specific feedback for each option.
  - **Explanations**: Step-by-step explanation builder for solutions.
- **Edit Questions**: Modify existing questions.
- **Copy Questions**: "Copy as New" feature to quickly duplicate questions.
- **Delete Questions**: Remove questions from the bank.

### 2. Advanced Filtering
Questions can be filtered hierarchically to find specific content:
- **School**: Select the target school.
- **Subject**: Dynamic loading of subjects based on the selected school.
- **Curriculum**: Dynamic loading of curricula based on the selected subject.

### 3. Smart Import Tools
- **Clipboard Parsing**: 
  - Automatically parses question text pasted from the clipboard.
  - Intelligently detects question body and options from raw text.
- **Bulk Import**: 
  - Dedicated modal (`BulkQuestionImport2`) for uploading or entering multiple questions at once.

### 4. Test & Worksheet Generation
- **Visual Preview**: Real-time preview of how questions will look on paper.
- **Randomization**: "Shuffle" button to randomize the order of questions for different test versions.
- **Customization Settings**:
  - **Layout**: Choose between "Compact" or "Spacious" layouts.
  - **Columns**: Adjust the number of columns for answer options (1-5 columns).
  - **Visibility Toggles**: Show/Hide:
    - Options
    - Correct Answers (Answer Key)
    - Explanations
    - Scores
    - Step Numbers
    - Notes
- **Format Options**: Select paper size (A4, Letter, Legal).
- **Export & Print**:
  - **Print**: Generates a printer-friendly version of the test.
  - **PDF Export**: Generates a PDF file (simulated via print driver).
  - **Answer Key**: Option to include/exclude the answer key in the final output.

### 5. Technical Components
- **QuestionParser**: Utility to parse raw text into structured JSON.
- **KaTeX Support**: Renders mathematical formulas in question bodies and options.
- **LessonExplain**: Integration for Lesson Explanation features.
- **JsonTableBuilder/DataTableV7**: Used for the main listing table.

## Data Structure
The system relies on a relational structure:
- **Schools** -> **Subjects** -> **Curricula** -> **Question Bank**
- Each question is linked to a specific School, Subject, and Curriculum.

## Current State Observations
- **PDF Generation**: The `html2pdf` integration appears to be partially commented out in favor of a specialized browser print window solution (`window.print()`).
- **Imports**: The system supports a specific JSON structure for questions, including `body`, `options` array, `explanation` steps, and `type`.
