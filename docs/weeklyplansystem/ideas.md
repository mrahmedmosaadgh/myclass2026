# Weekly Plan System - Understanding & Design

## System Overview
The weekly plan system is designed to provide teachers with a flexible, editable weekly plan for managing their sessions per subject and class. It operates independently from the fixed schedule while maintaining a soft reference to it.

## Key Relationships & Architecture

### Core Entities
1. **academic_years** - Stores academic year information
2. **classroom_subject_teachers (cst)** - The central pivot table that connects:
   - classroom_id (specific class)
   - subject_id (specific subject)
   - teacher_id (assigned teacher)
   - academic_year_id
   - classes_per_week (determines how many sessions per week)

3. **weekly_plans** - Container for weekly plans
   - Links to cst_id (classroom_subject_teachers.id)
   - Contains academic_year_id, semester_number, and week_number
   - One plan per subject/class/week combination

4. **weekly_plan_sessions** - Individual editable sessions
   - Belongs to weekly_plan_id
   - Contains session_index (1, 2, 3... within that week)
   - period_code format: academic_year.semester.week.day (e.g., "25.1.2.5")
   - Customizable type, title, and data (JSON)

### Schedule Independence Strategy
- **period_code** is a string-based soft reference, NOT a foreign key
- When the fixed schedule changes, only the period_code mapping needs updating
- The weekly plan structure remains intact regardless of schedule changes
- This provides stability while allowing schedule flexibility

### Session Types & Flexibility
- **lesson** - Regular lesson content
- **quiz** - Assessment sessions
- **exam** - Formal examinations
- **extra** - Additional sessions
- **note** - Notes or reminders

### Data Structure Benefits
- JSON field for data allows flexible content storage (materials, zoom links, homework, skill tags)
- Editable per session without affecting the underlying structure
- Maintains historical data even if original lessons change
- Configurable week range (default 1-18 or 1-36)

## Implementation Boundaries
- All code under `/weeklyplansystem` directory
- Subdirectories: migrations, models, controllers, requests, VuePages
- Uses existing Laravel structure and conventions
- Integrates with current authentication and authorization system

## Workflow Understanding
1. Teacher selects a subject/class combination (cst_id)
2. System can optionally generate initial weekly plan based on fixed schedule
3. Teacher can edit each session independently:
   - Reorder sessions
   - Add quiz/exam sessions
   - Skip sessions
   - Modify content
4. Changes persist regardless of schedule modifications
5. period_code can be updated if schedule changes, maintaining plan integrity

## Updated Schema Based on Requirements
- **weekly_plans** table: academic_year_id, semester_number, week_number, cst_id
- **weekly_plan_sessions** table: weekly_plan_id, session_index, period_code, type, title, data(JSON)
- **period_code format**: academic_year.semester.week.day (e.g., "25.1.2.5")
