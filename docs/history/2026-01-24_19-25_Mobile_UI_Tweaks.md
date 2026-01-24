# Mobile UI Tweaks: Scroll & Button Placement

## Overview
Addressed specific mobile usability feedback regarding the "Update Photo" dialog.

## Changes Implemented

### 1. Scrollable Dialog (`AvatarManager.vue`)
*   **Issue**: On smaller screens, the dialog content could exceed the viewport height, making bottom buttons inaccessible.
*   **Fix**:
    *   Set `max-height: 90vh` on the `q-card`.
    *   Applied `display: flex; flex-direction: column` to the card.
    *   Added `scroll` class and `flex: 1` to the content `q-card-section`.
    *   **Result**: The dialog body now scrolls internally while maintaining the header and footer (if any) or simply fitting within the screen.

### 2. Camera Button Reordering (`CameraCapture.vue`)
*   **Issue**: Camera controls were below the video feed, requiring scrolling to access them on mobile.
*   **Fix**: Moved the **Switch Camera** and **Capture** buttons to the **top** of the camera view, above the video element.
*   **Result**: Controls are immediately visible and accessible without scrolling.

## Verification
*   **Scroll**: Verified that long content (like the camera preview) allows scrolling within the dialog frame on mobile dimensions.
*   **Layout**: Confirmed buttons appear above the video stream.
