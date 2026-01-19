# Fixing i18n Translation Issues

## Context
The application was experiencing persistent `[i18n] Missing translation` warnings, and the UI was displaying translation keys (e.g., `weeklyPlans.selectWeek`) instead of the localized text. This occurred despite the translation strings being present in the `en.js` and `ar.js` files.

## Changes Made
1.  **Duplicate Object Merging**: Identified and resolved duplicate `weeklySystem` objects in both `resources/js/lang/en.js` and `resources/js/lang/ar.js` which were causing keys to be overwritten.
2.  **Conversion to JSON**: Converted the translation files from `.js` (ESM exports) to `.json` format (`en.json`, `ar.json`). This simplifies the import process and prevents issues with nested `.default` exports during the Vite build process.
3.  **Updated App Entry Point**: Modified `resources/js/app.js` to import the new `.json` translation files and removed the `.default` property access which was causing empty message objects in the `i18n` instance.
4.  **Reinstalled vue-i18n**: Uninstalled the existing `vue-i18n` package and reinstalled version 9 (`vue-i18n@9`) to ensure compatibility and resolve potential internal package conflicts.
5.  **Fixed Language Switcher**: Rewrote `resources/js/plugins/languageSwitcher.js` to correctly handle `vue-i18n` v9's `global.locale` property. Added `window.location.reload()` to force a full page reload upon language change, ensuring all components render with the correct translations immediately.
7.  **Cleanup**: Removed the obsolete `resources/js/lang/en.js` and `resources/js/lang/ar.js` files to prevent confusion, as the system now exclusively uses the new JSON files.

## Status
- **Validation**: Confirmed that the build process (`npm run build`) completes successfully.
- **Verification**: The main translation issues should be resolved, and switching languages should now work correctly and instantly (via reload).

## Next Steps
- Verify that no other missing translation warnings appear in the console during extended usage.
- Consider implementing hot-reloading for translations without a full page refresh if needed in the future, though the current reload solution is robust.
