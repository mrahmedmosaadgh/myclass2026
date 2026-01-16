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

## What Needs to Be Done / Future Improvements
1.  **Prompt Refinement:** Continue tuning the system prompt in `promptTemplates.js` to minimize AI formatting errors (e.g., Markdown code blocks around JSON).
2.  **Global AI Settings:** Consider moving the "Preferred AI Tool URL" from `localStorage` (component-level) to a user profile setting in the database for cross-device persistence.
3.  **Direct API Integration:** Evaluate the feasibility of using direct API calls (e.g., OpenAI API) instead of the copy-paste workflow for a more seamless experience, though the current approach allows for free model usage.
4.  **Template Editing:** Allow users to tweak the generated content (e.g., change an icon or color) *during* the Preview step before final acceptance.
