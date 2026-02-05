# Fix TypeScript Imports in Quiz Module

**Date:** 2026-02-05
**Time:** 09:16
**Status:** Completed

## Context
The `QuizEngine.vue` component was throwing TypeScript errors ("Could not find a declaration file for module...") because several utility files were still in JavaScript (`.js` + `.d.ts` pairs) while the consuming component expected proper TypeScript modules. Specifically, `audio.js` and `useQuizI18n.js` were causing issues.

## Changes Implemented
1.  **Converted `audio.js` to TypeScript:**
    *   Created `resources/js/Utils/audio.ts`.
    *   Removed `resources/js/Utils/audio.js` and `resources/js/Utils/audio.d.ts`.
    *   Added explicit types for the `sounds` object and `playSound` function.

2.  **Converted `useQuizI18n` Internal Composable to TypeScript:**
    *   Created `resources/js/Pages/my_table_mnger/lesson_presentation/quiz/composables/useQuizI18n.ts`.
    *   Removed `resources/js/Pages/my_table_mnger/lesson_presentation/quiz/composables/useQuizI18n.js` and `.d.ts`.
    *   Merged the interface definitions directly into the implementation file for better type inference and maintainability.

## Pending Actions / Next Steps
- [ ] Verify that the `QuizEngine` builds without any remaining TS errors.
- [ ] Check if other files importing `audio.js` need their import paths updated (though `@/Utils/audio` should auto-resolve to `.ts` now).
