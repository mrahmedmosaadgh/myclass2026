# Fix Exam Layout Issues

## Issues
The user reported persistent layout issues ("mobile width narrow screen") with:
1.  Exam Details page (`/qu/student/exams/4`)
2.  Exam Taking Interface (`/qu/student/exams/4/take/13`)

## Fixes

### 1. Exam Details (`QuExamDetails.vue`)
*   **Change**: Removed column width restrictions (`col-12 col-md-10 col-lg-8`) and set it to full width (`col-12`).
*   **Reason**: The previous restrictive column classes made the card look too narrow on larger screens, especially if wrapped in a constrained layout.

### 2. Exam Taking Interface (`QuTakeExam.vue`)
*   **Change**: Added `defineOptions({ layout: null })`.
*   **Reason**: The component defines its own full-page `<q-layout>` structure. Without explicitly disabling the default `AppLayoutDefault`, it was likely being rendered *inside* the default layout's content area (which has padding and constraints), causing a nested layout issue where the exam interface was squished. Disabling the default layout allows it to take the full viewport as intended.

## Build Status
*   Triggered `npm run build` to compile these layout fixes.
*   Updated `myclass2026_build` repository.
