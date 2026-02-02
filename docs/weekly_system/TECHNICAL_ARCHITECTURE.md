# Weekly Plan System - Technical Architecture

## 1. System Overview

The Weekly Plan System is designed to manage the academic planning process. It links the Master Schedule (`schedules` table) to weekly content (`weekly_plans` table), allowing teachers to input Classwork, Homework, and Notes for each scheduled session.

## 2. Data Model

### core Entities

#### 1. WeeklyPlan (`weekly_plans`)
 The central entity representing a plan for a specific schedule slot in a specific week.
- **Constraints:**
    - Unique Composite Key: `[schedule_id, academic_year_id, semester_number, week_number]`
    - This ensures only ONE plan exists per schedule slot per week.
- **Relationships:**
    - `belongsTo(Schedule)`
    - `belongsTo(AcademicYear)`
    - `hasMany(WeeklyPlanSession)` (for split sessions)

#### 2. WeeklyPlanSession (`weekly_plan_sessions`)
Used when a single schedule slot represents multiple logical sessions (e.g., a double period or when `classes_per_week` > 1 but scheduled less often).
- **Fields:** `period_code`, `title`, `data` (JSON).
- **Period Code Format:** `YY.S.W.Session` (Year-Semester-Week-SessionIndex).

#### 3. Schedule (`schedules`)
The source of truth for timing and assignment.
- **Role:** Determines *who* (Teacher) teaches *what* (Subject) to *whom* (Classroom) at *when* (Day/Period).

## 3. Service Layer Logic (`WeeklyPlanService`)

### A. Generation & Sync
The system relies on a "Sync" mechanism rather than a static copy.
- **`generateForWeek(...)`**:
    - Iterates through all **Active** schedules.
    - Checks if a `WeeklyPlan` exists for that schedule/week.
    - If not, creates a new one.
    - **Crucial:** It does NOT duplicate plans if the schedule ID allows it.

### B. Sync Logic (`syncWithSchedule`)
When the Master Schedule changes (e.g., Teacher swapping):
1.  The Admin triggers a Sync.
2.  The service finds active schedules that match the `Classroom + Subject` pair of the orphan plan.
3.  Updates the `weekly_plans.schedule_id` to the new active schedule.
4.  Updates the `cst_id` to reflect the new teacher.

### C. Completion Stats
Calculated on-the-fly to ensure accuracy.
- **Complete:** Both CW and HW are filled.
- **Partial:** CW or HW is filled.
- **Empty:** Neither is filled.

## 4. Frontend Architecture

### Teacher View (`SimpleWeeklyPlans.vue`)
- **State:** Uses local refs for `weekNumber`.
- **Logic:** Fetches plans via `api/teacher/my-weekly-plans`.
- **Validation:** Prevents editing locked weeks (if implemented).

### Admin View (`WeeklyPlansManager.vue`)
- **Dashboard:** `WeeklyPlanSyncDashboard` for high-level controls.
- **Stats:** `WeeklyPlanStats` for monitoring.
- **Data Flow:** Admin triggers "Batch Create/Sync" -> Backend processes -> Stats update via Events or Polling.

## 5. Key Constants & Enums

- **Week Numbers:** 1-18 (Standard Semester).
- **Days:** 1 (Sunday) to 5 (Thursday) or 6 (Saturday).
- **Periods:** Configurable (usually 1-8).

## 6. Common Issues & Debugging

| Issue | Probable Cause | Fix |
|-------|----------------|-----|
| Plan visual duplicate | Multiple active schedules for same slot | Run `Schedule Deduplication` tool |
| "No Data" for Teacher | Teacher not assigned in CST | Check `ClassroomSubjectTeacher` table |
| Wrong Teacher Name | Plan linked to old Schedule ID | Run **Sync & Generate** for the week |
