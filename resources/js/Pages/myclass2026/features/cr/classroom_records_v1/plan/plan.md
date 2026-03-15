# Classroom Records v1 — Plan

## هدف الميزة
إعادة بناء Classroom Records داخل `myclass2026/features/cr/classroom_records_v1` بطريقة:
- تحميل سريع جدًا
- ممتعة للمعلم والطلاب (واجهة Cards + tap actions)
- قابلة لإعادة الاستخدام داخل Teacher Schedule (الدخول للجلسة مباشرة بدون اختيار يدوي)

## النطاق (v1)
صفحة “Classroom Record” للجلسة الواحدة وتسجيل نقاط لكل طالب بسرعة.

### فئات النقاط (المجموع 20)
بدل 5 فئات، سيتم دمج:
- Book + Participation = فئة واحدة

الفئات في v1 (4 فئات × 5 = 20):
1) Attendance (0..5) — الافتراضي 5  
2) Book+Participation (0..5) — الافتراضي 5  
3) Homework (0..5) — الافتراضي 5  
4) Behavior (0..5) — الافتراضي 5  

المجموع (Session Total) = Attendance + BookParticipation + Homework + Behavior (0..20)

## تجربة الاستخدام (UX)
### 1) Session Context (Top Bar) — مكوّن قابل لإعادة الاستخدام
نحتاج مكوّن علوي reusable لعرض/اختيار سياق الجلسة:
- classroom selector
- subject
- date
- day_number
- period_number
- (period_code أو ما يعادله)

**مهم:** نفس المكوّن سيتم استخدامه لاحقًا في Teacher Schedule بحيث:
- في حالة الدخول من الجدول: يتم تمرير الـ context جاهزًا ويصبح المكوّن “readonly / locked” (بدون اختيار يدوي).
- في حالة الدخول من صفحة مستقلة: يكون “interactive” لاختيار القيم.

اقتراح API للمكوّن:
- Props:
  - `modelValue` (object: `{ classroom_id, subject_id, date, day_number, period_number, period_code? }`)
  - `mode`: `'interactive' | 'readonly'`
  - `source`: `'standalone' | 'teacher_schedule'` (اختياري لأهداف UI)
- Emits:
  - `update:modelValue`
  - `ready` (عند اكتمال القيم المطلوبة)

### 2) Student Cards
عرض الطلاب على شكل Cards (أبسط من reward_sys):
- الاسم + الصورة (إن وجدت)
- Panel افتراضي يعرض: **Session Total (0..20)**
- 4 أزرار كبيرة (icons) لكل فئة، تعرض قيمة الفئة (افتراضي 5)
- تغيير القيمة يكون سريع (Tap Cycle) لتقليل وقت الإدخال:
  - اقتراح v1: 5 → 3 → 0 → 5
  - يمكن لاحقًا إضافة “long-press” لفتح Slider 0..5

### 3) Modes / Options داخل الكارت (للمستقبل القريب)
الطالب يحتاج عرض أكثر من نوع مجموع:
- Session Total (افتراضي)
- Overall Points (حسب فلتر)

فلتر Overall Points (خيارات):
- Scope:
  - All subjects
  - Only my subject (subject_id الحالي)
- Time Range:
  - Current day
  - Current week
  - Current period_code
  - Custom range (from/to)

UI اقتراح:
- Dropdown عام أعلى الصفحة: “Card Panel Mode”
- أو toggle داخل الكارت (لكن الأفضل عام لتقليل ازدحام الواجهة)

## البيانات (Data Model) — قرار v1
سيتم إنشاء جداول جديدة بالكامل ببادئة `cr_` (بدل استخدام `classroom_records`) لتكون جاهزة للتقارير ومرنة لتعدد الفئات.

### تمثيل v1 داخل DB (اقتراح عملي)
v1 يعتمد Hybrid من البداية:
- `cr_student_periods` = مصدر تقارير الحضور (attendance_status) + total_score
- `cr_category_mappings` = تعريف فئات المدرسة
- `cr_scores` = قيم الفئات (long format)

قيود التقارير (مؤكدة):
- لا يسمح بتكرار student في نفس `date + period_code`:
  - `UNIQUE (school_id, year_id, date, period_code, student_id)`
- التقارير تعتمد على `attendance_status` كمصدر الحقيقة، و `attendance_score` للـ UX/الحسابات

## API (سريع التحميل)
الموجود حاليًا:
- `GET /api/classroom-records` يرجع فقط الصفوف الموجودة
- `PATCH /api/classroom-records/{id}` يحفظ صف واحد

هذا لا يحقق “تحميل سريع بدون خطوات”.

### endpoints المقترحة لـ v1
1) `POST /api/cr/init-session`
- Input: `{ classroom_id, subject_id, teacher_id, date, day_number, period_number, period_code }`
- Output:
  - session + roster + student_periods + scores (defaults created if missing)

2) `PATCH /api/cr/batch`
- Input: `{ updates: [...] }`
- Output: success + updated rows

3) `GET /api/cr/stats`
لاستخراج Overall Points بسرعة:
- Params:
  - `classroom_id` (اختياري)
  - `subject_id` (اختياري)
  - `teacher_id` (اختياري)
  - `from`, `to` (dates)
  - `period_code` (اختياري)
- Output:
  - `{ by_student: { [student_id]: { total_sum } } }`
تنفيذ SQL: `SUM(total) GROUP BY student_id` مع filters.

## الأداء (Performance)
مبادئ v1:
- تحميل واحد للجلسة: init-session يرجع roster + records جاهزة
- حفظ مجمّع:
  - Local state updates + debounce batch save (كل 800ms مثلًا)
  - إرسال فقط التغييرات (dirty rows)
- تجنب re-render الكبير:
  - استخدام keyed list + computed totals محليًا

## التكامل مع Teacher Schedule
الهدف: المعلم يضغط على أي session في الجدول → يفتح نفس صفحة Classroom Records مع context جاهز.

تصميم URL:
- `/classroom-manager?classroom_id=..&subject_id=..&date=YYYY-MM-DD&period_code=Y{year_id}-S#-W#-D#-P#`

قرار period_code:
- يتم توليده من التقويم (year_id+semester+week+day+period) لضمان ثبات التاريخ حتى لو تغير الجدول لاحقًا.

SessionContextBar:
- إذا وجد query params كاملة → mode=readonly + emits ready مباشرة
- إذا ناقصة → mode=interactive
