# QuestionNavigator - Quick Reference

## Import

```typescript
import QuestionNavigator from './components/QuestionNavigator.vue'
```

## Basic Usage

```vue
<QuestionNavigator
  :questions="quizQuestions"
  :current-index="currentIndex"
  :answers="studentAnswers"
  @navigate="handleNavigate"
/>
```

## Props

| Prop | Type | Description |
|------|------|-------------|
| `questions` | `QuizQuestion[]` | All quiz questions |
| `currentIndex` | `number` | Current question (0-based) |
| `answers` | `AnswerRecord[]` | Answered questions |

## Events

| Event | Payload | Description |
|-------|---------|-------------|
| `navigate` | `number` | Question index to navigate to |

## Visual States

- **Default**: Gray border, white background
- **Hover**: Blue border, light blue background  
- **Current**: Blue background, white text
- **Answered**: Green border, light green background
- **Current + Answered**: Green background, white text

## Accessibility

- ✅ ARIA navigation role
- ✅ ARIA labels on all dots
- ✅ Keyboard navigation (Tab, Enter, Space)
- ✅ Focus indicators
- ✅ Screen reader support

## When to Use

Use when:
- Review mode is enabled
- All questions have been answered
- User needs to jump between questions

## Example Handler

```typescript
const handleNavigate = (index: number) => {
  currentIndex.value = index
  // Optional: scroll to question, update URL, etc.
}
```

## Responsive Breakpoints

- **Mobile**: < 640px (smaller dots, centered)
- **Tablet**: 640px - 1024px (medium spacing)
- **Desktop**: > 1024px (standard layout)

## Requirements Satisfied

- ✅ Requirement 4.1: Navigation controls
- ✅ Requirement 4.3: Answered indicators
- ✅ Requirement 8.1: ARIA attributes
- ✅ Requirement 8.2: Keyboard navigation
- ✅ Requirement 8.4: Focus indicators

## Files

- **Component**: `QuestionNavigator.vue`
- **Demo**: `QuestionNavigatorDemo.vue`
- **Docs**: `QUESTION_NAVIGATOR_README.md`
- **Integration**: `QUESTION_NAVIGATOR_INTEGRATION.md`
