# Bundle Size Optimization & Smart Chunking

## Date: 2026-02-06

## Issue
-   **Problem**: Page load triggered 1220+ HTTP requests.
-   **Cause**: Excessive code splitting caused by `defineAsyncComponent` in almost every sub-component (especially in the Quiz system and Page Resolver).
-   **Impact**: Significant performance degradation due to request overhead.

## Work Done
-   **Vite Configuration**: Implemented `manualChunks` in `vite.config.js` to group many small files into logical bundles.
-   **Vendor Bundling**:
    -   `vendor-vue-core`: Vue, Pinia, Inertia core.
    -   `vendor-quasar`: Quasar UI framework components.
    -   `vendor-firebase`: Firebase SDK.
    -   `vendor-charts`: ECharts and visualization libraries.
    -   `vendor-katex`: KaTeX math rendering.
    -   `vendor-utils`: Shared utilities like axios, lodash, date-fns.
-   **Feature Bundling**:
    -   `feature-quiz-engine`: Consolidated all Quiz Management and Question System components.
    -   `feature-admin-panel`: Consolidated all admin-related pages.
    -   `feature-teacher-portal`: Consolidated teacher dashboard and portal pages.
    -   `feature-student-portal`: Consolidated student and Qudrat landing pages.

## Results
-   **Before**: 1220+ requests.
-   **After**: Drastically reduced request count (estimated below 100) by grouping over 1,000 tiny files into fewer, meaningful chunks.
-   **Build Output**: Verified through `npm run build` that chunks are correctly formed (e.g., `feature-admin-panel` is now a single 770KB file instead of hundreds of tiny files).

## Next Steps
-   Deployment to production.
-   Final verification of request count in the browser.
