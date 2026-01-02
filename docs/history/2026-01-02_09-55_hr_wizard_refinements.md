# 2026-01-02 09:55 | HR Setup Wizard Refinements & School Management Backend

## Overview
Refined the HR Setup Wizard to support HR workflows and laid groundwork for comprehensive school management system.

## Changes Made

### Backend Enhancements

#### HRSetupWizardController (`app/Http/Controllers/HRSetupWizardController.php`)
- **Enhanced `index` method**: Now returns schools list with setup progress indicators (`has_stages`, `has_subjects`, `has_classrooms`, `can_delete`)
- **Added `destroy` method**: Safe school deletion with checks for students/teachers
- **Fixed data handling**: Removed incorrect `json_encode`/`json_decode` usage (models auto-cast to array)
- **Expanded default sections**: Now includes A-Z plus Boys, Girls, Mixed

#### Routes (`routes/r_hr.php`)
- Added `DELETE /admin/hr/schools/{id}` route for school deletion
- Ensured all wizard routes accessible by `admin|hr_admin|super_admin` roles

#### Models
- **Stage.php**: Added missing `grades()` relationship (fixes 500 error)

### Frontend Improvements

#### SetupWizard.vue (`resources/js/Pages/my_class/super_admin/HR/SetupWizard.vue`)
- **Step 1 (HR Profile)**: Converted to read-only for authenticated HRs
- **Step 2 (School Info)**: Added school selector dropdown
- **Step 5 (Classrooms)**: Replaced checkbox sections with dynamic `q-select` allowing custom values (e.g., "F", "Special")

## Bug Fixes
1. **500 Error on School Switch**: Added `grades()` relationship to `Stage` model
2. **JSON Decode Errors**: Fixed accessing array-cast attributes correctly
3. **Middleware Access**: Updated routes to include `super_admin` role

## What's Next
Frontend implementation of school list view with full CRUD operations (planned in `implementation_plan.md`).

## Testing Notes
- School selection dropdown works for both HRs (their schools) and Super Admins (all schools)
- Custom section input allows typing any value and pressing Enter to add
- Backend safely prevents deletion of schools with students/teachers
