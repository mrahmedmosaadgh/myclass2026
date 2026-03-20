Perfect. Here is the complete detailed plan — no code, just the full blueprint.

---

## 🏗️ Focus Grid v1.1 — Complete Project Plan

---

## 1. Database Tables (MySQL, prefix: `fg_`)

### `fg_domains`
Life area containers. Everything in the system belongs to one.

| Column | Type | Notes |
| :--- | :--- | :--- |
| `id` | UUID | Generated frontend |
| `user_id` | FK | Auth user |
| `name` | string | "Work", "Health", etc. |
| `emoji` | string | Single emoji |
| `color_hex` | string | UI color |
| `is_active` | boolean | Soft toggle |
| `sort_order` | integer | Manual ordering |
| `sync_status` | enum | `synced` / `pending` / `conflict` |
| `created_at` / `updated_at` | timestamps | — |

---

### `fg_tasks`
The core execution unit. Every actionable item lives here.

| Column | Type | Notes |
| :--- | :--- | :--- |
| `id` | UUID | Generated frontend |
| `user_id` | FK | — |
| `domain_id` | FK → `fg_domains` | Nullable (unclassified) |
| `session_id` | FK → `fg_sessions` | Nullable — active session link |
| `title` | string | The task |
| `notes` | text | Optional extra context |
| `importance` | tinyint (0–10) | Manual or AI-suggested |
| `status` | enum | `inbox` / `active` / `done` / `cancelled` |
| `source` | enum | `manual` / `ai_vent` / `quick_capture` |
| `due_date` | date | Nullable |
| `completed_at` | timestamp | Nullable |
| `sync_status` | enum | `synced` / `pending` / `conflict` |
| `created_at` / `updated_at` | timestamps | — |

---

### `fg_sub_tasks`
Small executable steps under a parent task.

| Column | Type | Notes |
| :--- | :--- | :--- |
| `id` | UUID | Generated frontend |
| `task_id` | FK → `fg_tasks` | Required |
| `title` | string | The step |
| `is_done` | boolean | Simple checkbox |
| `sort_order` | integer | Manual ordering |
| `sync_status` | enum | `synced` / `pending` / `conflict` |
| `created_at` / `updated_at` | timestamps | — |

---

### `fg_notes`
Thoughts and ideas — things to remember, not to do.

| Column | Type | Notes |
| :--- | :--- | :--- |
| `id` | UUID | Generated frontend |
| `user_id` | FK | — |
| `domain_id` | FK → `fg_domains` | Nullable — defaults to "General" |
| `body` | text | Free-form content |
| `source` | enum | `manual` / `ai_vent` / `quick_capture` |
| `tags` | JSON | Optional lightweight tagging |
| `sync_status` | enum | `synced` / `pending` / `conflict` |
| `created_at` / `updated_at` | timestamps | — |

---

### `fg_sessions`
Conscious work blocks — opened intentionally, closed when done.

| Column | Type | Notes |
| :--- | :--- | :--- |
| `id` | UUID | Generated frontend |
| `user_id` | FK | — |
| `task_id` | FK → `fg_tasks` | The one task this session focuses on |
| `intention` | string | What you plan to do — in your words |
| `energy_level` | enum | `high` / `medium` / `low` |
| `status` | enum | `active` / `completed` / `drifted` |
| `check_in_answer` | enum | `on_track` / `drifted` / `done` — from soft check-in |
| `started_at` | timestamp | When session opened |
| `ended_at` | timestamp | Nullable — when closed |
| `sync_status` | enum | `synced` / `pending` / `conflict` |
| `created_at` / `updated_at` | timestamps | — |

---

## 2. Folder & File Structure

### Laravel Backend

```
focus-grid/
│
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Fg/
│   │   │       ├── FgDomainController.php
│   │   │       ├── FgTaskController.php
│   │   │       ├── FgSubTaskController.php
│   │   │       ├── FgNoteController.php
│   │   │       ├── FgSessionController.php
│   │   │       └── FgAiController.php
│   │   │
│   │   └── Requests/
│   │       └── Fg/
│   │           ├── FgDomainRequest.php
│   │           ├── FgTaskRequest.php
│   │           ├── FgSubTaskRequest.php
│   │           ├── FgNoteRequest.php
│   │           └── FgSessionRequest.php
│   │
│   ├── Models/
│   │   └── Fg/
│   │       ├── FgDomain.php
│   │       ├── FgTask.php
│   │       ├── FgSubTask.php
│   │       ├── FgNote.php
│   │       └── FgSession.php
│   │
│   └── Services/
│       └── Fg/
│           ├── FgSyncService.php        ← handles conflict resolution
│           ├── FgAiService.php          ← calls Claude API, parses output
│           └── FgVentParserService.php  ← sorts AI output → tasks vs notes
│
├── database/
│   └── migrations/
│       ├── xxxx_create_fg_domains_table.php
│       ├── xxxx_create_fg_tasks_table.php
│       ├── xxxx_create_fg_sub_tasks_table.php
│       ├── xxxx_create_fg_notes_table.php
│       └── xxxx_create_fg_sessions_table.php
│
└── routes/
    └── fg_api.php   ← all FG routes isolated in one file
```

---

### Vue 3 Frontend

```
resources/
└── js/
    └── fg/
        │
        ├── stores/
        │   ├── fg-tasks.store.js       ← Pinia: tasks CRUD + sync queue
        │   ├── fg-notes.store.js       ← Pinia: notes CRUD + sync queue
        │   ├── fg-domains.store.js     ← Pinia: domains list
        │   ├── fg-sessions.store.js    ← Pinia: active session state
        │   └── fg-sync.store.js        ← Pinia: global sync status + IndexedDB bridge
        │
        ├── components/
        │   ├── fg-venting-area.vue     ← Dump moment: free text input
        │   ├── fg-ai-review-modal.vue  ← Clarify moment: review AI output before saving
        │   ├── fg-now-view.vue         ← Act moment: single task focus screen
        │   ├── fg-quick-capture.vue    ← Act moment: distraction capture during work
        │   ├── fg-table-view.vue       ← Planning: full task list with filter/sort
        │   └── fg-session-panel.vue    ← Gentle session awareness + check-in prompt
        │
        ├── views/
        │   ├── FgDashboard.vue         ← Entry point: shows Now view or planning
        │   ├── FgPlanningView.vue      ← Morning planning / full task review
        │   └── FgReviewView.vue        ← End-of-day session summary
        │
        ├── composables/
        │   ├── fg-use-sync.js          ← online/offline detection + sync trigger
        │   ├── fg-use-ai.js            ← API call wrapper for venting feature
        │   └── fg-use-session.js       ← session open/close/check-in logic
        │
        ├── services/
        │   ├── fg-api.service.js       ← Axios wrapper for all FG endpoints
        │   └── fg-idb.service.js       ← IndexedDB read/write for offline data
        │
        └── router/
            └── fg-routes.js            ← Vue Router routes for all FG views
```

---

## 3. API Routes (in `fg_api.php`)

| Method | Endpoint | Controller | Purpose |
| :--- | :--- | :--- | :--- |
| GET | `/fg/domains` | FgDomainController@index | List all domains |
| POST | `/fg/domains` | FgDomainController@store | Create domain |
| PUT | `/fg/domains/{id}` | FgDomainController@update | Edit domain |
| DELETE | `/fg/domains/{id}` | FgDomainController@destroy | Delete domain |
| GET | `/fg/tasks` | FgTaskController@index | List tasks (filterable) |
| POST | `/fg/tasks` | FgTaskController@store | Create task |
| PUT | `/fg/tasks/{id}` | FgTaskController@update | Edit task |
| DELETE | `/fg/tasks/{id}` | FgTaskController@destroy | Delete task |
| POST | `/fg/tasks/sync` | FgTaskController@sync | Batch sync from IndexedDB |
| GET | `/fg/notes` | FgNoteController@index | List notes |
| POST | `/fg/notes` | FgNoteController@store | Create note |
| PUT | `/fg/notes/{id}` | FgNoteController@update | Edit note |
| DELETE | `/fg/notes/{id}` | FgNoteController@destroy | Delete note |
| GET | `/fg/sessions` | FgSessionController@index | List sessions |
| POST | `/fg/sessions` | FgSessionController@store | Open new session |
| PUT | `/fg/sessions/{id}` | FgSessionController@update | Close / check-in |
| POST | `/fg/ai/vent` | FgAiController@vent | Send vent text → get tasks+notes back |

---

## 4. The AI Vent Flow (No Code — Logic Only)

```
User types in fg-venting-area.vue
        ↓
fg-use-ai.js sends text to POST /fg/ai/vent
        ↓
FgAiController → FgAiService → Claude API
        ↓
FgVentParserService separates output into:
    → [ ] tasks  (actionable, has a verb, has an outcome)
    → [ ] notes  (reflective, an idea, something to remember)
        ↓
JSON returned to frontend
        ↓
fg-ai-review-modal.vue shows user the output
User can: edit titles / change type / remove items / assign domain
        ↓
User confirms → saved to fg_tasks + fg_notes via stores
        ↓
fg-sync.store.js queues for MySQL sync
```

---

## 5. Offline-First Sync Logic

```
All writes → IndexedDB first (instant, no wait)
          → sync_status = "pending"
          ↓
fg-use-sync.js watches navigator.onLine
          ↓
When online → POST /fg/tasks/sync (batch)
           → Server compares updated_at timestamps
           → Newer record wins (last-write-wins for v1)
           → sync_status = "synced"
```

---

## 6. Build Order (Recommended Sequence)

| Phase | What to build | Why this order |
| :--- | :--- | :--- |
| **1** | Migrations + Models + Basic Auth (Breeze) | Foundation before anything |
| **2** | fg_domains + fg_tasks CRUD (API + Store) | Core data layer |
| **3** | fg-table-view.vue | Lets you see and manage tasks immediately |
| **4** | fg_sessions + fg-session-panel + fg-now-view | The "Act" moment — daily driver |
| **5** | fg-venting-area + FgAiService + fg-ai-review-modal | The differentiator feature |
| **6** | fg_notes + fg-quick-capture | Capture layer |
| **7** | Offline sync (IndexedDB + fg-sync.store) | Polish — add once core works |
| **8** | FgReviewView (end-of-day) | Last because it reads existing data |

---

This is your complete blueprint. Every file has a reason, every table has a boundary, and the build order protects you from building things you can't test yet.

**Ready to start Phase 1 — migrations and models?**