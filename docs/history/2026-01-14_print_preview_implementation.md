# Print Preview Implementation Strategy (2026-01-14)

This document explains the implementation of the "Print Preview" feature for exams.

## Objective
To provide a reliable way to print exams without:
1.  Opening new windows/tabs (which can be blocked or confusing).
2.  Leaking global application styles into the print output.
3.  Generating extra blank pages (a common issue with full-page frameworks).

## Solution: Iframe-based Dialog

We implemented a **Print Preview Dialog** that uses an `iframe` to load the print route. This creates a completely isolated DOM environment for the printable content, ensuring perfect styling and no interference from the main application's layout.

### Components Involved

1.  **`QuExamPrint.vue`**: 
    - The dedicated print view component.
    - Uses `layout: PrintLayout` to opt-out of the default app layout.
    - Contains specific `@media print` styles.

2.  **`PrintLayout.vue`**:
    - A minimal layout wrapper.
    - Resets `html`, `body`, and `#app` styles to `height: auto`, `overflow: visible`, and `background: white`.
    - Forces `color: black` to prevent Dark Mode text invisibility issues.

3.  **Consumer Components (`QuStudentExamList.vue`, `QuExamList.vue`)**:
    - **Dialog**: A `q-dialog` containing a `q-card` and an `iframe`.
    - **Iframe Source**: Points to the print route: `:src="route('qu-student.exams.print', exam.id)"`.
    - **Logic**: 
      ```javascript
      const showPrintDialogState = ref(false);
      const selectedExamForPrint = ref(null);
      const openPrintDialog = (exam) => {
          selectedExamForPrint.value = exam;
          showPrintDialogState.value = true;
      };
      ```

### Key Benefits
- **Isolation**: The iframe ensures the print styles (margin, padding, height) are strictly controlled.
- **User Experience**: Users stay on the same page.
- **Reliability**: Eliminates the "extra empty page" issue caused by `min-height: 100vh` on the main app layout.

### Usage
- Click **"Preview"** (Student) or **Print Icon** (Teacher) to open the dialog.
- Right-click inside the preview > Print (or use browser shortcut) to print *only* the frame content (most browsers handle this focusing automatically).
