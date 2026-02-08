# Fix Exam Layout Width

## Issue
The user reported that `QuExamDetails` (the student exam start page) looked too narrow ("mobile width") on the hosted server, possibly due to restrictive column sizing on larger screens.

## Fix
*   **File**: `resources/js/Pages/my_class/QuQuestionBankSystem/QuExamDetails.vue`
*   **Change**: Updated the container column classes:
    *   From: `col-12 col-md-8 col-lg-6` (50% width on large screens)
    *   To: `col-12 col-md-10 col-lg-8` (66% width on large screens)
*   **Effect**: The exam details card will now take up significantly more horizontal space on desktop screens, looking less like a mobile layout.

## Build Status
*   Triggered `npm run build` to compile the layout change.
*   Updated `myclass2026_build` repository.
