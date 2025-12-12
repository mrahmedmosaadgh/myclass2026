# Weekly Plans Documentation Index

This index provides links to all documentation related to the Weekly Plans feature in the MyClass7 system.

## Overview

The Weekly Plans feature allows teachers to plan their curriculum lessons for each week and period, and then link these plans to actual classroom activities. This creates a connection between the planned curriculum and what is actually taught in the classroom.

## Documentation Files

1. [**Integration Documentation**](weekly_plans_integration.md)
   - Comprehensive overview of the Weekly Plans feature
   - Database schema details
   - Model relationships
   - Controller functionality
   - Routes and API endpoints
   - Usage examples

2. [**Database Relationship Diagram**](weekly_plans_diagram.md)
   - Visual representation of the database relationships
   - Explanation of key relationships
   - Description of the code field format

3. [**Usage Guide**](weekly_plans_usage_guide.md)
   - Step-by-step instructions for creating weekly plans
   - Examples of linking period activities to weekly plans
   - Reporting and analysis features
   - API usage examples
   - Best practices

4. [**Implementation Checklist**](weekly_plans_implementation_checklist.md)
   - List of all components that need to be implemented
   - Status tracking for each component
   - Deployment instructions
   - Future feature ideas

## Key Files Created

### Migrations

- `database/migrations/2025_05_21_000000_create_weekly_plans_table.php`
- `database/migrations/2025_05_21_000001_add_lesson_code_to_period_activities.php`

### Models

- `app/Models/WeeklyPlan.php`
- Updates to `app/Models/PeriodActivity.php`

### Controllers

- `app/Http/Controllers/WeeklyPlanController.php`
- Updates to `app/Http/Controllers/Api/PeriodActivityController.php`

### Routes

- `routes/weekly_plans.php`
- Updates to `routes/web.php`

## Database Schema Summary

### Weekly Plans Table

```
weekly_plans
├── id
├── academic_year_id
├── semester
├── week_number
├── period_number
├── cst_id
├── curriculum_lesson_id
├── code
├── teacher_id
├── notes
├── created_at
├── updated_at
└── deleted_at
```

### Period Activities Table Update

```
period_activities
├── ... (existing fields)
├── lesson_code (new field)
└── ... (existing fields)
```

## Key Relationships

- Weekly plans are linked to academic years, classroom-subject-teachers, curriculum lessons, and teachers.
- Period activities are linked to weekly plans via the `lesson_code` field (non-foreign key relationship).
- The `code` field in weekly plans follows the format: `academic_year_id.semester.week_number.period_number`.

## Next Steps

1. Complete the frontend implementation (views and components)
2. Write tests for the new functionality
3. Deploy to production
4. Train users on the new feature

## Support

For questions or issues related to the Weekly Plans feature, please contact the development team.
