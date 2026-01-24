# Mobile UI Enhancements: Update Photo

## Overview
Improved the "Update Photo" dialog to be mobile-friendly and added an requested feature to make cropping optional.

## Changes Implemented

### 1. Responsive Dialog Width (`AvatarManager.vue`)
*   **Issue**: Dialog had a fixed `min-width: 500px`, causing it to overflow on small mobile screens.
*   **Fix**: Changed to `width: 100%; max-width: 500px`.
*   **Mobile**: Applied `maximized` prop solely for very small screens (`$q.screen.lt.sm`) if needed, but the width fix handles most cases.

### 2. Stacked Buttons (`CameraCapture.vue`)
*   **Issue**: Action buttons (Crop, Retake, Cancel) were inline, making them hard to tap on mobile.
*   **Fix**: Added CSS media queries (applied globally for the modal context) to:
    *   Stack buttons vertically (`flex-direction: column`).
    *   Make buttons full width (`width: 100%`).
    *   Add spacing between buttons (`margin-bottom: 8px`).

### 3. Optional Crop (`CameraCapture.vue`)
*   **Request**: "Make crop optional".
*   **Implementation**:
    *   Added a "➡️ Skip Crop" button to the controls.
    *   Implemented `useOriginal()` function that:
        *   Sets the `croppedDataUrl` to the original image source.
        *   Bypasses the crop rect validation.
        *   Moves directly to the "Preview/Save" stage.

## Verification
*   **Mobile View**: Dialog fits within screen width. Buttons are large and easy to tap.
*   **Flow**: User can now choose to crop OR simply use the uploaded/captured image as-is.
