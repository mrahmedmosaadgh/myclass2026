# Weekly Plans Implementation Checklist

Use this checklist to ensure all components of the Weekly Plans feature have been properly implemented.

## Database Migrations

- [x] Create `weekly_plans` table migration
  - File: `database/migrations/2025_05_21_000000_create_weekly_plans_table.php`
  - Includes all required fields and relationships
  - Includes unique constraints and indexes

- [x] Add `lesson_code` to `period_activities` table migration
  - File: `database/migrations/2025_05_21_000001_add_lesson_code_to_period_activities.php`
  - Adds the field with appropriate comment and index

- [ ] Run migrations
  ```
  php artisan migrate
  ```

## Models

- [x] Create `WeeklyPlan` model
  - File: `app/Models/WeeklyPlan.php`
  - Includes all fillable fields
  - Defines relationships to other models
  - Includes helper method for code generation

- [x] Update `PeriodActivity` model
  - Add `lesson_code` to fillable array
  - Add relationship to `WeeklyPlan` model

## Controllers

- [x] Create `WeeklyPlanController`
  - File: `app/Http/Controllers/WeeklyPlanController.php`
  - Implements all CRUD operations
  - Includes additional API methods for filtering

- [x] Update `PeriodActivityController`
  - Add `lesson_code` to validation rules
  - Handle the relationship with weekly plans

## Routes

- [x] Create routes for Weekly Plans
  - File: `routes/weekly_plans.php`
  - Includes resource routes for CRUD operations
  - Includes API routes for filtering

- [x] Include routes file in main web.php
  - Add `include dirname(__DIR__) . '/routes/weekly_plans.php';`

## Frontend Views

- [ ] Create Weekly Plans index view
  - File: `resources/js/Pages/WeeklyPlans/Index.vue`
  - Displays list of weekly plans with filters
  - Includes pagination and sorting

- [ ] Create Weekly Plans create/edit form
  - File: `resources/js/Pages/WeeklyPlans/Create.vue`
  - File: `resources/js/Pages/WeeklyPlans/Edit.vue`
  - Includes all required fields with validation
  - Handles form submission and error display

- [ ] Create Weekly Plans show view
  - File: `resources/js/Pages/WeeklyPlans/Show.vue`
  - Displays detailed information about a weekly plan
  - Shows linked period activities

- [ ] Update Period Activity form
  - Add field for selecting a weekly plan
  - Automatically populate `lesson_code` based on selection

## API Endpoints

- [x] Implement API endpoints for Weekly Plans
  - `/api/weekly-plans/by-academic-year/{academicYearId}`
  - `/api/weekly-plans/by-semester/{academicYearId}/{semester}`
  - `/api/weekly-plans/by-week/{academicYearId}/{semester}/{weekNumber}`
  - `/api/weekly-plans/by-cst/{cstId}`

- [ ] Update Period Activities API to handle `lesson_code`
  - Ensure `lesson_code` is included in responses
  - Allow filtering by `lesson_code`

## Testing

- [ ] Create unit tests for WeeklyPlan model
  - Test relationships
  - Test code generation method

- [ ] Create feature tests for WeeklyPlanController
  - Test CRUD operations
  - Test API endpoints

- [ ] Test integration with PeriodActivity
  - Test linking period activities to weekly plans
  - Test retrieving weekly plans from period activities

## Documentation

- [x] Create integration documentation
  - File: `help_ok/doc/weekly_plans_integration.md`
  - Explains the relationship between tables
  - Describes model relationships and methods

- [x] Create database diagram
  - File: `help_ok/doc/weekly_plans_diagram.md`
  - Shows the relationships between tables
  - Explains the code field format

- [x] Create usage guide
  - File: `help_ok/doc/weekly_plans_usage_guide.md`
  - Provides examples of how to use the feature
  - Includes API usage examples

- [x] Create implementation checklist
  - File: `help_ok/doc/weekly_plans_implementation_checklist.md`
  - Lists all components that need to be implemented
  - Can be used to track progress

## Deployment

- [ ] Run migrations on production server
  ```
  php artisan migrate --force
  ```

- [ ] Clear caches
  ```
  php artisan optimize:clear
  ```

- [ ] Verify all components are working correctly
  - Test creating weekly plans
  - Test linking period activities to weekly plans
  - Test API endpoints

## Additional Features (Future)

- [ ] Implement bulk creation of weekly plans
  - Allow creating multiple weekly plans at once
  - Provide template-based creation

- [ ] Add reporting features
  - Curriculum coverage report
  - Teacher adherence report

- [ ] Add notification system
  - Notify teachers when weekly plans are due
  - Notify administrators about curriculum coverage

## Notes

- The `code` field in `weekly_plans` and `lesson_code` field in `period_activities` follow the format: `academic_year_id.semester.week_number.period_number`
- This implementation allows period activities to be created before or after weekly plans exist
- The relationship between `period_activities` and `weekly_plans` is not enforced at the database level for flexibility
