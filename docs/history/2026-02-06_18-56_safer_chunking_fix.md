# Fix Chunk Initialization & Safter Optimization

## Date: 2026-02-06

## Issue
-   **Error**: `ReferenceError: Cannot access 'l8' before initialization` in `vendor-vue-core`.
-   **Cause**: Grouping core framework libraries (Vue, Pinia, Inertia) together in `manualChunks` broke their internal initialization order because they have complex circular dependencies in the node_modules.

## Work Done
-   **Safer Chunking**: Updated `vite.config.js`.
    -   Removed `vendor-vue-core` grouping. Let Vite handle core framework dependencies automatically (safer).
    -   Refined application groupings to be more specific (Quiz Engine, Admin Core, Teacher Portal, Reward System).
    -   Kept heavy third-party groupings (Quasar, Charts, Firebase, KaTeX).

## Next Steps
-   Re-build production assets.
-   Deploy and verify if the initialization error is resolved.
-   Verify request count (should still be significantly lower than the original 1220).
