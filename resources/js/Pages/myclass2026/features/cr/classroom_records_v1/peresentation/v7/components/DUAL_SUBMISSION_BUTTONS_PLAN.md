# Dual Submission Buttons Plan

## Current State
- Single "Confirm & Add to Presentation" button
- Uses `generateQuestionElements(parsedQuestions.value, 'new', 'v3')` to add questions
- All questions added with default layout

## Target State
Two submission buttons with different layout modes:

**Button 1: Add All in Extended Slide**
- Stacks all questions vertically in one tall slide
- Extended slide height to accommodate all questions
- Good for review/overview of all questions

**Button 2: Add as Individual Slides**
- Each question on its own slide
- Navigation between slides
- Better for presentation mode with one question per slide

## Implementation Strategy

### 1. Add Second Submission Button
- Keep existing button: "Confirm & Add to Presentation (Extended Slide)"
- Add new button: "Add as Individual Slides"
- Style both buttons distinctly

### 2. Modify Submission Logic
- Update `submitToPresentation()` to accept a `layoutMode` parameter
- Values: `'extended'` (default) or `'individual'`
- Pass layoutMode to `generateQuestionElements()`

### 3. Update useAIPaste Composable
- Check if `generateQuestionElements` accepts layout parameter
- If not, add logic to handle both modes:
  - `extended`: Add all questions to single slide with vertical stacking
  - `individual`: Create new slide for each question

### 4. Layout Implementation Details

**Extended Slide Mode:**
- Single slide with increased height
- All GroupMCQ elements stacked vertically
- Calculate total height based on question count
- Use `presentation.addSlide()` once

**Individual Slides Mode:**
- Loop through questions
- Call `presentation.addSlide()` for each question
- Add one GroupMCQ element per slide
- Standard slide height

## Technical Changes

### File: `GroupQuizGenerator.vue`

#### Template Changes
- Rename existing button: "Add All in Extended Slide"
- Add new button: "Add as Individual Slides"
- Both buttons call `submitToPresentation()` with different parameters

#### Script Changes
```javascript
function submitToPresentation(layoutMode = 'extended') {
  if (parsedQuestions.value.length === 0) return;
  generateQuestionElements(parsedQuestions.value, 'new', 'v3', layoutMode);
  appendLeaderboard();
  close();
}
```

### File: `useAIPaste.js` (or wherever generateQuestionElements is defined)
- Add `layoutMode` parameter to `generateQuestionElements`
- Implement logic for both modes
- For extended: stack all elements on one slide
- For individual: create slide per question

## Expected Outcome
- Users can choose how questions are laid out in presentation
- Extended mode: good for review/printing
- Individual mode: good for live presentation
- Clear distinction between button purposes
