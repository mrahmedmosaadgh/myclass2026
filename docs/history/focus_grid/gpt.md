You’re close to a solid system, but there are a few structural gaps and some assumptions that will create problems later (especially around sync, sessions, and AI flow). I’ll go through this like a system review: clarify → challenge → improve.

---

# 🔍 1. Key Clarifications (you should answer before building)

These are not minor — they affect architecture decisions:

### A. Task ownership vs session focus

* Can **one session have multiple tasks** or strictly one?
* Can a **task exist without ever having a session**?

👉 Right now:

* You enforce **1 task per session**
* But also allow `session_id` inside `fg_tasks`

⚠️ This creates **bidirectional coupling** → risky.

---

### B. “importance” logic

* Is `importance`:

  * user-defined only?
  * AI-generated?
  * dynamic (changes over time)?

👉 If AI modifies it later → you need **history tracking**

---

### C. Offline conflicts

You defined:

```
last-write-wins
```

Question:

* What if user edits same task on **2 devices offline**?

👉 Current plan = silent overwrite → **data loss risk**

---

### D. Notes vs Tasks boundary

Your AI decides:

* task = has verb
* note = reflective

👉 But:

* Can user convert note → task later?
* Can tasks become notes?

---

### E. Domains lifecycle

* What happens if a domain is deleted?

  * cascade?
  * orphan tasks?

---

# ⚠️ 2. Structural Issues (Important Fixes)

## 2.1 ❌ `session_id` inside `fg_tasks`

This is the biggest issue.

### Problem:

* A task can have multiple sessions over time
* Your schema allows only one

### Fix:

👉 REMOVE:

```
session_id from fg_tasks
```

👉 KEEP ONLY:

```
fg_sessions.task_id
```

This makes it:

* One task → many sessions ✅
* Clean history ✅

---

## 2.2 ❌ Missing indexing / performance planning

You will hit slow queries quickly.

### Add indexes:

* `fg_tasks(user_id, status)`
* `fg_tasks(domain_id)`
* `fg_sessions(user_id, started_at)`
* `fg_notes(user_id)`

---

## 2.3 ❌ No soft deletes

You use:

```
DELETE endpoints
```

👉 Dangerous for:

* sync
* recovery
* audit

### Fix:

Add:

```
deleted_at (soft delete)
```

to ALL tables.

---

## 2.4 ❌ No audit / history layer

Right now:

* Edits overwrite data
* No traceability

### Add (optional v1.2):

```
fg_task_logs
- id
- task_id
- field_changed
- old_value
- new_value
- changed_at
```

---

## 2.5 ❌ `tags` as JSON in notes only

Inconsistent.

👉 Tasks will also need tags.

### Fix:

Either:

* add `tags` to tasks
  OR
* create:

```
fg_tags
fg_taggables (polymorphic)
```

---

## 🧠 3. AI Flow — Needs Tightening

Your AI flow is good conceptually, but missing constraints.

## Problem:

AI output is unpredictable.

### Add strict contract:

Instead of free parsing:
👉 enforce JSON schema from AI:

```
{
  "tasks": [
    { "title": "...", "confidence": 0.8 }
  ],
  "notes": [
    { "body": "...", "confidence": 0.7 }
  ]
}
```

### Why:

* prevents parsing errors
* enables filtering low-confidence items
* easier UI decisions

---

## 🧠 4. Sync System — Needs Upgrade (Important)

Current:

```
last-write-wins
```

This is fine for v1 BUT you need **minimal upgrade**:

### Add:

```
version (int)
```

### Flow:

* each update → version++
* server compares:

  * if mismatch → mark `conflict`

### This lets you:

* detect conflicts instead of hiding them

---

## 🧱 5. Missing Core Concepts

## 5.1 ❗ No “Inbox Zero” mechanism

You have:

```
status: inbox / active / done
```

But no rule system.

### Add logic:

* inbox = unprocessed
* active = selected for execution

👉 enforce:

* only X active tasks (e.g., 3–5)

---

## 5.2 ❗ No prioritization engine

You have `importance` but no usage.

### Add derived field (frontend or backend):

```
priority_score =
    importance * 0.6 +
    urgency (due_date proximity) * 0.4
```

---

## 5.3 ❗ No “Today” layer

Users think in:

* Today
* Not today

### Add:

```
is_today (boolean)
```

OR computed filter:

```
due_date = today OR manually marked
```

---

## 5.4 ❗ Sessions lack duration logic

You track:

```
started_at / ended_at
```

But not:

* duration
* interruptions

### Add:

```
duration_seconds (computed or stored)
```

---

# 🎯 6. UX-Level Improvements (High Impact)

## 6.1 “Now View” constraint

Force:

* 1 active task at a time

👉 This aligns with your philosophy.

---

## 6.2 Quick Capture should bypass everything

Current:
→ goes through system

Better:
→ instant insert:

```
status = inbox
source = quick_capture
importance = 1
```

No friction.

---

## 6.3 AI Review Modal

Add:

* confidence indicator
* “convert all to notes” button
* domain auto-suggestion

---

# 🏗️ 7. Build Order — Improved

Your order is good but slightly risky.

### Revised:

| Phase | Adjustment                               |
| ----- | ---------------------------------------- |
| 1     | ✅ same                                   |
| 2     | Add **soft deletes + indexes now**       |
| 3     | Add **basic filtering (status, domain)** |
| 4     | Sessions AFTER tasks stable              |
| 5     | AI AFTER manual flow is proven           |
| 6     | Notes earlier (before AI actually)       |
| 7     | Sync last (correct)                      |

---

# 🔥 8. Strategic Improvements (Big Picture)

## 8.1 Rename “Domains”

“Domains” is vague.

Better:

* Areas
* Life Areas
* Contexts

👉 Improves UX clarity.

---

## 8.2 Introduce “Focus Philosophy Layer”

Right now it's technical.

You actually built:

* Capture
* Clarify
* Act
* Reflect

👉 Make this explicit in UI:

* Dump → Clarify → Act → Review

---

## 8.3 Future-ready hooks

Prepare for:

* reminders
* recurring tasks
* AI prioritization

Add nullable fields now:

```
is_recurring
recurrence_rule
```

---

# ✅ Final Verdict

### What’s strong:

✔ Clean modular structure
✔ Good separation (tasks / notes / sessions)
✔ AI integration is well-placed
✔ Offline-first thinking (rare, good)

---

### What must be fixed before building:

1. ❗ Remove `session_id` from tasks
2. ❗ Add soft deletes
3. ❗ Improve sync (add versioning)
4. ❗ Define AI output schema
5. ❗ Clarify task-session relationship

---

If you want next step, I’d suggest:

👉 I can convert this into:

* **v1.2 upgraded schema (final production-ready)**
* or **execution checklist (day-by-day build plan)**

Just tell me which direction.
