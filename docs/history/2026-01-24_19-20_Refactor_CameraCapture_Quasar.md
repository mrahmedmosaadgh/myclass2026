# Refactor: CameraCapture to Quasar Components

## Overview
Refactored the `CameraCapture.vue` component to replace custom HTML/CSS with standard Quasar components. This ensures a consistent UI/UX across the application, especially on mobile devices, and reduces codebase maintenance by leveraging the framework's built-in responsiveness.

## Changes Implemented

### 1. Template Refactor
*   **File Selection**: Replaced `<input type="file">` with `<q-file filled bottom-slots>`.
*   **Buttons**: Replaced `<button class="touch-btn">` with `<q-btn>` using standard colors (`primary`, `positive`, `warning`, `negative`) and icons (`videocam`, `cameraswitch`, `crop`, `save`, etc.).
*   **Containers**: Wrapped sections in `q-card` and `q-card-section` for proper spacing and elevation.
*   **Instructions**: Replaced custom instruction divs with `q-banner` components (`bg-blue-1`, `bg-green-1`).

### 2. Script Updates
*   **File Handling**: Updated `onFileChange` to accept the file object directly (Quasar's `q-file` model value) instead of an event object.
*   **Reactive References**: Added `const file = ref(null)` to bind the file input.

### 3. CSS Cleanup
*   **Removed**: `touch-btn`, `section`, `section-title`, `camera-controls`, `crop-buttons`, `cropped-actions`, and custom mobile media queries.
*   **Kept**: Minimal scoped styles for `video` (max-width) and `crop-canvas` (border/cursor).

## Benefits
*   **Mobile-First**: Quasar components are naturally responsive and touch-friendly.
*   **Consistency**: Matches the visual style of the rest of the Reward System (e.g., `AvatarManager`, `StudentCard`).
*   **Maintainability**: Significantly reduced custom CSS lines (removed ~100 lines of style).
