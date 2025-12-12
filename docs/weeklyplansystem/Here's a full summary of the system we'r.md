  Here's a full summary of the system we're about to build, organized for clarity and structured in a way that can be given to an AI assistant (or development team) for implementation. It includes:

* The system purpose
* Folder structure suggestion
* How to handle existing tables
* New tables needed
* Your key logic
* Questions answered
* Prompt to AI

---

### ✅ System Goal

Build a modular **Weekly Plan System** that allows teachers to plan sessions (lesson, quiz, exam, etc.) per subject and class, based on the **school's academic year, semester, and schedule**, while keeping flexibility even if the actual schedule changes.

---

### 🗂 Folder/Module Name

```
weeklyplansystem/
```

---

### 🔁 Integration with Existing Tables

1. **Academic Years**

   ```php
   Schema::create('academic_years', function (Blueprint $table) {
       $table->id();
       ...
   });
   ```

2. **Classroom-Subject-Teacher Binding**

   ```php
   Schema::create('classroom_subject_teachers', function (Blueprint $table) {
       $table->id(); // ← this is your cst_id
       $table->foreignId('academic_year_id')->constrained();
       ...
   });
   ```

3. **Fixed Schedule Table**
   You likely already have this.

   ```
   schedule_periods
   - id
   - cst_id
   - day_number (1-7)
   - period_number
   - fixed_period_code (e.g. 25.1.2.5)
   ```

---

### 🆕 New Tables to Create

#### 1. `weekly_plans`

A weekly plan for a class-subject in a specific week.

```php
Schema::create('weekly_plans', function (Blueprint $table) {
    $table->id();
    $table->foreignId('academic_year_id')->constrained();
    $table->integer('semester_number');
    $table->integer('week_number');
    $table->foreignId('cst_id')->constrained('classroom_subject_teachers');
    $table->timestamps();
});
```

#### 2. `weekly_plan_sessions`

Custom sessions that belong to a weekly plan.

```php
Schema::create('weekly_plan_sessions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('weekly_plan_id')->constrained();
    $table->integer('session_index'); // 1,2,3,...
    $table->string('period_code'); // e.g. 25.1.2.5
    $table->enum('type', ['lesson', 'quiz', 'exam', 'extra', 'note']);
    $table->string('title');
    $table->json('data')->nullable(); // extra info if needed
    $table->timestamps();
});
```

---

### 🤖 Prompt for AI (ready to send)

```
You are assisting in building a Laravel module called "weeklyplansystem". The goal is to let teachers create and customize session plans for each week based on academic year, subject, and class schedule.

🧠 Here's what you need to know:

1. There is a table `academic_years`. We use `academic_year_id`.
2. We use a table `classroom_subject_teachers`. The ID of this table is called `cst_id`.
3. A session is assigned to a specific period using `period_code` format like: `25.1.2.5` → meaning academic_year 25, semester 1, week 2, day 5.
4. The system must allow flexibility in editing weekly plans and sessions, even if the school schedule later changes.
5. Each `weekly_plan` is for a specific academic_year_id + semester + week + cst_id.
6. Each `weekly_plan_session` contains a session_index (1,2...), a period_code, and a type (lesson, quiz, etc.).
7. There is an existing table for fixed schedule periods. This can optionally be used to populate weekly plans automatically.

🎯 Your Task:
- Understand the relationships.
- Plan out the Laravel migrations, models, and API routes for managing weekly plans and sessions.
- Do NOT write any code until you explain:
    - Your full understanding of the data relationships
    - Steps you will take
    - Confirm what needs clarification
```

---

### ✅ Your Answered Questions

| Question             | Answer                                             |
| -------------------- | -------------------------------------------------- |
| Schedule Integration | Let system optionally copy from fixed schedule     |
| Week Range           | Configurable (default: 1–18 or 1–36)               |
| Session Index Logic  | Reset per week (starts from 1)                     |
| Period Code Format   | academic\_year.semester.week.day (e.g. `25.1.2.5`) |
| Initial Data Source  | Can copy from `lessons` table or start empty       |

---

### 🔑 Key Benefits

* Even if the fixed schedule changes, the plan remains unchanged unless manually edited.
* Easy linking using `period_code + cst_id`.
* Fully editable structure per class/subject without affecting others.

---
 