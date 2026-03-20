Here is the final complete plan — v1.2, stable, ready to build.

---

# 🏗️ Focus Grid v1.2 — Final Master Plan

---

## 1. Database Tables

### `fg_domains`

| Column | Type | Notes |
| :--- | :--- | :--- |
| `id` | UUID | Generated frontend |
| `user_id` | FK → users | Required |
| `name` | string(100) | "Health", "Work" etc. |
| `emoji` | string(10) | Single emoji |
| `color_hex` | string(7) | e.g. `#FF5733` |
| `is_active` | boolean | default true |
| `sort_order` | integer | Manual ordering |
| `version` | integer | default 1, increment on update |
| `sync_status` | enum | `synced` / `pending` / `conflict` |
| `deleted_at` | timestamp | Soft delete |
| `created_at` | timestamp | — |
| `updated_at` | timestamp | — |

**Indexes:** `user_id`

**On soft delete:** tasks under this domain → `domain_id = null` (unclassified, not deleted)

---

### `fg_tasks`

| Column | Type | Notes |
| :--- | :--- | :--- |
| `id` | UUID | Generated frontend |
| `user_id` | FK → users | Required |
| `domain_id` | FK → `fg_domains` | Nullable — unclassified if null |
| `title` | string(255) | Required |
| `notes` | text | Nullable — extra context |
| `importance` | tinyint(0–10) | Manual or AI suggested |
| `status` | enum | `inbox` / `active` / `done` / `cancelled` |
| `source` | enum | `manual` / `ai_vent` / `quick_capture` |
| `is_today` | boolean | default false |
| `sort_order` | integer | Manual ordering |
| `tags` | JSON | Nullable |
| `due_date` | date | Nullable |
| `completed_at` | timestamp | Nullable |
| `version` | integer | default 1 |
| `sync_status` | enum | `synced` / `pending` / `conflict` |
| `deleted_at` | timestamp | Soft delete |
| `created_at` | timestamp | — |
| `updated_at` | timestamp | — |

**Indexes:** `(user_id, status)` · `(user_id, is_today)` · `domain_id`

**Rules:**
- Max 5 tasks with `status = active` at any time (enforced frontend + backend)
- `quick_capture` source → auto `status = inbox`, `importance = 1`

---

### `fg_sub_tasks`

| Column | Type | Notes |
| :--- | :--- | :--- |
| `id` | UUID | Generated frontend |
| `task_id` | FK → `fg_tasks` | Required |
| `title` | string(255) | Required |
| `is_done` | boolean | default false |
| `sort_order` | integer | Manual ordering |
| `version` | integer | default 1 |
| `sync_status` | enum | `synced` / `pending` / `conflict` |
| `deleted_at` | timestamp | Soft delete |
| `created_at` | timestamp | — |
| `updated_at` | timestamp | — |

**Indexes:** `task_id`

---

### `fg_notes`

| Column | Type | Notes |
| :--- | :--- | :--- |
| `id` | UUID | Generated frontend |
| `user_id` | FK → users | Required |
| `domain_id` | FK → `fg_domains` | Nullable — defaults to General in UI |
| `body` | text | Required |
| `source` | enum | `manual` / `ai_vent` / `quick_capture` |
| `tags` | JSON | Nullable |
| `version` | integer | default 1 |
| `sync_status` | enum | `synced` / `pending` / `conflict` |
| `deleted_at` | timestamp | Soft delete |
| `created_at` | timestamp | — |
| `updated_at` | timestamp | — |

**Indexes:** `(user_id)` · `domain_id`

---

### `fg_sessions`

| Column | Type | Notes |
| :--- | :--- | :--- |
| `id` | UUID | Generated frontend |
| `user_id` | FK → users | Required |
| `task_id` | FK → `fg_tasks` | The focus task for this session |
| `intention` | string(255) | What you plan to accomplish |
| `energy_level` | enum | `high` / `medium` / `low` |
| `status` | enum | `active` / `completed` / `drifted` |
| `check_in_answer` | enum | `on_track` / `drifted` / `done` — nullable |
| `started_at` | timestamp | When session opened |
| `ended_at` | timestamp | Nullable |
| `duration_seconds` | integer | Computed on close: ended_at − started_at |
| `version` | integer | default 1 |
| `sync_status` | enum | `synced` / `pending` / `conflict` |
| `deleted_at` | timestamp | Soft delete |
| `created_at` | timestamp | — |
| `updated_at` | timestamp | — |

**Indexes:** `(user_id, started_at)` · `task_id`

**Relationship:** one task → many sessions over time. `session_id` does NOT exist in `fg_tasks`.

---

## 2. AI Contract (Strict JSON Schema)

Every response from `POST /fg/ai/vent` must conform to this shape:

```
{
  "tasks": [
    {
      "title": string,
      "importance": int (0–10),
      "domain_hint": string | null,
      "confidence": float (0.0–1.0)
    }
  ],
  "notes": [
    {
      "body": string,
      "domain_hint": string | null,
      "confidence": float (0.0–1.0)
    }
  ]
}
```

**Rules:**
- Items with `confidence < 0.5` shown in UI as dimmed — user decides
- `domain_hint` is a suggestion only — user confirms in review modal
- AI is instructed to return only this JSON, no prose around it

---

## 3. Sync Logic

```
Every write → IndexedDB first
            → sync_status = "pending"
            → version stays local

When online:
  POST /fg/[table]/sync (batch payload)
  Server checks:
    incoming.version > stored.version → accept, update
    incoming.version ≤ stored.version → reject, mark "conflict"
    deleted_at set → soft delete on server too

Conflict resolution (v1.2):
  → mark sync_status = "conflict"
  → surface in UI as a small conflict badge
  → user manually resolves (keep local / keep server)
```

---

## 4. Priority Score (Frontend Computed — Not Stored)

```
priority_score =
  (importance × 0.6) +
  (due_date_proximity_score × 0.4)

due_date_proximity_score:
  overdue        → 10
  due today      → 8
  due in 2 days  → 6
  due this week  → 4
  no due date    → 2
```

Used for default sort order in `fg-table-view.vue`. Never written to DB.

---

## 5. Folder & File Structure

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
│   │           ├── FgSessionRequest.php
│   │           └── FgSyncRequest.php
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
│           ├── FgSyncService.php
│           ├── FgAiService.php
│           └── FgVentParserService.php
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
    └── fg_api.php
```

---

### Vue 3 Frontend

```
resources/js/fg/
│
├── stores/
│   ├── fg-domains.store.js
│   ├── fg-tasks.store.js
│   ├── fg-subtasks.store.js
│   ├── fg-notes.store.js
│   ├── fg-sessions.store.js
│   └── fg-sync.store.js
│
├── components/
│   ├── fg-venting-area.vue
│   ├── fg-ai-review-modal.vue
│   ├── fg-now-view.vue
│   ├── fg-quick-capture.vue
│   ├── fg-table-view.vue
│   └── fg-session-panel.vue
│
├── views/
│   ├── FgDashboard.vue
│   ├── FgPlanningView.vue
│   └── FgReviewView.vue
│
├── composables/
│   ├── fg-use-sync.js
│   ├── fg-use-ai.js
│   ├── fg-use-session.js
│   └── fg-use-priority.js
│
├── services/
│   ├── fg-api.service.js
│   └── fg-idb.service.js
│
└── router/
    └── fg-routes.js
```

---

## 6. API Routes (`fg_api.php`)

| Method | Endpoint | Controller | Purpose |
| :--- | :--- | :--- | :--- |
| GET | `/fg/domains` | FgDomainController@index | List domains |
| POST | `/fg/domains` | FgDomainController@store | Create |
| PUT | `/fg/domains/{id}` | FgDomainController@update | Edit |
| DELETE | `/fg/domains/{id}` | FgDomainController@destroy | Soft delete |
| GET | `/fg/tasks` | FgTaskController@index | List — filterable by status, domain, is_today |
| POST | `/fg/tasks` | FgTaskController@store | Create |
| PUT | `/fg/tasks/{id}` | FgTaskController@update | Edit |
| DELETE | `/fg/tasks/{id}` | FgTaskController@destroy | Soft delete |
| POST | `/fg/tasks/sync` | FgTaskController@sync | Batch sync |
| GET | `/fg/subtasks` | FgSubTaskController@index | List by task_id |
| POST | `/fg/subtasks` | FgSubTaskController@store | Create |
| PUT | `/fg/subtasks/{id}` | FgSubTaskController@update | Edit |
| DELETE | `/fg/subtasks/{id}` | FgSubTaskController@destroy | Soft delete |
| GET | `/fg/notes` | FgNoteController@index | List |
| POST | `/fg/notes` | FgNoteController@store | Create |
| PUT | `/fg/notes/{id}` | FgNoteController@update | Edit |
| DELETE | `/fg/notes/{id}` | FgNoteController@destroy | Soft delete |
| POST | `/fg/notes/sync` | FgNoteController@sync | Batch sync |
| GET | `/fg/sessions` | FgSessionController@index | List |
| POST | `/fg/sessions` | FgSessionController@store | Open session |
| PUT | `/fg/sessions/{id}` | FgSessionController@update | Close / check-in |
| POST | `/fg/ai/vent` | FgAiController@vent | Vent → AI → JSON |

---

## 7. User Flow Map

```
DUMP moment
  └── fg-venting-area.vue
        └── POST /fg/ai/vent
              └── fg-ai-review-modal.vue
                    ├── confirmed tasks → fg_tasks (status: inbox)
                    └── confirmed notes → fg_notes

PLAN moment
  └── FgPlanningView.vue (fg-table-view.vue)
        ├── filter: is_today / status / domain
        ├── sort: priority_score (computed)
        ├── mark tasks → is_today = true
        └── promote inbox → active (max 5)

ACT moment
  └── FgDashboard.vue → fg-now-view.vue
        ├── shows: 1 active task
        ├── fg-session-panel.vue (open session, tag energy)
        └── fg-quick-capture.vue (distraction → inbox instantly)

REVIEW moment
  └── FgReviewView.vue
        ├── sessions today: duration, energy, check-in answers
        └── tasks completed today
```

---

## 8. Build Order

| Phase | What | Outcome |
| :--- | :--- | :--- |
| **1** | Laravel install + Breeze/Sanctum + `fg_api.php` route file | Auth works, routes registered |
| **2** | Migrations for all 5 tables with indexes + soft deletes | DB complete |
| **3** | Models + relationships + FgSyncRequest validation | Data layer ready |
| **4** | `fg_domains` full CRUD (API + fg-domains.store.js) | First working endpoint |
| **5** | `fg_tasks` full CRUD + fg-table-view.vue | Can see and manage tasks |
| **6** | `fg_notes` full CRUD + fg-notes.store.js | Notes working |
| **7** | `fg_sub_tasks` CRUD (inline under task) | Sub-tasks working |
| **8** | `fg_sessions` + fg-session-panel + fg-now-view | Daily driver ready |
| **9** | fg-venting-area + FgAiService + fg-ai-review-modal | Core differentiator |
| **10** | fg-quick-capture + inbox flow | Capture complete |
| **11** | FgPlanningView + FgReviewView | Full daily loop |
| **12** | IndexedDB + fg-sync.store + batch sync endpoints | Offline-first complete |

---

## 9. What This System Is (One Paragraph)

Focus Grid is a personal clarity tool. You arrive overwhelmed — you dump everything in your head into a text box. AI sorts it into tasks and thoughts. You pick what matters today, open a session around one task, and work. When you drift, you capture the distraction without losing your thread. At the end of the day you see what you actually did. The whole system is built around the gap between chaos and starting — that is the only problem it solves.

---

**This is v1.2 — frozen, complete, and ready to build. Start with Phase 1.**