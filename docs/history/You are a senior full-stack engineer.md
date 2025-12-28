You are a senior full-stack engineer.

I am building a Laravel + Vue project and I want to implement a
PAGE-BASED DEVELOPMENT CHECKLIST / NOTES system.

REQUIREMENTS:
- Every page in the app has its own checklist (notes).
- The checklist is identified by a `page_key` (route name preferred).
- When a page opens, its checklist loads automatically.
- If the checklist does not exist, create an empty one silently.

EDITOR BEHAVIOR (VERY IMPORTANT):
- The editor must be extremely simple and keyboard-first.
- Press Enter → creates a new line.
- Each line has a checkbox.
- Users can freely type, edit, select, copy, and paste text.
- Pasting multiple lines should automatically create multiple checklist items.
- No heavy rich-text editor (no Quill, TipTap, Slate, etc).
- Use a lightweight `contenteditable` approach or equivalent.

DATA STORAGE:
- Store the ENTIRE checklist as ONE JSON document.
- One database row per page.
- Do NOT store each checklist item as a separate database record.

JSON FORMAT EXAMPLE:
{
  "page_key": "ScheduleCopiesIndex",
  "blocks": [
    { "type": "checkbox", "checked": true,  "text": "API connected" },
    { "type": "checkbox", "checked": false, "text": "Validation errors UI" }
  ]
}

BACKEND:
- Laravel
- Table example: `page_checklists`
  - id
  - page_key (indexed)
  - content (json)
  - updated_at
- API endpoints:
  - Get checklist by page_key
  - Save checklist (overwrite JSON)

FRONTEND:
- Vue component: `PageChecklist`
- Receives `pageKey` as a prop
- Auto-saves (debounced) or saves manually
- Supports copy-all as plain text

GLOBAL VIEW:
- Provide a separate page (e.g. /page-checklists) to:
  - List all pages with checklists
  - Show count of unchecked items
  - Search text across all checklists
  - Click to navigate to the related page

UX RULES:
- Checklist should be contextual (page-based), not global noise.
- Global overview is intentional (opened when needed).
- Hide checklist in production or behind a dev flag.

OUTPUT EXPECTATION:
- Propose clean architecture
- Provide example Laravel migration + model
- Provide Vue component structure
- Focus on simplicity, maintainability, and developer experience
