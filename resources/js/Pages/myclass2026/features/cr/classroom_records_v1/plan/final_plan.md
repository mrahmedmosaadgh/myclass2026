# Classroom Records v1 — Final Plan

## 1) الهدف
نظام “Classroom Records v1” لإدخال تقرير يومي سريع للصف (Session-based) عبر واجهة Cards:
- سريع جدًا في التحميل (request واحد للجلسة)
- ممتع وبسيط (tap-cycle)
- قابل لإعادة الاستخدام من Teacher Schedule (فتح الجلسة مباشرة)
- قابل للتوسع لاحقًا لإضافة أعمدة/فئات جديدة بدون إعادة هيكلة كبيرة

## 2) تعريف الجلسة (Session Context)
الجلسة تُعرّف بالبيانات التالية:
- `classroom_id`
- `subject_id`
- `date`
- `day_number`
- `period_number`
- `period_code`

ملاحظة v1:
- الواجهة تعرض day_number + period_number، ولكن التخزين/الـ API يعتمد على `period_code`.
- عند الدخول من Teacher Schedule، يجب أن تكون كل هذه القيم جاهزة وممرّرة للصفحة.

### period_code (Decision)
سيتم توليد `period_code` بشكل مشتق من التقويم:
- Format: `Y{year_id}-S#-W#-D#-P#`
- Example: `Y2026-S1-W12-D2-P3`

الهدف:
- ثبات البيانات حتى لو تغير جدول المعلم لاحقًا
- سهولة التقارير حسب week/day/period

## 3) فئات النقاط (20 نقطة)
تم اعتماد دمج Book + Participation.

الفئات في v1 (4 فئات × 5 = 20):
1) Attendance (0..5) — default 5
2) Book+Participation (0..5) — default 5
3) Homework (0..5) — default 5
4) Behavior (0..5) — default 5

Session Total = مجموع الأربع فئات (0..20)

## 4) قواعد التفاعل (Interaction Rules)
### Tap Cycle (Confirmed)
القيمة تتغير بالضغط:
- 5 → 3 → 0 → 5

### Attendance Lock (Confirmed)
إذا Attendance = 0 (Absent):
- يتم قفل باقي الفئات تلقائيًا إلى 0

### Attendance Details
Attendance ليست فقط score:
- `status`: present | absent | late | left_early
- `note`: تعليق المعلم
ومع ذلك، `score` يبقى رقمًا (0..5) لضمان التوحيد والتقارير.

### Behavior
Behavior له نقاط أيضًا في نظام آخر (reward_sys)، لكن هنا الهدف “daily report”.
v1 سيخزن Behavior كقيمة 0..5 داخل نظام Classroom Records الجديد (cr_*).

### Points Philosophy (Decision)
النقاط نوعين:
- Classroom Records Total (Lifetime Achievement): محفوظة في `cr_student_periods.total_score` ولا تنقص.
- Reward System Balance (Wallet/Currency): رصيد قابل للصرف داخل reward_sys (يزيد وينقص حسب المكافآت/الشراء).

واجهة المستخدم تعرض:
- Level / Lifetime (من lifetime totals)
- Wallet (من reward balance)
- Combined Power Score (عرض بصري فقط) = lifetime + wallet

## 5) UI Components (Reusable)
### A) Session Context Bar
مكوّن واحد reusable لعرض/اختيار session context:
- في standalone: interactive
- في teacher schedule: readonly/locked

Props المقترحة:
- `modelValue`: `{ classroom_id, subject_id, date, day_number, period_number, period_code? }`
- `mode`: `interactive | readonly`
- `source`: `standalone | teacher_schedule`
- `options`: `{ classrooms?, subjects? }` (اختياري، للسماح بتمرير البيانات من خارج النظام بدل fetch داخلي)

### B) Classroom Records Grid (The Feature Container)
مكوّن حاوي قابل لإعادة الاستخدام يقبل options من الخارج:
- `context` (mandatory)
- `categories` (optional override)
- `readOnly` (optional)
- `apiBase` (optional; default `/api/cr`)

## 6) التخزين (DB) — cr_* schema
v1 سيستخدم جداول جديدة بالكامل ببادئة `cr_` حسب المخطط:
- `cr_sessions`
- `cr_student_periods`
- `cr_category_mappings`
- `cr_scores`

تخزين v1 (20 نقطة):
- Attendance (تقارير الحضور):
  - `cr_student_periods.attendance_status` (مصدر التقارير)
  - `cr_student_periods.attendance_score` (للـ UX/الحسابات)
  - `cr_student_periods.attendance_note`
- Total:
  - `cr_student_periods.total_score` (0..20)
- Categories:
  - `cr_category_mappings` (seed افتراضي: book_participation, homework, behavior)
  - `cr_scores.numeric_value` (0..5) لكل فئة

### قيود فريدة (Unique Constraints)
- داخل `cr_student_periods`:
  - `UNIQUE (school_id, year_id, date, period_code, student_id)`

## 7) API (Fast loading + Bulk save)
### v1 endpoints المطلوبة
1) `POST /api/cr/init-session`
- Input: `{ classroom_id, subject_id, teacher_id, date, period_code, day_number, period_number }`
- Output:
  - session
  - roster students
  - student_periods + scores (إن لم توجد: create defaults)

2) `PATCH /api/cr/batch`
- Input: `{ updates: [{ student_period_id, attendance_status?, attendance_score?, attendance_note?, total_score?, scores?: [{ mapping_id, numeric_value? }] }] }`

3) `GET /api/cr/stats`
- Params: scope + date range + subject filter
- Output: totals per student
