# Question Import Preview - Show All Questions

## Changes Made

### Before
- Preview showed only "first 5 questions"
- Limited visibility of imported data
- No way to review all questions before import

### After
- Preview shows **ALL questions** in a scrollable list
- Each question displays:
  - Question number (1, 2, 3...)
  - Question text
  - All answer options (A, B, C, D, E, F) with correct answer highlighted in green
  - Question type
  - Correct answer indicator
  - Validation status (✓ or ✗)
- Scrollable container (max 500px height) for easy navigation
- Error details expansion panel for failed validations

## Preview Features

### Question Display
```
┌─────────────────────────────────────────────────────┐
│ [1] What is 2+2?                                    │
│ ┌─────┬─────┬─────┬─────┐                          │
│ │ A:2 │ B:3 │ C:4 │ D:5 │  (C is highlighted)      │
│ └─────┴─────┴─────┴─────┘                          │
│ Question #1 | Multiple Choice | Correct: C      ✓  │
└─────────────────────────────────────────────────────┘
```

### Visual Indicators
- **Avatar Numbers**: Each question has a numbered avatar (1, 2, 3...)
  - Green background = Valid question
  - Red background = Invalid question
- **Option Chips**: Answer options displayed as chips
  - Green chip = Correct answer
  - Grey chip = Incorrect answer
- **Status Icon**: Right side shows validation status
  - ✓ Green checkmark = Valid
  - ✗ Red error = Invalid

### Scrollable List
- Maximum height: 500px
- Vertical scroll for many questions
- All questions visible before import
- No pagination needed

### Error Details
- Expandable section for validation errors
- Shows row number and error message
- Only appears if there are errors

## User Experience

### Before Import
1. User uploads/pastes questions
2. System validates all questions
3. Preview shows **ALL** questions with:
   - Question text
   - All options
   - Correct answers highlighted
   - Validation status
4. User can scroll through entire list
5. User reviews and confirms import

### Benefits
- ✅ Full visibility of all questions
- ✅ Easy to spot errors before import
- ✅ Visual confirmation of correct answers
- ✅ No surprises after import
- ✅ Better user confidence

## Technical Implementation

### Computed Property
```javascript
const previewQuestions = computed(() => {
  if (!preview.value || !preview.value.questions) return [];
  return preview.value.questions;
});
```

### Helper Method
```javascript
const getQuestionOptions = (question) => {
  const options = {};
  const optionKeys = ['A', 'B', 'C', 'D', 'E', 'F'];
  
  optionKeys.forEach(key => {
    const optionKey = `option_${key.toLowerCase()}`;
    if (question[optionKey]) {
      options[key] = question[optionKey];
    }
  });
  
  return options;
};
```

### Preview Data Structure
```javascript
preview.value = {
  total_rows: 10,
  successful: 10,
  failed: 0,
  valid: 10,
  warnings: 0,
  errors: [],
  questions: [
    {
      question_text: "What is 2+2?",
      option_a: "2",
      option_b: "3",
      option_c: "4",
      option_d: "5",
      correct_answer: "C",
      valid: true
    },
    // ... more questions
  ]
};
```

## UI Components Used

### Quasar Components
- `q-list` - Container for questions
- `q-item` - Individual question row
- `q-avatar` - Numbered indicator
- `q-chip` - Answer options
- `q-icon` - Status indicators
- `q-expansion-item` - Error details

### Styling
- Scrollable container with `max-height: 500px`
- Color coding:
  - Green = Valid/Correct
  - Red = Invalid/Error
  - Grey = Neutral/Incorrect option
- Responsive layout
- Clear visual hierarchy

## Example Preview

### Valid Question
```
┌──────────────────────────────────────────────────────┐
│ [1] What is the capital of France?                   │
│ ┌─────────┬─────────┬─────────┬─────────┐           │
│ │ A:London│ B:Paris │ C:Berlin│ D:Madrid│           │
│ └─────────┴─────────┴─────────┴─────────┘           │
│ Question #1 | Multiple Choice | Correct: B       ✓  │
└──────────────────────────────────────────────────────┘
```

### Invalid Question
```
┌──────────────────────────────────────────────────────┐
│ [2] What is 2+2?                                      │
│ ┌─────┬─────┬─────┬─────┐                           │
│ │ A:2 │ B:3 │ C:4 │ D:5 │                           │
│ └─────┴─────┴─────┴─────┘                           │
│ Question #2 | Multiple Choice | Correct: E       ✗  │
│ ⚠ Correct answer 'E' not found in options           │
└──────────────────────────────────────────────────────┘
```

## Testing Checklist

- [ ] Upload CSV with 10 questions - all visible in preview
- [ ] Paste Excel data with 20 questions - all visible with scroll
- [ ] Paste JSON with 50 questions - scrollable list works
- [ ] Correct answers highlighted in green
- [ ] Invalid questions show error messages
- [ ] Question numbers match row numbers
- [ ] All options (A-F) display correctly
- [ ] Scroll works smoothly
- [ ] Error expansion panel shows details
- [ ] Import button disabled if errors exist
