# Weekly Plans Database Relationship Diagram

## Entity Relationship Diagram

```
+---------------------+       +----------------------+       +---------------------+
| academic_years      |       | classroom_subject_   |       | curriculum_lessons  |
|---------------------|       | teachers (cst)       |       |---------------------|
| id                  |       |----------------------|       | id                  |
| school_id           |       | id                   |       | school_id           |
| name                |       | school_id            |       | curriculum_id       |
| start_date          |       | academic_year_id     |       | selected            |
| end_date            |       | classroom_id         |       | topic_number        |
| is_current          |       | subject_id           |       | topic_title         |
+---------------------+       | teacher_id           |       | lesson_number       |
        |                     | classes_per_week     |       | lesson_title        |
        |                     +----------------------+       | page_number         |
        |                              |                    | description          |
        |                              |                    | ...                  |
        |                              |                    +---------------------+
        |                              |                              |
        |                              |                              |
+---------------------+       +----------------------+                |
| weekly_plans        |       | teachers             |                |
|---------------------|       |----------------------|                |
| id                  |       | id                   |                |
| academic_year_id ---|-----> | user_id              |                |
| semester            |       | name                 |                |
| week_number         |       | email                |                |
| period_number       |       | phone                |                |
| cst_id -------------|-----> | ...                  |                |
| curriculum_lesson_id|-----> +----------------------+                |
| code                |                                               |
| teacher_id ----------|------------------------------------------------+
| notes               |
+---------------------+
        |
        | (non-foreign key relationship via code)
        |
        v
+---------------------+       +----------------------+       +---------------------+
| period_activities   |       | schedules            |       | calendars           |
|---------------------|       |----------------------|       |---------------------|
| id                  |       | id                   |       | id                  |
| schedule_id --------|-----> | copy_id              |       | school_id           |
| calendar_id --------|---------------------------------> | academic_year_id     |
| teacher_id          |       | cst_id               |       | semester_number     |
| teacher_substitute_id       | school_id            |       | week_number         |
| teacher_present     |       | day                  |       | date                |
| teacher_plan        |       | period_number        |       | ...                 |
| period_status       |       | ...                  |       +---------------------+
| event_id            |       +----------------------+
| lesson_code         |
| lesson_notes        |
| ...                 |
+---------------------+
        |
        |
        v
+---------------------+
| student_period_     |
| records             |
|---------------------|
| id                  |
| period_activity_id  |
| student_id          |
| attendance_status   |
| late_minutes        |
| homework_completed  |
| ...                 |
+---------------------+
```

## Key Relationships Explained

1. **weekly_plans to academic_years**: Each weekly plan belongs to an academic year.
   - Foreign key: `weekly_plans.academic_year_id` → `academic_years.id`

2. **weekly_plans to classroom_subject_teachers (cst)**: Each weekly plan is for a specific classroom-subject-teacher combination.
   - Foreign key: `weekly_plans.cst_id` → `classroom_subject_teachers.id`

3. **weekly_plans to curriculum_lessons**: Each weekly plan can optionally be linked to a specific curriculum lesson.
   - Foreign key: `weekly_plans.curriculum_lesson_id` → `curriculum_lessons.id`

4. **weekly_plans to teachers**: Each weekly plan is created by a teacher.
   - Foreign key: `weekly_plans.teacher_id` → `teachers.id`

5. **period_activities to weekly_plans**: Period activities can be linked to weekly plans via the `lesson_code` field.
   - This is a non-foreign key relationship (no constraint) for flexibility.
   - `period_activities.lesson_code` matches `weekly_plans.code`

6. **period_activities to schedules**: Each period activity is linked to a schedule.
   - Foreign key: `period_activities.schedule_id` → `schedules.id`

7. **period_activities to calendars**: Each period activity is linked to a calendar date.
   - Foreign key: `period_activities.calendar_id` → `calendars.id`

8. **student_period_records to period_activities**: Student attendance and participation records are linked to period activities.
   - Foreign key: `student_period_records.period_activity_id` → `period_activities.id`

## Code Field Format

The `code` field in `weekly_plans` and `lesson_code` field in `period_activities` follow this format:

```
academic_year_id.semester.week_number.period_number
```

Example: `12.1.1.1` represents:
- Academic year ID: 12
- Semester: 1
- Week number: 1
- Period number: 1

This unique code allows for easy linking between weekly plans and period activities without requiring a foreign key constraint.
