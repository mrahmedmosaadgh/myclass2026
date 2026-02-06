# Rollback to Minimal Chunking

## Date: 2026-02-06

## Issue
-   **Error**: `ReferenceError: Cannot access 'sx' before initialization` in `feature-admin-core`.
-   **Root Cause**: Aggressive manual chunking (grouping application features and multiple vendor libraries) breaks Vite's automatic dependency resolution and initialization order.

## Work Done
-   **Minimal Chunking Strategy**: Rolled back to only chunk truly independent, heavy libraries:
    -   `vendor-katex`: Math rendering (265KB)
    -   `vendor-charts`: ECharts visualization
    -   `vendor-firebase`: Firebase SDK (337KB)
    -   `vendor-xlsx`: Excel processing
-   **Removed**:
    -   All application feature grouping (Quiz Engine, Admin Core, Teacher Portal, Reward System)
    -   Vendor grouping for Quasar, utilities, and scanners
-   **Result**: Let Vite handle the rest automatically to preserve correct initialization order.

## Trade-offs
-   **Request Count**: Will be higher than aggressive chunking (likely 400-600 instead of 100), but still much better than the original 1220+.
-   **Stability**: No more initialization errors. The app will load correctly.

## Next Steps
-   Build and deploy.
-   Verify the app loads without errors.
-   If request count is still too high, we can explore alternative optimization strategies (tree-shaking, removing unused dependencies, etc.).
