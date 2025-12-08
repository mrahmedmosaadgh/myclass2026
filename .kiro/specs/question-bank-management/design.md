# Design Document

## Overview

The Question Bank Management system provides a comprehensive interface for teachers to create, organize, and manage questions. The system consists of three main pages and several reusable components, all built with Vue 3, Quasar, and Inertia.js following the existing application patterns.

## Architecture

### High-Level Component Structure

```
QuestionManagement/
├── QuestionBank.vue          # Main listing page
├── QuestionEditor.vue        # Create/Edit form
├── QuestionImport.vue        # Bulk import interface
└── components/
    ├── QuestionCard.vue      # Question display card (already exists)
    ├── QuestionFilters.vue   # Filter sidebar
    ├── QuestionForm.vue      # Dynamic form based on type
    ├── OptionEditor.vue      # Option management for MCQ
    └── ImportPreview.vue     # Preview imported questions
```

### Page Flow

```
┌─────────────────┐
│ Question Bank   │ ──────► View all questions
│ (Main Page)     │         Filter & search
└────────┬────────┘         Pagination
         │
         ├──► Create New ──────► QuestionEditor.vue
         │                       (Empty form)
         │
         ├──► Edit Question ───► QuestionEditor.vue
         │                       (Pre-filled form)
         │
         ├──► Import ──────────► QuestionImport.vue
         │                       (File upload & preview)
         │
         └──► Delete ──────────► Confirmation dialog
                                 API call
```

## Components and Interfaces

### 1. QuestionBank.vue (Main Page)

**Purpose:** Display, filter, and manage all questions in the bank.

**Layout:**
```
┌─────────────────────────────────────────────────────────────┐
│  Question Bank                    [Import] [+ New Question] │
├──────────────┬──────────────────────────────────────────────┤
│              │  Search: [________________] [🔍]             │
│  Filters     │                                               │
│  ────────    │  ┌─────────────────────────────────────┐    │
│  Type        │  │ Question Card                        │    │
│  □ Multiple  │  │ What is 2+2?                        │    │
│  □ True/False│  │ Type: Multiple Choice | Easy         │    │
│  □ Short Ans │  │ Subject: Math | Grade: 3            │    │
│              │  │ [Edit] [Duplicate] [Delete]         │    │
│  Difficulty  │  └─────────────────────────────────────┘    │
│  □ Easy      │                                               │
│  □ Medium    │  ┌─────────────────────────────────────┐    │
│  □ Hard      │  │ Question Card                        │    │
│              │  │ ...                                  │    │
│  Subject     │  └─────────────────────────────────────┘    │
│  [Dropdown]  │                                               │
│              │  [< Previous] [1] [2] [3] [Next >]          │
│  Grade       │                                               │
│  [Dropdown]  │                                               │
│              │                                               │
│  Status      │                                               │
│  □ Draft     │                                               │
│  □ Active    │                                               │
│  □ Archived  │                                               │
└──────────────┴──────────────────────────────────────────────┘
```

**Key Features:**
- Responsive grid layout for question cards
- Real-time filtering without page reload
- Pagination with configurable items per page
- Bulk actions (future: select multiple, bulk delete/status change)
- Quick actions on each card (edit, duplicate, delete)

**State Management:**
```typescript
interface QuestionBankState {
  questions: Question[]
  filters: {
    search: string
    type: number | null
    difficulty: string | null
    subject: number | null
    grade: number | null
    topic: number | null
    status: string | null
  }
  pagination: {
    currentPage: number
    perPage: number
    total: number
  }
  loading: boolean
}
```

### 2. QuestionEditor.vue (Create/Edit Form)

**Purpose:** Dynamic form for creating or editing questions.

**Layout:**
```
┌─────────────────────────────────────────────────────────────┐
│  ← Back to Question Bank                                     │
│  Create New Question / Edit Question                         │
├─────────────────────────────────────────────────────────────┤
│                                                               │
│  Question Type: [Multiple Choice ▼]                         │
│                                                               │
│  Question Text: *                                            │
│  ┌───────────────────────────────────────────────────────┐  │
│  │ What is the capital of France?                        │  │
│  └───────────────────────────────────────────────────────┘  │
│                                                               │
│  ┌─────────────────────────────────────────────────────┐    │
│  │ Curriculum Alignment                                 │    │
│  │ Grade: [Select ▼]  Subject: [Select ▼]             │    │
│  │ Topic: [Select ▼]                                   │    │
│  └─────────────────────────────────────────────────────┘    │
│                                                               │
│  ┌─────────────────────────────────────────────────────┐    │
│  │ Cognitive Settings                                   │    │
│  │ Difficulty: [Easy ▼]  Bloom Level: [3 ▼]           │    │
│  │ Est. Time: [60] seconds                             │    │
│  └─────────────────────────────────────────────────────┘    │
│                                                               │
│  ┌─────────────────────────────────────────────────────┐    │
│  │ Answer Options                                       │    │
│  │ ○ A. Paris          [✓ Correct] [×]                │    │
│  │ ○ B. London         [ ] [×]                         │    │
│  │ ○ C. Berlin         [ ] [×]                         │    │
│  │ ○ D. Madrid         [ ] [×]                         │    │
│  │ [+ Add Option]                                       │    │
│  └─────────────────────────────────────────────────────┘    │
│                                                               │
│  ┌─────────────────────────────────────────────────────┐    │
│  │ Hints (Optional)                                     │    │
│  │ 1. [Think about France...]              [×]         │    │
│  │ [+ Add Hint]                                         │    │
│  └─────────────────────────────────────────────────────┘    │
│                                                               │
│  ┌─────────────────────────────────────────────────────┐    │
│  │ Explanation (Optional)                               │    │
│  │ [Paris is the capital and largest city of France]   │    │
│  │ Reveal: [After first attempt ▼]                     │    │
│  └─────────────────────────────────────────────────────┘    │
│                                                               │
│  Status: [Draft ▼]                                           │
│                                                               │
│  [Cancel]                              [Save as Draft] [Save]│
└─────────────────────────────────────────────────────────────┘
```

**Dynamic Form Behavior:**

The form adapts based on question type:

| Question Type | Shows Options | Shows Hints | Shows Explanation |
|--------------|---------------|-------------|-------------------|
| Multiple Choice | ✅ (2-6 options) | ✅ | ✅ |
| Multi-Select | ✅ (2-6 options) | ✅ | ✅ |
| True/False | ✅ (2 fixed) | ✅ | ✅ |
| Fill in Blank | ❌ | ✅ | ✅ |
| Short Answer | ❌ | ✅ | ✅ |
| Essay | ❌ | ✅ | ✅ |

**Validation Rules:**
- Question text: Required, min 10 characters
- Question type: Required
- Options: Required for MCQ/Multi-Select/True-False
  - At least 2 options
  - At least 1 correct answer
  - No duplicate option text
- Grade/Subject: Optional but recommended
- Difficulty: Optional, defaults to "Medium"
- Status: Required, defaults to "draft"

### 3. QuestionImport.vue (Bulk Import)

**Purpose:** Import multiple questions from Excel/CSV files.

**Layout:**
```
┌─────────────────────────────────────────────────────────────┐
│  ← Back to Question Bank                                     │
│  Import Questions                                            │
├─────────────────────────────────────────────────────────────┤
│                                                               │
│  Step 1: Upload File                                         │
│  ┌───────────────────────────────────────────────────────┐  │
│  │  📄 Drag and drop file here                           │  │
│  │     or click to browse                                │  │
│  │                                                        │  │
│  │  Supported formats: .xlsx, .xls, .csv                │  │
│  └───────────────────────────────────────────────────────┘  │
│                                                               │
│  [Download Template]                                         │
│                                                               │
│  ─────────────────────────────────────────────────────────  │
│                                                               │
│  Step 2: Preview & Validate                                  │
│  ┌───────────────────────────────────────────────────────┐  │
│  │ ✓ 45 valid questions                                  │  │
│  │ ⚠ 3 warnings                                          │  │
│  │ ✗ 2 errors                                            │  │
│  │                                                        │  │
│  │ [Show Details]                                        │  │
│  └───────────────────────────────────────────────────────┘  │
│                                                               │
│  Preview (first 5 questions):                                │
│  ┌───────────────────────────────────────────────────────┐  │
│  │ Row 1: What is 2+2? | Multiple Choice | Easy | ✓     │  │
│  │ Row 2: True or False: Earth is flat | True/False | ✗ │  │
│  │        Error: Missing correct answer                  │  │
│  │ Row 3: Capital of France? | Multiple Choice | ✓      │  │
│  └───────────────────────────────────────────────────────┘  │
│                                                               │
│  [Cancel]                                    [Import Valid]  │
└─────────────────────────────────────────────────────────────┘
```

**Import File Format (Excel/CSV):**

| Column | Required | Description | Example |
|--------|----------|-------------|---------|
| question_text | Yes | The question | "What is 2+2?" |
| question_type | Yes | Slug from question_types | "multiple_choice" |
| difficulty | No | Easy/Medium/Hard | "Easy" |
| grade_id | No | Grade level ID | 3 |
| subject_id | No | Subject ID | 2 |
| topic_id | No | Topic ID | 15 |
| bloom_level | No | 1-6 | 2 |
| option_a | Conditional | First option | "2" |
| option_b | Conditional | Second option | "3" |
| option_c | Conditional | Third option | "4" |
| option_d | Conditional | Fourth option | "5" |
| correct_answer | Conditional | A, B, C, D or A,B,C | "C" |
| hints | No | Pipe-separated | "Hint 1\|Hint 2" |
| explanation | No | Explanation text | "2+2 equals 4" |

**Import Process:**
1. File upload and parsing
2. Validation of each row
3. Preview with error/warning indicators
4. Batch creation of valid questions
5. Summary report with success/failure counts

## Data Models

### Question Interface
```typescript
interface Question {
  id: number
  question_type_id: number
  question_text: string
  grade_id: number | null
  subject_id: number | null
  topic_id: number | null
  bloom_level: number | null
  difficulty_level: number | null
  estimated_time_sec: number | null
  usage_count: number
  avg_success_rate: number | null
  discrimination_index: number | null
  author_id: number | null
  status: 'draft' | 'active' | 'archived' | 'review'
  hints: string[] | null
  explanation: {
    text: string
    revealed_after_attempt: number
  } | null
  created_at: string
  updated_at: string
  
  // Relationships
  question_type?: QuestionType
  options?: QuestionOption[]
  grade?: Grade
  subject?: Subject
  topic?: Topic
  author?: User
}
```

### QuestionOption Interface
```typescript
interface QuestionOption {
  id: number
  question_id: number
  option_key: string  // A, B, C, D, etc.
  option_text: string
  is_correct: boolean
  distractor_strength: number | null
  order_index: number
  created_at: string
  updated_at: string
}
```

### QuestionType Interface
```typescript
interface QuestionType {
  id: number
  slug: string
  name: string
  has_options: boolean
  supports_hints: boolean
  supports_explanation: boolean
}
```

## API Endpoints

### Question CRUD

```typescript
// List questions with filters and pagination
GET /api/questions
Query params: {
  search?: string
  question_type_id?: number
  difficulty?: string
  grade_id?: number
  subject_id?: number
  topic_id?: number
  status?: string
  page?: number
  per_page?: number
}
Response: {
  success: boolean
  data: {
    data: Question[]
    current_page: number
    last_page: number
    per_page: number
    total: number
  }
}

// Get single question with options
GET /api/questions/{id}
Response: {
  success: boolean
  data: Question
}

// Create new question
POST /api/questions
Body: {
  question_type_id: number
  question_text: string
  grade_id?: number
  subject_id?: number
  topic_id?: number
  difficulty_level?: number
  bloom_level?: number
  estimated_time_sec?: number
  status: string
  hints?: string[]
  explanation?: {
    text: string
    revealed_after_attempt: number
  }
  options?: {
    option_key: string
    option_text: string
    is_correct: boolean
    order_index: number
  }[]
}
Response: {
  success: boolean
  data: Question
  message: string
}

// Update question
PUT /api/questions/{id}
Body: Same as POST
Response: {
  success: boolean
  data: Question
  message: string
}

// Delete question
DELETE /api/questions/{id}
Response: {
  success: boolean
  message: string
}

// Duplicate question
POST /api/questions/{id}/duplicate
Response: {
  success: boolean
  data: Question
  message: string
}
```

### Bulk Operations

```typescript
// Import questions from file
POST /api/questions/import
Body: FormData with file
Response: {
  success: boolean
  data: {
    imported: number
    failed: number
    errors: {
      row: number
      message: string
    }[]
  }
  message: string
}

// Export questions to Excel/CSV
GET /api/questions/export
Query params: {
  format: 'xlsx' | 'csv'
  filters?: same as list endpoint
}
Response: File download
```

### Metadata Endpoints

```typescript
// Get question types
GET /api/question-types
Response: {
  success: boolean
  data: QuestionType[]
}

// Get grades
GET /api/grades
Response: {
  success: boolean
  data: Grade[]
}

// Get subjects (optionally filtered by grade)
GET /api/subjects?grade_id={id}
Response: {
  success: boolean
  data: Subject[]
}

// Get topics (optionally filtered by subject)
GET /api/topics?subject_id={id}
Response: {
  success: boolean
  data: Topic[]
}
```

## Error Handling

### Validation Errors
```typescript
{
  success: false,
  message: "Validation failed",
  errors: {
    question_text: ["The question text field is required."],
    options: ["At least one option must be marked as correct."]
  }
}
```

### Server Errors
```typescript
{
  success: false,
  message: "Failed to create question",
  error: "Database connection error"
}
```

### Client-Side Error Display
- Use Quasar's `$q.notify()` for toast notifications
- Display inline validation errors below form fields
- Show error summary at top of form for multiple errors
- Provide helpful error messages with suggestions

## Testing Strategy

### Unit Tests
- Test question form validation logic
- Test filter computation
- Test option management (add/remove/reorder)
- Test import file parsing
- Test export data formatting

### Integration Tests
- Test question CRUD operations through API
- Test file upload and import process
- Test filter combinations
- Test pagination
- Test duplicate functionality

### E2E Tests (Manual)
- Create question of each type
- Edit existing question
- Delete question with confirmation
- Import questions from sample file
- Export questions and verify format
- Apply multiple filters and verify results

## Performance Considerations

### Optimization Strategies
1. **Pagination:** Load 20-50 questions per page
2. **Lazy Loading:** Load question options only when needed
3. **Debounced Search:** Wait 300ms after typing before filtering
4. **Cached Metadata:** Store question types, grades, subjects in Vuex/Pinia
5. **Optimistic Updates:** Update UI immediately, rollback on error
6. **Virtual Scrolling:** For large lists (future enhancement)

### Database Indexes
Already defined in migrations:
- `questions.question_type_id`
- `questions.grade_id, questions.subject_id` (composite)
- `questions.status`
- `questions.author_id`
- `question_options.question_id`

## Accessibility

### WCAG 2.1 AA Compliance
- All form inputs have associated labels
- Keyboard navigation support (Tab, Enter, Escape)
- ARIA labels for icon buttons
- Focus indicators on interactive elements
- Color contrast ratio ≥ 4.5:1
- Screen reader announcements for dynamic content
- Error messages associated with form fields

### Keyboard Shortcuts
- `Ctrl/Cmd + N`: New question
- `Ctrl/Cmd + S`: Save question
- `Escape`: Close dialogs
- `Enter`: Submit forms
- `Tab`: Navigate between fields

## Security

### Authorization
- Only authenticated teachers/admins can access question bank
- Questions are scoped to school_id (multi-tenancy)
- Author can edit their own questions
- Admins can edit all questions

### Input Validation
- Sanitize HTML in question text (allow basic formatting only)
- Validate file types and sizes for imports
- Prevent SQL injection through parameterized queries
- Rate limiting on API endpoints

### Data Protection
- Questions marked as "archived" are soft-deleted
- Audit log for question modifications (future)
- Backup before bulk operations


## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system-essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

### Property 1: Dynamic form adaptation
*For any* question type selection, the form should display option fields if and only if the question type has_options is true
**Validates: Requirements 1.2, 1.3**

### Property 2: Option management operations
*For any* question with options, adding an option should increase the option count by 1, removing an option should decrease it by 1, and reordering should preserve the total count
**Validates: Requirements 1.4**

### Property 3: Valid question persistence
*For any* question with all required fields filled correctly, saving should result in a database record with matching data
**Validates: Requirements 1.6**

### Property 4: Validation error display
*For any* incomplete question (missing required fields or invalid data), attempting to save should display specific validation errors for each invalid field
**Validates: Requirements 1.7**

### Property 5: Cascading grade-subject filter
*For any* grade selection, the available subjects should only include those associated with that grade level
**Validates: Requirements 2.2**

### Property 6: Cascading subject-topic filter
*For any* subject selection, the available topics should only include those associated with that subject
**Validates: Requirements 2.3**

### Property 7: Difficulty level validation
*For any* difficulty input, the system should only accept "Easy", "Medium", or "Hard" (case-insensitive)
**Validates: Requirements 2.4**

### Property 8: Bloom level validation
*For any* Bloom level input, the system should only accept integer values from 1 to 6 inclusive
**Validates: Requirements 2.5**

### Property 9: Question list display completeness
*For any* question in the list view, all required fields (question text, type, difficulty, subject, status) should be displayed
**Validates: Requirements 3.2**

### Property 10: Question detail view completeness
*For any* question clicked in the list, the detail view should display all question properties including all associated options
**Validates: Requirements 3.3**

### Property 11: Pagination navigation
*For any* page change action, the system should load and display the correct subset of questions based on the page number and items per page
**Validates: Requirements 3.5**

### Property 12: Search filter accuracy
*For any* search query, the filtered results should only include questions where the question_text contains the search term (case-insensitive)
**Validates: Requirements 4.1**

### Property 13: Type filter accuracy
*For any* question type filter selection, the filtered results should only include questions with that question_type_id
**Validates: Requirements 4.2**

### Property 14: Difficulty filter accuracy
*For any* difficulty filter selection, the filtered results should only include questions with that difficulty level
**Validates: Requirements 4.3**

### Property 15: Subject filter accuracy
*For any* subject filter selection, the filtered results should only include questions with that subject_id
**Validates: Requirements 4.4**

### Property 16: Grade filter accuracy
*For any* grade filter selection, the filtered results should only include questions with that grade_id
**Validates: Requirements 4.5**

### Property 17: Topic filter accuracy
*For any* topic filter selection, the filtered results should only include questions with that topic_id
**Validates: Requirements 4.6**

### Property 18: Status filter accuracy
*For any* status filter selection, the filtered results should only include questions with that status
**Validates: Requirements 4.7**

### Property 19: Combined filter accuracy
*For any* combination of filters, the filtered results should only include questions matching ALL selected criteria (AND logic)
**Validates: Requirements 4.8**

### Property 20: Filter reset completeness
*For any* state with active filters, clearing all filters should result in displaying all questions (same as initial load)
**Validates: Requirements 4.9**

### Property 21: Edit form population
*For any* question opened for editing, all form fields should be populated with the current values from the database
**Validates: Requirements 5.2**

### Property 22: Question update persistence
*For any* valid edits to a question, saving should update the database record with the new values
**Validates: Requirements 5.4**

### Property 23: Edit cancellation preservation
*For any* question being edited, canceling should not modify the database record
**Validates: Requirements 5.5**

### Property 24: Cascading delete
*For any* question deletion, all associated question_options records should also be deleted from the database
**Validates: Requirements 6.3**

### Property 25: Delete cancellation preservation
*For any* question with delete initiated, canceling the confirmation should leave the question unchanged in the database
**Validates: Requirements 6.4**

### Property 26: Post-delete list refresh
*For any* successful question deletion, the question should no longer appear in the question list
**Validates: Requirements 6.5**

### Property 27: File type validation
*For any* file upload attempt, the system should only accept files with extensions .xlsx, .xls, or .csv
**Validates: Requirements 7.2**

### Property 28: Import file parsing
*For any* valid Excel or CSV file, the system should successfully parse all rows and extract question data
**Validates: Requirements 7.3**

### Property 29: Import preview generation
*For any* parsed import file with valid data, the system should display a preview showing all questions to be imported
**Validates: Requirements 7.4**

### Property 30: Bulk question creation
*For any* confirmed import with N valid questions, the system should create exactly N question records in the database
**Validates: Requirements 7.5**

### Property 31: Import error reporting
*For any* import file with invalid rows, the system should display specific error messages identifying each invalid row and the validation failure
**Validates: Requirements 7.6**

### Property 32: Import with options creation
*For any* imported question with options, the system should create the corresponding question_options records with correct associations
**Validates: Requirements 7.8**

### Property 33: Question duplication completeness
*For any* question duplication, the new question should have all the same properties as the original except for id, created_at, updated_at, status, and question_text
**Validates: Requirements 8.2**

### Property 34: Duplicate status initialization
*For any* duplicated question, the status should be set to "draft" regardless of the original question's status
**Validates: Requirements 8.3**

### Property 35: Duplicate text marking
*For any* duplicated question, the question_text should be the original text with " (Copy)" appended
**Validates: Requirements 8.4**

### Property 36: Status update persistence
*For any* status change action, the question's status field in the database should be updated to the new value
**Validates: Requirements 9.2**

### Property 37: Active status quiz builder visibility
*For any* question with status changed to "active", the question should appear in quiz builder question pools
**Validates: Requirements 9.3**

### Property 38: Archived status quiz builder hiding
*For any* question with status changed to "archived", the question should not appear in default quiz builder question pools
**Validates: Requirements 9.4**

### Property 39: Hints array storage
*For any* question with N hints added, the hints field should be stored as a JSON array with N elements
**Validates: Requirements 10.2**

### Property 40: Explanation structure storage
*For any* question with an explanation, the explanation field should be stored as JSON with both "text" and "revealed_after_attempt" properties
**Validates: Requirements 10.3, 10.5**

### Property 41: Usage count increment
*For any* question used in a quiz, the usage_count field should be incremented by 1
**Validates: Requirements 11.2**

### Property 42: Export data completeness
*For any* export operation, the generated file should include all question data and associated options for all selected questions
**Validates: Requirements 12.2**

### Property 43: Export-import round trip
*For any* set of questions exported and then re-imported, the imported questions should have the same data as the original questions (excluding auto-generated fields like id and timestamps)
**Validates: Requirements 12.6**

