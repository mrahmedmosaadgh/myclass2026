---
description: Add LaTeX support to RichTextEditor
---

# Task: Add LaTeX Support to RichTextEditor

## Context
The user wants to display LaTeX math equations like $x + 5 = 12$ correctly in the preview mode. Specifically, they asked for "LaTeX Support for any text" and a "btn to fix math symples".

## Requirements
1.  **Render LaTeX**: The preview (and editor, if possible) should render LaTeX equations enclosed in `$` or `$$`.
2.  **Fix Math Symbols Button**: A button in the `RichTextEditor` toolbar that scans the content for common math patterns (e.g., `x + 5 = 12`) and wraps them in LaTeX delimiters (e.g., `$x + 5 = 12$`) or converts them to LaTeX format.
3.  **Preview**: Ensure the "Preview" modes (LessonPlayer) render this LaTeX correctly.

## Implementation Plan

### 1. Update `RichTextEditor.vue`
-   **Add "Fix Math" Button**: Add a button to the toolbar (e.g., icon `auto_fix_high` or `calculate`).
-   **Implement `fixMathSymbols` function**:
    -   Regex to find isolated equations or math-like patterns.
    -   Be careful not to break existing HTML or LaTeX.
    -   Example patterns to detect: `x = 5`, `2x + 3`, `a^2 + b^2`.
    -   Wrap detected patterns in `$ ... $`.
-   **Integrate Katex**:
    -   Ensure `katex` is imported and used to render the content in the *editor view* if possible, or at least highlighted.
    -   Actually, for a `contenteditable` div, rendering live LaTeX is hard without a complex library like Quill or ProseMirror.
    -   **Approach**: We will keep the source code in the editor (e.g., `$x+5=12$`) but maybe highlight it.
    -   **Preview**: The `SlideRenderer` needs to parse this.

### 2. Update `SlideRenderer.vue` (and `TextSlide` renderer)
-   The `SlideRenderer` uses `v-html="slide.slide_content?.text"`.
-   We need a function that processes this HTML and renders LaTeX using Katex before displaying.
-   **Action**: Create a `renderMath(html)` function in `SlideRenderer.vue`.
    -   Use `katex.renderToString` logic or a library that auto-renders.
    -   Basic implementation: Find `$ ... $` and replace with `katex.renderToString(...)`.

### 3. Verification
-   Type `x + 5 = 12`.
-   Click "Fix Math".
-   It becomes `$x + 5 = 12$`.
-   Click Preview.
-   It renders as a nice math equation.

## "Best Practice"
-   Use `katex` for rendering (fast, standard).
-   Don't over-aggressive "fix math" to avoid breaking normal text.
-   Provide a "preview" mode in the editor itself if possible, or rely on the slide preview.

