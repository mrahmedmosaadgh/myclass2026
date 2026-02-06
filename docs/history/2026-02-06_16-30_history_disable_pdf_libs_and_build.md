# History: Disable PDF Libraries and Build Deployment

**Date:** 2026-02-06
**Time:** 16:30
**Task:** Disable heavy PDF libraries, fix build issues, and deploy assets to `myclass2026_build`.

## 1. Issue Description
- The build process was failing due to circular dependency errors and issues with heavy PDF libraries (`jspdf`, `html2canvas`, `vue-pdf-embed`).
- The bundle size was large, and these libraries were identified as non-critical for the current deployment phase.
- The goal was to temporarily disable them and replace usage with placeholders.

## 2. Actions Taken

### 2.1. Removed Dependencies
- Uninstalled the following packages:
  - `html2canvas`
  - `jspdf`
  - `html2pdf.js`
  - `vue-pdf-embed`

### 2.2. Code Modifications
- **`resources/js/Pages/my_table_mnger/reward_sys/final/SimplePDFViewer.vue`**:
  - Commented out dynamic import of `vue-pdf-embed`.
  - Added maintenance log/alert.
- **`resources/js/Pages/my_table_mnger/reward_sys/final/PDFAnnotator.vue`**:
  - Commented out static imports of `html2canvas` and `jsPDF`.
  - Commented out dynamic import of `vue-pdf-embed`.
  - Updated `downloadCurrentPage` and `downloadAllPages` to show an alert.
- **`resources/js/Pages/my_table_mnger/reward_sys/reward_sys_comp/CertificateGenerator.vue`**:
  - Commented out dynamic imports of `html2canvas` and `jspdf`.
  - Updated `generatePDF` to show an alert.
- **`resources/js/Pages/my_table_mnger/reward_sys/reward_sys_comp/TopLeaderboard.vue`**:
  - Commented out `html2canvas` import.
  - Disabled `captureScreenshot` functionality with an alert.
- **`resources/js/Pages/print_html/MathRenderer.vue`**:
  - Removed unused `html2canvas` import.
- **`resources/js/Pages/my_table_mnger/weekly_system/components/WeeklyPlanClassroomList.vue`**:
  - Disabled `downloadPDF` method (which used `html2pdf.js`).
- **`resources/js/Pages/my_table_mnger/reward_sys/final/PDFViewer.vue`**:
  - Disabled `vue-pdf-embed` imports.
- **`vite.config.js`**:
  - Reverted to default chunking strategy (removed `manualChunks`) to fix circular dependency issues.

### 2.3. Build Verification
- Ran `npm run build`.
- Build completed successfully.
- Verified that no reference to uninstalled packages remained.

### 2.4. Deployment
- Deployed built assets to `https://github.com/mrahmedmosaadgh/myclass2026_build.git`.
- Used `git add -A`, `commit`, and `push origin main` from within the `public/build` directory.

## 3. Current Status
- **Build:** Success.
- **PDF Features:** Temporarily disabled (User sees alerts).
- **Deployment:** Assets updated in build repo.

## 4. Next Steps / TODO
- Re-enable PDF functionalities using lighter alternatives or by fixing the build configuration for these libraries when needed.
- Monitor application stability.
