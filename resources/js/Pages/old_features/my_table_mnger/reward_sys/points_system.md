Here’s a **powerful, enterprise-grade AI prompt** you can use to make any AI fully understand your **complete points system** — including behaviors, point actions, canceling logic, totals, and dynamic calculations.

This prompt is crafted to drive **deep contextual understanding**, ensuring consistent output across API design, backend logic, Vue UI, and data modeling.

Use it as-is in your workflow.

---

# ✅ **AI Prompt — “Full System Understanding for the Student Points Engine”**

**Use the following prompt whenever you want the AI to understand your system deeply and generate accurate code or logic:**

---

### **🔶 AI SYSTEM PROMPT (Copy & Use)**

I am building a dynamic “Student Behavior & Points Management System” in Laravel + Inertia + Vue. I need you to fully understand the architecture and follow it consistently.

Here is the complete system definition:

---

## **1. Core Entity: `student_behaviors`**

Each record represents a single student’s behavior entry for a given day or lesson.

### Fields include:

* `school_id`
* `student_behaviors_mains_id`
* `student_id`
* `attend`
* `notes`

**Important:**
`points_plus` and `points_minus` **do not exist in the database anymore**.
They are calculated dynamically.

---

## **2. Sub-Entity: `student_behaviors_point_actions`**

This stores **every point transaction**, as an audit log.

### Fields:

* `student_behaviors_id`
* `reason_id`
* `value` (positive for plus, negative for minus)
* `action_type` (e.g., “reward”, “penalty”, “attendance”, etc.)
* `note`
* `created_by`

### Cancellation fields:

* `canceled` (boolean)
* `canceled_by`
* `canceled_at`
* `cancel_reason`

A canceled action **must never count toward totals** but must stay in the database for full traceability.

---

## **3. Dynamic Point Calculation (Laravel Model Accessors)**

The system auto-calculates:

### `points_plus`

Sum of all **positive, not-canceled** actions.

### `points_minus`

Absolute sum of all **negative, not-canceled** actions.

### `total_points`

`points_plus - points_minus`

These values are computed on the fly from `student_behaviors_point_actions`.
Nothing is stored in the database for these totals.

---

## **4. Adding a Point Action**

A new point action is created with:

* reason_id
* value
* action_type
* note
* created_by
* canceled = false

---

## **5. Canceling a Point Action**

When canceling:

* do NOT delete the record
* set `canceled = true`
* set `canceled_by`
* set `canceled_at`
* set `cancel_reason`

After cancellation, totals automatically update because the dynamic accessor ignores canceled records.

---

## **6. Requirements for AI output**

Whenever you generate code or logic for this system:

### You must:

* Respect the dynamic calculation system (never store totals).
* Always use `student_behaviors_point_actions` for any updates.
* Never modify totals manually.
* Ensure canceling actions only marks them canceled.
* Keep full audit history.
* Use clean, scalable architecture (Services + Controllers + Vue components).
* Follow best practices for Laravel, Vue, Quasar, and Inertia.

---

## **7. Deliverables You May Generate**

Depending on my request, you can provide:

* Laravel migrations
* Laravel models
* Laravel services
* Controllers
* API endpoints
* Business rules
* Vue UIs
* Quasar components
* UX flows
* Data validation
* Realtime sync logic
* Sample payloads
* DB diagrams
* API documentation

Always align your output with this full system design.

---

# 🟦 **End of System Definition**

When you respond, always rely on this full understanding.

---

If you understand this, say:
**“System loaded successfully. Ready to build.”**

---

# 🌟 **This prompt is your master key.**

Use it every time you want the AI to generate anything related to:

* adding points
* removing points
* canceling actions
* reporting totals
* UI flows
* backend logic

It forces the AI into **total alignment** with your architecture.

 