> [!WARNING]
> **DEPRECATED (2026-01-22)**: The "Schedule Copies" architecture described here has been replaced by a "Single Live Schedule + JSON Drafts" model.
> See `docs/history/2026-01-22_17-20_draft_schedules_implementation.md` for the current implementation details.

I’ve summarized **the full understanding from this chat** into a clean **`.md-style document`** in the canvas.




# Weekly System – Design Summary & Active Context Strategy

## 1. Core Understanding from the Discussion

This system manages **school timetables and weekly lesson plans** with a strong separation between:

* **Structure (stable)** → timetable layout
* **Content (variable)** → weekly lesson details

The goal is to ensure that:

* Changing *day* or *period number* never breaks lesson plans
* Weekly content can vary across weeks while using the same timetable structure
* The backend is the **single source of truth** for what is *active*

---

## 2. Data Model – Final Mental Model

### 2.1 Schedule Copies (Structure Owner)

`schedule_copies` represents a **timetable structure**, not a week.

* One schedule copy can be reused across many weeks
* It defines *which timetable is currently active*

Key idea:

> Schedule copies answer: **“Which timetable layout are we using?”**

---

### 2.2 Schedules (Timetable Slots)

`schedules` represents **one slot in the timetable**.

* Stable identity: `schedules.id`
* Can change day, period, or order without breaking anything

Key idea:

> Schedules answer: **“When and where does this lesson happen?”**

---

### 2.3 Weekly Plans (Weekly Content)

`weekly_plans` represents **lesson content per week**.

Important rules:

* Linked by `schedule_id`
* Differentiated by `week_number`
* No day/period stored here

Key idea:

> Weekly plans answer: **“What happens in this lesson this week?”**

Recommended constraint:

```sql
UNIQUE (schedule_id, week_number)
```

---

## 3. Why This Design Works

* Admin can move lessons between days/periods safely
* All weeks follow the same structural changes automatically
* No duplication of schedules per week
* Clean support for rotating curriculum

This is a **structure vs time-based content** design, used in mature timetable systems.

---

## 4. Active Context Philosophy

The system has multiple "active" concepts:

* Active School
* Active Academic Year
* Active Semester
* Active Schedule Copy

The decision was:

> **The backend resolves the active context. The frontend only displays it.**

The frontend does NOT guess, default, or auto-select values.

---

## 5. Backend Context Resolution Rules

### School

* If user has one school → auto-select
* Else → select first allowed (or later: last-used)

### Academic Year

* Prefer active academic year
* Else → most recent

### Semester

* Prefer Semester 1 or active semester

### Schedule Copy

Priority:

1. Active schedule copy
2. First created copy
3. None → UI shows "Create Schedule"

This logic lives in **one backend endpoint** (e.g. `/weekly-system/context`).

---

## 6. Question: One “Active Records” Table vs Backend Logic

### Option A – Central `active_context` Table

Example:

```text
active_context
- school_id
- academic_year_id
- semester_id
- schedule_copy_id
- updated_by
- updated_at
```

**Pros**

* Single source of truth
* Very easy to manage
* Admin-friendly
* Fast queries
* Perfect for UI and permissions

**Cons**

* Needs careful updates
* Must enforce integrity rules

---

### Option B – Active Flags in Each Table (Derived Logic)

Each table has its own `active` flag and backend logic resolves the context.

**Pros**

* Fewer tables
* Simple at small scale

**Cons**

* Complex backend queries
* Harder to debug
* Risk of multiple actives
* Logic scattered across the system

---

## 7. Recommended Approach (Best Practice)

### ✅ Hybrid (Best of Both Worlds)

* Keep `active` flags in domain tables (academic year, schedule copy, etc.)
* Add **one resolved context table or service** that represents the current system state

Think of it as:

> **Active flags define possibilities**
> **Active context defines reality**

This gives:

* Clarity
* Safety
* Scalability

---

## 8. Final Principles to Keep

* Identity must be stable; time can change
* Backend decides, frontend renders
* Structure is not duplicated per week
* Weekly content is week-specific, not structure-specific

---

## 9. Final Verdict

Your idea is **architecturally sound**.

If the system is expected to grow:

* Multiple admins
* AI imports
* Audit requirements

Then **centralizing active context (logically or physically)** is the correct decision.

This design is already at **enterprise-grade level**.



















### Short answer to your architectural question 👇

> **Yes — your idea is correct, and you’re thinking in the right direction.**

But the **best solution is not “only one table” vs “only backend logic”**.
The **best practice is a hybrid**.

---

## My Recommendation (Clear & Practical)

### ✅ Use **backend logic** to determine *what can be active*

* `active` flags in:

  * schools
  * academic_years
  * semesters
  * schedule_copies

This keeps domain rules close to the data.

### ✅ Use **one resolved active context**

Either as:

* a **single table** (`active_context`)
* or a **single backend service / query**

This represents:

> “What the system is currently operating on”

That gives you:

* One place to debug
* One place to change
* One place the UI depends on

---

## Why this is the right balance

* ❌ Pure backend derivation → logic scattered, hard to reason about
* ❌ Pure active table only → risky if not validated
* ✅ Hybrid → **safe, clear, scalable**

This is how **ERP / School / LMS systems** do it.

---

## One sentence to remember

> **Active flags define possibilities.
> Active context defines reality.**

You’re no longer designing *screens* —
you’re designing a **system brain**.

If you want next:

* I can design the `active_context` table
* Or formalize the `/context` API contract
* Or help migrate safely from current logic
