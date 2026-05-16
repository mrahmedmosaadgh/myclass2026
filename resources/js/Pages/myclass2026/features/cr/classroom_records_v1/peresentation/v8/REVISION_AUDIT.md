# V8 Presentation — Revision Audit

**Date:** 2026-05-16  
**Status:** 3 REMAINING BUGS + 4 RECOMMENDATIONS — AWAITING APPROVAL

---

## Remaining Bugs (Coding Required)

### BUG A — `handleLoadPresentation` ignores the API result → slides never load (CRITICAL)
**File:** `Index.vue` lines 44–63  
**Root cause:**  
`listV8Presentations` (used by PresentationManager) intentionally omits `presentation_data` from the response to keep the list lightweight. So `presentationData.presentation_data` is `undefined` when emitted from the manager.  
`loadPresentation(presentationData.id)` does fetch the full data and returns it as `data.data` — **but the return value is ignored**.  

**Current broken code:**
```js
const fullData = await loadPresentation(presentationData.id)  // return ignored!
presentation.loadPresentationData(presentationData.presentation_data)  // undefined!
```

**Fix:** Use the return value:
```js
const fullData = await loadPresentation(presentationData.id)
presentation.loadPresentationData(fullData.presentation_data)
```

---

### BUG B — `submitAttempts()` still reads token from URL path → wrong token (HIGH)
**File:** `StudentPresentation.vue` lines 102–103  
The `loadPresentationFromToken` was fixed (uses `props.shareToken`) but `submitAttempts()` was missed.  
It still does `window.location.pathname.split('/')` which returns `"student-v8"` not the real token.  
Result: All student attempt submissions will fail with 404 (no presentation found for token "student-v8").

**Fix:**
```js
async function submitAttempts() {
  const shareToken = props.shareToken  // use prop, not URL parsing
  ...
}
```

---

### BUG C — `q-icon name="presentation"` doesn't exist in Material Icons → broken icon (MEDIUM)
**File:** `PresentationManager.vue` line 134  
Material Icons has no icon named `"presentation"`. Quasar will render a broken icon placeholder.  

**Fix:** Change to `slideshow` or `present_to_all`.

---

## Recommendations (Optional Improvements)

### REC 1 — Pre-fill title/description in SavePresentationDialog when updating
**File:** `SavePresentationDialog.vue`  
Currently when a teacher loads a presentation and clicks "Save", the title field is blank. They have to retype the title even though it's already known.  
**Suggestion:** Add `presentationTitle` and `presentationDescription` props and use `watch` to populate fields when the dialog opens.

---

### REC 2 — Refresh PresentationManager list when dialog re-opens
**File:** `PresentationManager.vue`  
The list is loaded only once in `onMounted`. If a teacher saves a presentation, closes the dialog, then re-opens it — the new entry won't appear.  
**Suggestion:** Add a `watch(props.modelValue, (val) => { if (val) loadPresentations() })`.

---

### REC 3 — Add "New Presentation" button
**File:** `Toolbar.vue` or `Index.vue`  
There is no way for a teacher to start a fresh empty presentation while a loaded one is active. They would have to refresh the page.  
**Suggestion:** Add "New Presentation" to the Toolbar File dropdown that calls a `newPresentation()` store action (reset slides to 1 empty slide, clear currentPresentationId).

---

### REC 4 — Show active presentation name in toolbar
**File:** `Toolbar.vue` or `Index.vue`  
When a presentation is loaded, there is no visual indicator of which one is active.  
**Suggestion:** Show a small label in the toolbar: `Editing: "{title}"` using `presentation.currentPresentationId` and a title ref set alongside `setCurrentPresentationId`.

---

## Summary

| Item | Type | Severity | Action |
|------|------|----------|--------|
| BUG A — Load uses `undefined` presentation_data | Bug | Critical | Must fix |
| BUG B — submitAttempts uses wrong token | Bug | High | Must fix |
| BUG C — `q-icon name="presentation"` invalid | Bug | Medium | Must fix |
| REC 1 — Pre-fill title in save dialog | UX | Medium | Optional |
| REC 2 — Refresh list on re-open | UX | Medium | Optional |
| REC 3 — New Presentation button | Feature | Medium | Optional |
| REC 4 — Active presentation name in toolbar | UX | Low | Optional |

---

## Files to Change (Bugs Only)
1. `Index.vue` — Fix handleLoadPresentation to use `fullData.presentation_data`
2. `StudentPresentation.vue` — Fix submitAttempts to use `props.shareToken`
3. `PresentationManager.vue` — Fix icon name from `"presentation"` to `"slideshow"`

**Awaiting approval. Will also include recommendations if confirmed.**
