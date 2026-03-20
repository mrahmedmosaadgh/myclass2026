I've read this review carefully. It's a solid technical critique — most points are valid. Let me go through it clearly.

---

## ✅ Full Agreement (Must Fix Before Building)

**Remove `session_id` from `fg_tasks`** — completely agree. The bidirectional coupling is a real bug waiting to happen. One task → many sessions is the correct relationship.

**Add soft deletes to all tables** — agree. With offline sync, hard deletes cause ghost records and sync nightmares.

**Add `version` int for sync conflict detection** — agree. "Last-write-wins" silently loses data. Version comparison at least *surfaces* the conflict instead of hiding it.

**Define strict AI JSON schema with confidence scores** — agree. Unpredictable AI output + no schema = brittle parsing. The confidence field also unlocks better UX in the review modal.

**Add indexes now** — agree. Cheap to add during migration, expensive to add later on a populated table.

---

## ✅ Agree With Nuance

**Soft delete vs hard delete on domains** — agree on soft delete, but I'd add one rule: if a domain is soft-deleted, its tasks become `domain_id = null` (unclassified), not deleted. Cascading domain deletion is dangerous for a personal tool where you might want to reorganize later.

**Tags consistency** — agree that `tags` on notes only is inconsistent. However I'd avoid the polymorphic `fg_taggables` table in v1. Reason: polymorphic relations add query complexity early. Better to add `tags JSON` to `fg_tasks` now and promote to a proper tagging system in v1.2 if needed.

**`priority_score` as derived field** — agree it's useful, but I'd keep it as a **frontend computed value** only in v1, not stored in the database. Storing computed fields creates a sync problem: what's the source of truth when importance or due_date changes?

**`is_today` boolean** — agree it's needed, but I'd implement it as a computed filter (`due_date = today OR is_today = true`) rather than a standalone boolean alone. Both together give you flexibility.

---

## ❌ Disagree

**Renaming "Domains" to "Areas" or "Contexts"** — I'd keep `fg_domains` as the *database name* for consistency with the established prefix system, but expose it in the UI as **"Life Areas"**. Renaming the technical layer mid-plan creates unnecessary churn. UI label and DB name don't have to match.

**`fg_task_logs` audit table in v1.2** — I'd push this to v2, not v1.2. You're a solo user building a personal tool. Audit logs are an enterprise concern. The engineering cost isn't worth it until you have a reason to need it.

**`is_recurring` / `recurrence_rule` nullable fields now** — partial disagreement. Adding nullable columns speculatively is tempting but adds mental overhead every time you touch the model. I'd only add these when recurring tasks become a planned feature, not as future-proofing.

---

## 💡 My Own Addition (Not in the Review)

One thing the review missed: **there's no `order` or `sort_order` on `fg_tasks`**. You have it on domains and sub-tasks but not on tasks themselves. When the user manually reorders their task list (which they will), you have no way to persist that. Add `sort_order integer` to `fg_tasks` now.

---

## 📋 The Agreed Final Changes to v1.1

| # | Change | Source |
| :--- | :--- | :--- |
| 1 | Remove `session_id` from `fg_tasks` | Review |
| 2 | Add `deleted_at` to all 5 tables | Review |
| 3 | Add `version int default 1` to all 5 tables | Review |
| 4 | Add indexes on user_id, status, domain_id, started_at | Review |
| 5 | Add `tags JSON nullable` to `fg_tasks` (match notes) | Review |
| 6 | Add `duration_seconds` to `fg_sessions` | Review |
| 7 | Add `is_today boolean` to `fg_tasks` | Review |
| 8 | Add `confidence float` to AI JSON schema output | Review |
| 9 | Add `sort_order integer` to `fg_tasks` | Mine |
| 10 | Keep `priority_score` as frontend computed only | Mine |
| 11 | Keep `fg_domains` as DB name, "Life Areas" in UI | Mine |
| 12 | Drop `fg_task_logs` — push to v2 | Mine |
| 13 | Drop `is_recurring` for now — not yet planned | Mine |

---

**This is now v1.2 — stable and production-ready to build.**

Do you want me to output the final complete updated schema and file structure incorporating all 13 changes, before we move to migrations?