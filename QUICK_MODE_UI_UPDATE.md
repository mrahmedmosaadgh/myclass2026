# Quick Mode UI Updates - Two-Phase Flow

## Visual Indicators for Two Phases

### Phase Indicator Component

Add a visual indicator showing which phase the user is in:

```vue
<!-- Phase Indicator -->
<q-stepper-navigation class="q-mb-md">
  <q-linear-progress 
    :value="quickModePhase === 'gathering' ? 0.5 : 1" 
    color="primary" 
    size="8px"
    class="q-mb-sm"
  />
  <div class="row items-center justify-between text-caption">
    <div :class="quickModePhase === 'gathering' ? 'text-primary text-weight-bold' : 'text-grey'">
      <q-icon name="help_outline" size="sm" />
      Phase 1: Get Suggestions
    </div>
    <div :class="quickModePhase === 'confirmed' ? 'text-primary text-weight-bold' : 'text-grey'">
      <q-icon name="check_circle" size="sm" />
      Phase 2: Generate Exam
    </div>
  </div>
</q-stepper-navigation>
```

### Updated Quick Mode Step

```vue
<q-step
  v-if="quickMode"
  :name="0"
  title="Quick Generate - AI Auto-Configuration"
  icon="bolt"
  :done="step > 0"
>
  <div class="step-content">
    
    <!-- Phase Indicator -->
    <q-card flat bordered class="q-mb-md bg-blue-1">
      <q-card-section>
        <div class="row items-center">
          <q-icon 
            :name="allFieldsFilled ? 'check_circle' : 'help_outline'" 
            :color="allFieldsFilled ? 'green' : 'orange'" 
            size="md" 
            class="q-mr-sm"
          />
          <div class="col">
            <div class="text-subtitle2">
              {{ allFieldsFilled ? '✓ Ready to Generate' : '⚠️ Information Needed' }}
            </div>
            <div class="text-caption">
              {{ allFieldsFilled 
                ? 'All information provided. AI will generate your exam.' 
                : 'AI will suggest options for missing information first.' 
              }}
            </div>
          </div>
        </div>
      </q-card-section>
    </q-card>

    <!-- What AI Will Do -->
    <q-card flat bordered class="q-pa-md q-mb-md">
      <div class="text-h6 q-mb-md">
        {{ allFieldsFilled ? '🤖 AI Will Generate:' : '🤖 AI Will Suggest:' }}
      </div>
      
      <q-list v-if="!allFieldsFilled" bordered separator class="rounded-borders">
        <q-item>
          <q-item-section avatar>
            <q-icon color="orange" name="lightbulb" />
          </q-item-section>
          <q-item-section>
            <q-item-label>Suggestions for Missing Information</q-item-label>
            <q-item-label caption>
              AI will provide 3-4 options for: 
              {{ missingFieldsList }}
            </q-item-label>
          </q-item-section>
        </q-item>

        <q-item>
          <q-item-section avatar>
            <q-icon color="orange" name="question_answer" />
          </q-item-section>
          <q-item-section>
            <q-item-label>Confirmation Request</q-item-label>
            <q-item-label caption>
              AI will ask you to confirm before generating
            </q-item-label>
          </q-item-section>
        </q-item>

        <q-item>
          <q-item-section avatar>
            <q-icon color="blue" name="info" />
          </q-item-section>
          <q-item-section>
            <q-item-label>Next Steps</q-item-label>
            <q-item-label caption>
              Review suggestions, fill in the fields above, then generate again
            </q-item-label>
          </q-item-section>
        </q-item>
      </q-list>

      <q-list v-else bordered separator class="rounded-borders">
        <q-item>
          <q-item-section avatar>
            <q-icon color="green" name="check_circle" />
          </q-item-section>
          <q-item-section>
            <q-item-label>Complete Exam Structure</q-item-label>
            <q-item-label caption>3-5 sections with clear organization</q-item-label>
          </q-item-section>
        </q-item>

        <q-item>
          <q-item-section avatar>
            <q-icon color="green" name="check_circle" />
          </q-item-section>
          <q-item-section>
            <q-item-label>{{ quickModeContext.totalQuestions }} Questions</q-item-label>
            <q-item-label caption>Mixed types with varied difficulty</q-item-label>
          </q-item-section>
        </q-item>

        <q-item>
          <q-item-section avatar>
            <q-icon color="green" name="check_circle" />
          </q-item-section>
          <q-item-section>
            <q-item-label>Answer Keys & Explanations</q-item-label>
            <q-item-label caption>Complete with LaTeX math expressions</q-item-label>
          </q-item-section>
        </q-item>
      </q-list>
    </q-card>

    <!-- Information Banner -->
    <q-banner 
      :class="allFieldsFilled ? 'bg-green-1' : 'bg-amber-1'" 
      class="q-mb-md" 
      rounded
    >
      <template v-slot:avatar>
        <q-icon 
          :name="allFieldsFilled ? 'check_circle' : 'info'" 
          :color="allFieldsFilled ? 'green-8' : 'amber-8'" 
        />
      </template>
      <div class="text-body2">
        <strong v-if="!allFieldsFilled">Optional:</strong>
        <strong v-else>Ready:</strong>
        {{ allFieldsFilled 
          ? 'All information provided. Click below to generate your exam.' 
          : 'Provide context to help AI, or leave empty for AI to suggest everything.' 
        }}
      </div>
    </q-banner>

    <!-- Input Fields -->
    <q-input
      v-model="quickModeContext.subject"
      label="Subject"
      :placeholder="allFieldsFilled ? quickModeContext.subject : 'e.g., Mathematics, Science, English'"
      outlined
      class="q-mb-md"
      :hint="allFieldsFilled ? '✓ Provided' : 'Leave empty for AI suggestions'"
      :filled="!!quickModeContext.subject"
    >
      <template v-slot:prepend>
        <q-icon :name="quickModeContext.subject ? 'check_circle' : 'help_outline'" 
                :color="quickModeContext.subject ? 'green' : 'grey'" />
      </template>
    </q-input>

    <q-input
      v-model="quickModeContext.grade"
      label="Grade Level"
      :placeholder="allFieldsFilled ? quickModeContext.grade : 'e.g., Grade 7, Grade 10'"
      outlined
      class="q-mb-md"
      :hint="allFieldsFilled ? '✓ Provided' : 'Leave empty for AI suggestions'"
      :filled="!!quickModeContext.grade"
    >
      <template v-slot:prepend>
        <q-icon :name="quickModeContext.grade ? 'check_circle' : 'help_outline'" 
                :color="quickModeContext.grade ? 'green' : 'grey'" />
      </template>
    </q-input>

    <q-input
      v-model="quickModeContext.examType"
      label="Exam Type"
      :placeholder="allFieldsFilled ? quickModeContext.examType : 'e.g., Final Exam, Mid-term, Quiz'"
      outlined
      class="q-mb-md"
      :hint="allFieldsFilled ? '✓ Provided' : 'Leave empty for AI suggestions'"
      :filled="!!quickModeContext.examType"
    >
      <template v-slot:prepend>
        <q-icon :name="quickModeContext.examType ? 'check_circle' : 'help_outline'" 
                :color="quickModeContext.examType ? 'green' : 'grey'" />
      </template>
    </q-input>

    <q-input
      v-model.number="quickModeContext.totalQuestions"
      type="number"
      label="Total Questions"
      :placeholder="allFieldsFilled ? String(quickModeContext.totalQuestions) : 'e.g., 20'"
      outlined
      min="5"
      max="50"
      class="q-mb-md"
      :hint="allFieldsFilled ? '✓ Provided' : 'Leave empty for AI suggestions (typically 15-25)'"
      :filled="!!quickModeContext.totalQuestions"
    >
      <template v-slot:prepend>
        <q-icon :name="quickModeContext.totalQuestions ? 'check_circle' : 'help_outline'" 
                :color="quickModeContext.totalQuestions ? 'green' : 'grey'" />
      </template>
    </q-input>
  </q-card>

  <!-- Action Buttons -->
  <q-stepper-navigation class="q-mt-md">
    <q-btn 
      @click="generateQuickModePrompt" 
      :color="allFieldsFilled ? 'positive' : 'primary'"
      :label="allFieldsFilled ? 'Generate Exam Now' : 'Get AI Suggestions'"
      :icon="allFieldsFilled ? 'auto_awesome' : 'lightbulb'"
      unelevated
      size="lg"
      class="full-width q-mb-sm"
    />
    <q-btn 
      flat 
      @click="quickMode = false; step = 1" 
      color="grey-7" 
      label="Switch to Manual Mode" 
      class="full-width"
      size="sm"
    />
  </q-stepper-navigation>

  <!-- Generated Prompt Display -->
  <div v-if="generatedPrompt && quickMode" class="q-mt-md">
    <q-card bordered>
      <q-card-section :class="allFieldsFilled ? 'bg-green text-white' : 'bg-orange text-white'">
        <div class="text-subtitle1">
          {{ allFieldsFilled ? '✨ Exam Generation Prompt' : '💡 Suggestion Request Prompt' }}
        </div>
        <div class="text-caption">
          {{ allFieldsFilled 
            ? 'Copy this and paste into your AI assistant to generate the complete exam' 
            : 'Copy this and paste into your AI assistant to get suggestions' 
          }}
        </div>
      </q-card-section>
      
      <q-card-section>
        <q-markdown :source="generatedPrompt" class="prompt-markdown" />
      </q-card-section>

      <q-card-actions>
        <q-btn 
          flat 
          @click="copyPrompt" 
          color="primary" 
          icon="content_copy"
          label="Copy to Clipboard"
        />
        <q-space />
        <q-btn 
          v-if="allFieldsFilled"
          @click="step = 2" 
          color="secondary" 
          icon="arrow_forward"
          label="Next: Paste AI Response"
          unelevated
        />
        <q-chip v-else color="orange" text-color="white" icon="info">
          After getting suggestions, fill fields above and generate again
        </q-chip>
      </q-card-actions>
    </q-card>
  </div>
</div>
</q-step>
```

### Computed Properties

```javascript
// Check if all fields are filled
const allFieldsFilled = computed(() => {
  return !!(
    quickModeContext.value.subject &&
    quickModeContext.value.grade &&
    quickModeContext.value.examType &&
    quickModeContext.value.totalQuestions
  )
})

// Get list of missing fields
const missingFieldsList = computed(() => {
  const missing = []
  if (!quickModeContext.value.subject) missing.push('Subject')
  if (!quickModeContext.value.grade) missing.push('Grade Level')
  if (!quickModeContext.value.examType) missing.push('Exam Type')
  if (!quickModeContext.value.totalQuestions) missing.push('Question Count')
  return missing.join(', ')
})

// Determine current phase
const quickModePhase = computed(() => {
  return allFieldsFilled.value ? 'confirmed' : 'gathering'
})
```

## Visual Flow Diagram

```
┌─────────────────────────────────────────────────────────────┐
│  Quick Generate Mode                                        │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  Phase: [████████░░░░░░░░] 50%                            │
│  ● Phase 1: Get Suggestions    ○ Phase 2: Generate Exam   │
│                                                             │
├─────────────────────────────────────────────────────────────┤
│  ⚠️ Information Needed                                      │
│  AI will suggest options for missing information first.    │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  🤖 AI Will Suggest:                                        │
│  ○ Suggestions for: Subject, Grade, Exam Type, Questions  │
│  ○ Confirmation Request                                    │
│  ○ Next Steps                                              │
│                                                             │
├─────────────────────────────────────────────────────────────┤
│  ⚠️ Optional: Provide context or leave empty               │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  ○ Subject         [empty - AI will suggest]              │
│  ○ Grade Level     [empty - AI will suggest]              │
│  ○ Exam Type       [empty - AI will suggest]              │
│  ○ Total Questions [empty - AI will suggest]              │
│                                                             │
│  [Get AI Suggestions]                                      │
│                                                             │
└─────────────────────────────────────────────────────────────┘

        ↓ User gets suggestions from AI

┌─────────────────────────────────────────────────────────────┐
│  Quick Generate Mode                                        │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  Phase: [████████████████] 100%                            │
│  ○ Phase 1: Get Suggestions    ● Phase 2: Generate Exam   │
│                                                             │
├─────────────────────────────────────────────────────────────┤
│  ✓ Ready to Generate                                       │
│  All information provided. AI will generate your exam.     │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  🤖 AI Will Generate:                                       │
│  ✓ Complete Exam Structure                                 │
│  ✓ 25 Questions                                            │
│  ✓ Answer Keys & Explanations                              │
│                                                             │
├─────────────────────────────────────────────────────────────┤
│  ✓ Ready: All information provided                         │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  ✓ Subject         [Mathematics]                           │
│  ✓ Grade Level     [Grade 8]                               │
│  ✓ Exam Type       [Mid-term Exam]                         │
│  ✓ Total Questions [25]                                    │
│                                                             │
│  [Generate Exam Now]                                       │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

## Color Coding

### Phase 1 (Information Gathering)
- **Primary Color**: Orange/Amber
- **Icons**: help_outline, lightbulb, question_answer
- **Message**: "AI will suggest..."
- **Button**: "Get AI Suggestions"

### Phase 2 (Exam Generation)
- **Primary Color**: Green
- **Icons**: check_circle, auto_awesome
- **Message**: "AI will generate..."
- **Button**: "Generate Exam Now"

## User Experience Benefits

1. **Clear Visual Feedback**: Users immediately see which phase they're in
2. **Progress Indication**: Progress bar shows 50% → 100%
3. **Status Icons**: Check marks vs question marks
4. **Color Psychology**: Orange (needs attention) → Green (ready to go)
5. **Contextual Help**: Different messages for each phase
6. **Smart Buttons**: Button text changes based on state
7. **Field Validation**: Visual indicators on each input
8. **Next Steps**: Clear guidance on what to do next

## Implementation Priority

1. ✅ Add `allFieldsFilled` computed property
2. ✅ Add `missingFieldsList` computed property  
3. ✅ Add `quickModePhase` computed property
4. ✅ Update phase indicator component
5. ✅ Update "What AI Will Do" section
6. ✅ Add field status icons
7. ✅ Update button labels dynamically
8. ✅ Update prompt card colors
9. ✅ Add conditional next step guidance
10. ✅ Test complete flow

## Testing Scenarios

### Test 1: Empty Start
- All fields empty
- Shows orange/amber theme
- Button says "Get AI Suggestions"
- Progress at 50%

### Test 2: Partial Fill
- Fill subject only
- Still shows orange theme
- Missing list shows 3 items
- Button still "Get AI Suggestions"

### Test 3: All Filled
- Fill all 4 fields
- Shows green theme
- Button says "Generate Exam Now"
- Progress at 100%

### Test 4: Clear After Fill
- Fill all fields
- Clear one field
- Reverts to orange theme
- Button changes back

## Accessibility

- High contrast colors
- Clear icon meanings
- Screen reader friendly labels
- Keyboard navigation support
- Focus indicators
- ARIA labels on all interactive elements
