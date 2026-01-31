# 2026-01-30 18:44 | Menu Management: Dropdown + Role Preview (server-side)

## TL;DR ✅
Implemented a compact module selector (split dropdown) and a Role filter with authoritative **server-side role preview** for the Admin Menu Management page. Also added a Preview toggle to render read-only mode, updated `MenuList` to respect preview, fixed Ziggy route errors in layouts, added tests, and removed debug artifacts.

---

## What I implemented (detailed)
- Replaced `q-tabs` with a split `QBtnDropdown` that shows the currently selected module and a dropdown list of modules.
- Added a **Role** split dropdown so admins can select a role (e.g., Teacher) and preview menus for that role.
- Added a **Preview** toggle (persisted in the URL as `?preview=1`) to render `MenuList` in read-only mode (hides edit/delete/drag handles and disables reordering).
- Implemented server-side preview support in the navigation API (`/api/navigation?role=<role>&v2=true&preview=1`) which:
  - Requires the user to have `manage-menus` permission.
  - Returns the role-scoped menu structure (uses `MenuService::getMenuStructure()` so it does NOT filter by the authenticated user's permissions).
- Updated `MenuService` cache usage indirectly by using the existing `getMenuStructure` function and leaving caching behavior intact.
- Frontend: `useMenuStore.fetchMenus(role, v2, { preview: true })` sends `preview=1` and caches preview results separately (added preview suffix to cache keys).
- `MenuList.vue` now accepts a `preview` prop — action buttons and drag handles are hidden when `preview` is enabled and sortable initialization is disabled in that mode.
- Fixed Ziggy route errors in layout components (`AdminLayout.vue` and `TeacherLayout.vue`) by checking required route params in `safeRoute()` and returning `'#'` when required params are missing (also suppress Ziggy parameter errors to avoid console noise).
- Added a clear UI badge/text `VIEWING AS: <ROLE> (Preview)` with a tooltip explaining that this is a server-side preview and does not reflect the current user's personal permissions.
- Removed temporary debug logs and dev-only UI elements.

---

## Files changed
- Frontend
  - resources/js/Pages/Admin/MenuManagement.vue — replaced tabs with `QBtnDropdown`, added Role dropdown, preview badge & tooltip, preview menu fetch + integration, removed debug logs.
  - resources/js/Pages/Admin/MenuManagement/components/MenuList.vue — added `preview` prop; hide actions and disable sortable in preview mode.
  - resources/js/Stores/useMenuStore.js — added `preview` param support to `fetchMenus()` and separate preview cache key handling; included `module` and `id` in transformed menu items.
  - resources/js/Layouts/AdminLayout.vue and TeacherLayout.vue — improved `safeRoute()` to detect required Ziggy route params and silently return `'#'` when missing.
- Backend
  - app/Http/Controllers/Api/NavigationController.php — added `preview` support, permission check for `manage-menus`, and returned role-scoped structure when previewing.
  - tests/Feature/NavigationMenuTest.php — added tests: `test_admin_can_preview_teacher_menus()` and `test_non_admin_cannot_use_preview()`.

---

## Tests added / modified
- tests/Feature/NavigationMenuTest.php
  - Added coverage for the preview endpoint and authorization for previewing.

---

## How to test manually 🧪
1. Log in as an admin with `manage-menus` permission.
2. Open **Admin → Menu Management**.
3. Click the **Role** dropdown and pick a role (e.g., Teacher).
   - You should see `VIEWING AS: Teacher (Preview)` and a tooltip explaining the preview.
   - The menu list should reflect the server-side preview (what a Teacher would see), not your own permissions.
4. Toggle **Preview** to see read-only rendering and confirm edit/delete/drag handles are hidden.
5. Ensure that non-admin users (without `manage-menus`) receive a 403 if they try to call the preview endpoint.
6. Confirm Ziggy-related sidebar errors are gone (no console Ziggy parameter errors for missing params).

---

## What still needs to be done / follow-ups 🧭
- Auto-inject common route params (e.g., `teacher_id`) into menus when current user context permits so routes like `schedules.teacher.view` can be used directly from the sidebar (TODO: implement in `safeRoute` or menu generation).
- End-to-end QA across devices and mobile viewports to confirm dropdown/tooltip/preview spinner behave on narrow screens.
- Run full test suite locally and fix any intermittent or failing tests if they appear.
- Add additional integration tests ensuring caching/ETag behavior (304 responses) works correctly for preview and normal requests.
- Consider adding accessibility improvements and ARIA to the dropdown and tooltip for screen readers.

---

## Commit & push instructions
Follow the project history rules (in `docs/history/history_info.md`):

1. Stage and commit the changes on your branch:

```bash
git add .
git commit -m "Menu: Add role preview & dropdown | 2026-01-30 18:44 | macOS | Implement server-side role preview and admin role dropdown, add tests, disable sorting in preview, fix Ziggy params"
```

2. Push your branch:
```bash
git push origin <branch_name>
```

3. Create a new history file entry (this file) — already created at `docs/history/2026-01-30_18-44_history_menu_preview_and_dropdown.md`.

---

If you want, I can also:
- Run the test suite and fix any failing tests now.
- Create a small PR with this branch and open it for review (include testing instructions and screenshots).

---

**Author:** GitHub Copilot (Raptor mini (Preview))
**Timestamp:** 2026-01-30 18:44
