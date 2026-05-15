# Quiz Timer & Fullscreen Feature Implementation Plan

**Date:** 2026-05-15  
**Feature:** Add timer options and fullscreen button to quiz-v2  
**URL:** `/classroom-records/presentation/builder-v8`

---

## Requirements

1. **Timer Options**
   - Add UI to configure timer in QuizGeneratorDialog
   - Support per-question timer OR total quiz time
   - Configurable time duration (in seconds)
   - Enable/disable toggle

2. **Fullscreen Button**
   - Add fullscreen button to QuizElementV2 header
   - Toggle fullscreen mode for the quiz element
   - Maintain accessibility and keyboard support

---

## Implementation Strategy

### Part 1: Timer Configuration UI (QuizGeneratorDialog.vue)

**Changes:**
- Add form fields for timer settings:
  - Timer enable/disable toggle
  - Timer mode: "per-question" or "total-quiz"
  - Timer duration input (seconds)
- Update `injectQuiz()` to include timer settings
- Default: timerEnabled: false, timerSeconds: 30, timerMode: 'per-question'

**UI Location:** After "Extra Instructions" field, before "Action Buttons"

### Part 2: Fullscreen Button (QuizElementV2.vue)

**Changes:**
- Add fullscreen toggle button in header (next to score badges)
- Add `isFullscreen` ref state
- Add `toggleFullscreen()` method using Fullscreen API
- Update CSS for fullscreen mode
- Handle ESC key to exit fullscreen

**UI Location:** In `.qv2-header-right`, before score badges

---

## Technical Details

### Timer Logic Enhancement

Current timer in QuizElementV2:
- Already has `timerEnabled` and `timerSeconds` settings
- Timer resets on question change
- Need to add `timerMode` to distinguish per-question vs total quiz

**Per-question mode (existing):**
- Timer resets for each question
- Current implementation works

**Total quiz mode (new):**
- Timer continues across questions
- Timer starts on first question
- Timer ends when quiz completes or time runs out

### Fullscreen API

```javascript
function toggleFullscreen() {
  if (!document.fullscreenElement) {
    quizContainer.value.requestFullscreen()
  } else {
    document.exitFullscreen()
  }
}
```

---

## Files to Modify

1. `/resources/js/Pages/myclass2026/features/cr/classroom_records_v1/peresentation/v8/components/quiz-v2/QuizGeneratorDialog.vue`
   - Add timer configuration UI
   - Update injectQuiz() settings

2. `/resources/js/Pages/myclass2026/features/cr/classroom_records_v1/peresentation/v8/components/quiz-v2/QuizElementV2.vue`
   - Add fullscreen button
   - Implement fullscreen toggle
   - Add fullscreen CSS
   - Enhance timer for total-quiz mode

---

## Testing Checklist

- [ ] Timer can be enabled/disabled in generator dialog
- [ ] Per-question timer resets on each question
- [ ] Total quiz timer persists across questions
- [ ] Timer displays correctly in present mode
- [ ] Fullscreen button toggles fullscreen
- [ ] ESC key exits fullscreen
- [ ] Fullscreen works in edit mode (if applicable)
- [ ] Settings persist after quiz injection
