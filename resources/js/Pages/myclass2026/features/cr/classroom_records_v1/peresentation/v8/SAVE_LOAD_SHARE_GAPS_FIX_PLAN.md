# V8 Presentation Save / Load / Share — Gap Analysis & Fix Plan

**Date:** 2026-05-16  
**Status:** AWAITING APPROVAL — do not code yet

---

## Gap Analysis (7 Critical Bugs)

### BUG 1 — Student share route does not exist (404)
**File:** `routes/myclass2026/cr/web.php`, `PresentationController.php`  
The controller generates share URLs as `/student-presentation/{shareToken}` but no such web route exists.  
The actual student route is `/classroom-records/presentation/student-v8` (no token in URL).  
**Result:** Every share link given to students is a 404.

---

### BUG 2 — Student API routes are inside auth middleware (401)
**File:** `routes/api.php` lines 239–241  
`GET /api/v8-presentations/shared/{shareToken}` and `POST .../attempt` are placed **inside** the `auth:sanctum` middleware group.  
Students are not logged in → they get a 401 Unauthenticated error.  
**Result:** Student can't load a shared presentation at all.

---

### BUG 3 — `presentation.loadPresentationData()` doesn't exist (JS crash)
**Files:** `Index.vue`, `StudentPresentation.vue`  
Both files call `presentation.loadPresentationData(...)` but the Pinia store only exposes `importPresentation()`.  
**Result:** Loading any presentation crashes with `TypeError: presentation.loadPresentationData is not a function`.

---

### BUG 4 — No CSRF token in API fetch calls (419 Page Expired)
**File:** `composables/usePresentationAPI.js`  
All `POST` and `DELETE` fetch calls are missing `X-XSRF-TOKEN` header.  
Laravel's CSRF protection will reject them with HTTP 419.  
**Result:** Save, delete, and submit-attempt all fail silently or with error.

---

### BUG 5 — Save always creates a new copy (no update/overwrite)
**File:** `PresentationController.php::saveV8Presentation`, `SavePresentationDialog.vue`  
There is no `PUT /api/v8-presentations/{id}` endpoint and no concept of "current presentation ID" in the store.  
If a teacher loads a saved presentation, edits it, and clicks Save → a duplicate is created instead of updating.  
**Result:** Teachers accumulate duplicates; no true edit-and-save workflow.

---

### BUG 6 — Save/Load/Share UI only accessible inside Focus Mode
**File:** `Index.vue`, `Toolbar.vue`  
The Save and "My Presentations" buttons are only mounted inside the **Focus Mini Bar** (`v-if="ui.isFocusMode"`), which is a secondary mode most users never activate.  
The main Toolbar "File" dropdown has only local Export/Import — no cloud Save or My Presentations.  
**Result:** The feature is effectively invisible to teachers using the normal editor.

---

### BUG 7 — StudentPresentation token read is broken
**File:** `StudentPresentation.vue::loadPresentationFromToken()`  
The code reads the token from `window.location.pathname.split('/').at(-1)`.  
But the student web route is `/classroom-records/presentation/student-v8` (static URL, no token).  
There is no mechanism to pass the share token to this Vue page.  
**Result:** `shareToken` is always `"student-v8"`, not a real token.

---

## Fix Plan (in priority order)

### Fix 1 — Add `{shareToken}` web route for students
**File:** `routes/myclass2026/cr/web.php`  
Add a **public** (no-auth) route: `GET /classroom-records/presentation/s/{shareToken}` that renders `StudentPresentation` and passes the token as an Inertia prop.

---

### Fix 2 — Move student API routes outside auth middleware
**File:** `routes/api.php`  
Move `GET /api/v8-presentations/shared/{shareToken}` and `POST .../attempt` outside the `auth:sanctum` group so unauthenticated students can reach them.

---

### Fix 3 — Add `loadPresentationData()` to the Pinia store
**File:** `stores/presentationStore.js`  
Add action `loadPresentationData(data)` — essentially an alias for `importPresentation()` that accepts the raw slides data from the API response and resets the store state.

---

### Fix 4 — Add CSRF token to all API fetch calls
**File:** `composables/usePresentationAPI.js`  
Read the `XSRF-TOKEN` cookie and attach it as `X-XSRF-TOKEN` header on all POST/DELETE requests. Helper:
```js
function getCsrfToken() {
  const match = document.cookie.match(/XSRF-TOKEN=([^;]+)/)
  return match ? decodeURIComponent(match[1]) : ''
}
```

---

### Fix 5 — Add update endpoint + "current presentation" tracking
**Backend:** Add `PUT /api/v8-presentations/{id}` → `updateV8Presentation()` in controller  
**Frontend Store:** Add `currentPresentationId` ref  
**`SavePresentationDialog`:** If `currentPresentationId` is set, offer "Update" + "Save as New" buttons  
**`Index.vue`:** On `@load`, set `currentPresentationId`; on `@saved`, set it if saving new

---

### Fix 6 — Add Save/Load/Share to the main Toolbar "File" dropdown
**File:** `Toolbar.vue`  
The "File" dropdown already has Export/Import. Add above the separator:
- **Cloud Save** → emits `save-to-cloud`
- **My Presentations** → emits `open-presentation-manager`

Parent `Index.vue` listens and opens the respective dialogs.  
Toolbar receives `@save-to-cloud` and `@open-presentation-manager` events from parent or uses a shared event bus/prop emit pattern already used in the codebase.

---

### Fix 7 — Pass share token as Inertia prop to StudentPresentation.vue
**File:** `StudentPresentation.vue`  
Accept `shareToken` as an Inertia prop (via `defineProps`).  
Remove fragile URL parsing. Use the prop directly in `loadPresentationFromToken()`.  
Also update the share URL generation in the controller to point to the new `/s/{shareToken}` route.

---

## Updated Share URL Format
```
https://qudratpro.com/classroom-records/presentation/s/{64-char-token}
```

---

## Files to Change

| File | Change |
|---|---|
| `routes/myclass2026/cr/web.php` | Add public `/s/{shareToken}` route |
| `routes/api.php` | Move 2 student routes outside auth middleware |
| `app/Http/Controllers/Api/PresentationController.php` | Add `updateV8Presentation()`, fix share URL |
| `stores/presentationStore.js` | Add `loadPresentationData()`, add `currentPresentationId` |
| `composables/usePresentationAPI.js` | Add CSRF token to all mutating requests, add `updatePresentation()` |
| `components/Toolbar.vue` | Add Cloud Save + My Presentations to File dropdown (emit to parent) |
| `components/SavePresentationDialog.vue` | Add update mode (update vs save-as-new) |
| `components/PresentationManager.vue` | Minor: show "currently loaded" badge |
| `StudentPresentation.vue` | Accept `shareToken` prop, remove URL parsing |
| `Index.vue` | Wire Toolbar events, set `currentPresentationId` on save/load |

**Total: 10 surgical edits — no new files needed**

---

## Assumptions
1. Auth for teachers uses Laravel Sanctum web guard (cookie-based) — CSRF is needed
2. The student route is public — no login required for `/s/{token}`
3. The existing `importPresentation()` store action is safe to reuse as `loadPresentationData()`
4. Quasar is globally configured — no new component imports needed

---

## Waiting for approval before any code changes.
