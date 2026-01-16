# Implement AI Lesson Template Generator & Page Titles

## Overview
Implemented an AI-powered workflow for generating lesson section templates and ensured all Lesson Presentation pages have proper `<Head>` titles for better browser navigation and history.

## What Was Done

### 1. AI Lesson Template Generator
- **New Component:** Created `resources/js/Components/Common/ai/AITemplateSuggestion.vue`.
  - **Multi-step Wizard:**
    1.  **Configure:** Input subject, grade, section count, and custom instructions.
    2.  **Use AI Tool:** Generated prompt display with "Copy" button. Added an **embedded iframe** to use AI tools (Gemini, ChatGPT, Claude) directly within the dialog without tab-switching.
    3.  **Paste Response:** Text area to paste the AI's JSON response with validation.
    4.  **Preview:** Visual preview of the generated template before acceptance.
  - **Features:**
    - **URL Management:** AI tool URL is customizable and persists in `localStorage`.
    - **Presets:** Quick buttons for popular AI tools.
    - **Prompt Generation:** Logic to create structured prompts demanding specific JSON formats.
    - **Validation:** Robust JSON parsing and validation to ensure the AI output matches the system's expected structure.
- **Integration:** Added "Generate with AI" button to `SectionTemplateManager.vue` using a distinctive gradient style.
- **Utilities:** Created `resources/js/utils/promptTemplates.js` to handle prompt text generation, response parsing, and validation logic.

### 2. Page Titles (`<Head>`)
Added dynamic `<Head>` titles to all pages in the Lesson Presentation module (`resources/js/Pages/my_table_mnger/lesson_presentation/`):
- **Lesson Dashboard (`LessonList.vue`):** "Lesson Dashboard"
- **Template Manager (`SectionTemplateManager.vue`):** "Section Templates"
- **Lesson Editor (`lesson_presentation.vue`):** "Editing: [Lesson Name]" or "New Lesson"
- **Student List (`StudentLessonList.vue`):** "My Lessons"
- **Student View (`StudentLessonView.vue`):** "Lesson: [Lesson Name]"
- **Teacher Dashboard (`TeacherProgressDashboard.vue`):** "Progress: [Lesson Name]"
- **Print View (`LessonPrintView.vue`):** "Print: [Lesson Name]"

## How to Implement AI Template Generator in Other Pages

### Step-by-Step Instructions

When you need to add an AI-powered template generator to another page (e.g., Quiz Templates, Assignment Templates, etc.), follow this pattern:

#### 1. Create Prompt Utility Functions
Add to `resources/js/utils/promptTemplates.js` (or create a new file like `resources/js/utils/quizPromptTemplates.js`):

```javascript
export function generateYourTemplatePrompt(config) {
  return `You are an expert [domain] template designer...
  
  Generate a JSON object with this EXACT structure:
  {
    "name": "Template Name",
    "items": [
      { "id": "item1", "title": "Title", "description": "..." }
    ]
  }
  
  Requirements:
  - Subject: ${config.subject}
  - Grade: ${config.grade}
  - Custom: ${config.customInstructions}
  
  IMPORTANT: Return ONLY valid JSON, no markdown, no code blocks.`;
}

export function validateYourTemplateResponse(data) {
  const errors = [];
  
  if (!data.name || typeof data.name !== 'string') {
    errors.push('Missing or invalid "name" field');
  }
  
  if (!Array.isArray(data.items) || data.items.length === 0) {
    errors.push('Must have at least one item');
  }
  
  // Add more validation...
  
  return {
    valid: errors.length === 0,
    errors
  };
}

export function parseAIResponse(text) {
  try {
    // Remove markdown code blocks if present
    let cleaned = text.trim();
    if (cleaned.startsWith('```json')) {
      cleaned = cleaned.replace(/```json\n?/g, '').replace(/```\n?/g, '');
    } else if (cleaned.startsWith('```')) {
      cleaned = cleaned.replace(/```\n?/g, '');
    }
    
    const data = JSON.parse(cleaned);
    return { success: true, data };
  } catch (error) {
    return { success: false, error: error.message };
  }
}
```

#### 2. Create the AI Generator Component
Create `resources/js/Components/Common/ai/AIYourFeatureSuggestion.vue`:

**Key sections to include:**
- **Template structure:** Multi-step `q-stepper` with 4 steps
- **Step 1 - Configure:** Input fields for configuration
- **Step 2 - Use AI Tool:** Prompt display + embedded iframe
- **Step 3 - Paste Response:** Textarea with validation
- **Step 4 - Preview:** Visual preview of generated content

**Essential features:**
```vue
<script setup>
import { ref, computed, onMounted } from 'vue';
import { useQuasar } from 'quasar';
import { generateYourTemplatePrompt, validateYourTemplateResponse, parseAIResponse } from '@/utils/yourPromptTemplates';

const $q = useQuasar();
const emit = defineEmits(['template-accepted', 'close']);

// Dialog state
const isOpen = ref(false);
const currentStep = ref(1);

// AI Tool URL Management (reusable pattern)
const aiToolUrl = ref('');
const customAiToolUrl = ref('');
const showUrlConfig = ref(false);

const aiToolPresets = [
  { name: 'Gemini', url: 'https://gemini.google.com/' },
  { name: 'ChatGPT', url: 'https://chatgpt.com/' },
  { name: 'Claude', url: 'https://claude.ai/' }
];

const loadAIToolUrl = () => {
  const savedUrl = localStorage.getItem('ai_tool_url');
  aiToolUrl.value = savedUrl || aiToolPresets[0].url;
};

const setAIToolUrl = (url) => {
  aiToolUrl.value = url;
  localStorage.setItem('ai_tool_url', url);
  $q.notify({ type: 'positive', message: 'AI tool updated' });
};

// Expose methods
defineExpose({ open });
</script>
```

#### 3. Integrate into Parent Component

In your main component (e.g., `YourTemplateManager.vue`):

```vue
<template>
  <!-- Add button with gradient style -->
  <q-btn
    unelevated
    icon="auto_awesome"
    label="Generate with AI"
    style="background: linear-gradient(to right, #9333ea, #4f46e5); color: white;"
    @click="openAIGenerator"
  />
  
  <!-- Add the AI component -->
  <AIYourFeatureSuggestion
    ref="aiGenerator"
    @template-accepted="handleAITemplateAccepted"
  />
</template>

<script setup>
import { ref } from 'vue';
import AIYourFeatureSuggestion from '@/Components/Common/ai/AIYourFeatureSuggestion.vue';

const aiGenerator = ref(null);

const openAIGenerator = () => {
  aiGenerator.value?.open();
};

const handleAITemplateAccepted = async (template) => {
  try {
    // Save the template using your existing API
    await axios.post(route('your.route.store'), template);
    $q.notify({ type: 'positive', message: 'Template created successfully!' });
    fetchTemplates(); // Refresh list
  } catch (error) {
    $q.notify({ type: 'negative', message: 'Failed to save template' });
  }
};
</script>
```

#### 4. Key Design Patterns to Follow

**Gradient Button Style:**
```vue
style="background: linear-gradient(to right, #9333ea, #4f46e5); color: white;"
```
(Use inline style because Quasar's `q-btn` overrides Tailwind classes)

**LocalStorage Key:**
- Use `'ai_tool_url'` for consistency across all AI features

**Validation Pattern:**
```javascript
const validateAndPreview = () => {
  validating.value = true;
  validationErrors.value = [];
  
  setTimeout(() => {
    const parseResult = parseAIResponse(aiResponse.value);
    if (!parseResult.success) {
      validationErrors.value = [parseResult.error];
      validating.value = false;
      return;
    }
    
    const validationResult = validateYourTemplateResponse(parseResult.data);
    if (!validationResult.valid) {
      validationErrors.value = validationResult.errors;
      validating.value = false;
      return;
    }
    
    parsedTemplate.value = parseResult.data;
    currentStep.value = 4;
    validating.value = false;
  }, 500);
};
```

**Copy/Paste Buttons:**
```javascript
const copyPromptToClipboard = async () => {
  try {
    await navigator.clipboard.writeText(generatedPrompt.value);
    $q.notify({ type: 'positive', message: 'Prompt copied!' });
  } catch (error) {
    $q.notify({ type: 'negative', message: 'Failed to copy' });
  }
};

const pasteFromClipboard = async () => {
  try {
    const text = await navigator.clipboard.readText();
    aiResponse.value = text;
    $q.notify({ type: 'positive', message: 'Pasted!' });
  } catch (error) {
    $q.notify({ type: 'negative', message: 'Failed to paste' });
  }
};
```

#### 5. Testing Checklist

- [ ] Prompt generates correctly with all config options
- [ ] Copy button works
- [ ] Iframe loads AI tool correctly
- [ ] Preset buttons switch AI tools
- [ ] Custom URL can be saved
- [ ] Paste button works
- [ ] JSON validation catches errors
- [ ] Invalid JSON shows error messages
- [ ] Valid JSON proceeds to preview
- [ ] Preview displays all fields correctly
- [ ] Accept button saves to database
- [ ] New item appears in list after saving

## What Needs to Be Done / Future Improvements
1.  **Prompt Refinement:** Continue tuning the system prompt in `promptTemplates.js` to minimize AI formatting errors (e.g., Markdown code blocks around JSON).
2.  **Global AI Settings:** Consider moving the "Preferred AI Tool URL" from `localStorage` (component-level) to a user profile setting in the database for cross-device persistence.
3.  **Direct API Integration:** Evaluate the feasibility of using direct API calls (e.g., OpenAI API) instead of the copy-paste workflow for a more seamless experience, though the current approach allows for free model usage.
4.  **Template Editing:** Allow users to tweak the generated content (e.g., change an icon or color) *during* the Preview step before final acceptance.
5.  **Reusable Base Component:** Extract common AI generator logic into a base component that can be extended for different use cases.
