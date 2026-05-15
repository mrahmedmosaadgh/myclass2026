# Question Preview Visibility Improvement Plan

## Current Issues
- Preview cards are too compact (12px padding)
- Question text is small (1rem font-size)
- Options have minimal spacing and weak visual distinction
- No question numbers for quick reference
- Poor visual hierarchy between question and options
- Math expressions may not render prominently
- Preview area may be cramped for 10+ questions

## Improvement Goals
1. **Enhanced Readability**: Larger fonts, better spacing, clearer hierarchy
2. **Visual Structure**: Question numbers, card separation, option grouping
3. **Math Visibility**: Ensure math expressions render prominently
4. **Scalability**: Better layout for multiple questions
5. **Professional Appearance**: Modern card design with clear visual cues

## Implementation Strategy

### 1. Preview Card Improvements
- Increase padding from 12px to 16-18px
- Add subtle shadow for depth
- Increase border radius for modern look
- Add question number badge

### 2. Question Text Enhancement
- Increase font-size from 1rem to 1.1-1.15rem
- Increase font-weight for emphasis
- Add bottom margin for separation from options
- Ensure EditableMath component has sufficient space

### 3. Options Layout
- Change from single column to 2-column grid for better space utilization
- Increase option padding from 6px to 10-12px
- Replace dashed border with solid border
- Add hover states for interactivity
- Improve correct answer highlighting (green background + checkmark icon)

### 4. Preview Area Layout
- Increase gap between cards from 12px to 16-18px
- Improve scrollbar styling
- Add max-height to prevent overflow issues

### 5. Visual Hierarchy
- Add question number badge (circle with number)
- Color-code question numbers
- Add subtle background pattern or gradient to cards
- Improve contrast ratios

## Technical Changes

### File: `GroupQuizGenerator.vue`

#### Template Changes (lines 280-305)
- Add question number badge to each card
- Restructure card layout for better spacing
- Add option letter labels (A, B, C, D)

#### Style Changes (lines 506-549)
- `.preview-cards`: Increase gap to 16-18px
- `.q-card`: Increase padding to 16-18px, add shadow, increase border-radius
- `.q-title`: Increase font-size to 1.15rem, add more margin-bottom
- `.q-options`: Change to 2-column grid for desktop
- `.q-opt`: Increase padding, change border style, add hover state
- Add new `.q-number` class for question badges
- Add new `.opt-label` class for option letters

## Expected Outcome
- Questions are easily scannable with clear numbers
- Math expressions render prominently
- Options are well-spaced and easy to read
- Correct answers are clearly highlighted
- Overall professional, modern appearance
- Better UX for reviewing generated questions before submission
