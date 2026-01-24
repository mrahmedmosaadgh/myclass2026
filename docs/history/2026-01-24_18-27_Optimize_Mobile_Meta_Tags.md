# Optimization: Mobile Meta Tags and PWA Settings

## Context
The user requested verification and improvement of the `app.blade.php` layout for mobile views. The existing meta tags were basic and did not fully utilize iOS/Android native-like capabilities.

## Changes Implemented

### 1. Viewport Configuration
*   **File**: `resources/views/app.blade.php`
*   **Change**: Updated the `viewport` meta tag.
    *   `viewport-fit=cover`: Extends content into the safe area (notch) on iPhone X+ devices, removing white bars.
    *   `maximum-scale=1, user-scalable=no`: Disables pinch-zoom to make the web app feel like a native app and remove tap delays.

### 2. iOS PWA Settings
*   **Added**: `apple-mobile-web-app-capable="yes"`. Hides the Safari address bar and toolbar when added to the home screen.
*   **Added**: `apple-mobile-web-app-status-bar-style="black-translucent"`. Sets the status bar to be transparent/blended with the app background.

### 3. Android Theme
*   **Added**: `theme-color="#1f2937"`. Matches the browser's address bar color with the app's dark theme background.

## User Benefit
*   **Immersive Experience**: The app now utilizes the full screen on mobile devices.
*   **Native Feel**: Removal of browser chrome (when installed) and matching status bar colors make the web app feel indistinguishable from a native application.
