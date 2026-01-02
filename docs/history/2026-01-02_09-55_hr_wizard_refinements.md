# 2026-01-02 09:55 | HR Setup Wizard Refinements & School Management Backend

## Overview
Comprehensive refinement of the HR Setup Wizard (`/admin/hr/setup-wizard`) to transform it from a one-time setup tool into a flexible school management system. The wizard now supports HR-specific workflows, allows managing multiple schools independently, and provides complete customization of educational structure per school.

### Key Objectives Achieved
1. **HR-Optimized Workflow**: HR managers can now edit their existing data rather than redundantly creating new accounts
2. **Multi-School Support**: Backend infrastructure for listing, selecting, and managing multiple schools
3. **Independent Customization**: Each school can have completely different stages, grades, subjects, and classroom sections
4. **Safety & Data Integrity**: Added extensive safety checks and fixed critical bugs

## Detailed Changes

### Backend Enhancements

#### HRSetupWizardController (`app/Http/Controllers/HRSetupWizardController.php`)

**1. Enhanced `index()` Method**
- **Query Parameter Support**: Now accepts `?school_id=X` to load specific school in edit mode
- **School Metadata**: Returns comprehensive setup progress for each school:
  ```php
  [
    'id' => 1,
    'name' => 'Main School',
    'has_stages' => true,    // ✓ Has educational structure
    'has_subjects' => true,   // ✓ Has subjects configured
    'has_classrooms' => false, // ✗ Needs classroom setup
    'can_delete' => false     // Has students/teachers
  ]
  ```
- **Role-Based Access**:
  - HRs see only their assigned schools
  - Super Admins see all schools for management

**2. Data Handling Bug Fixes**
- **Problem**: Controller was using `json_decode()` on attributes already cast to arrays in models
- **Impact**: Caused `TypeError` in PHP 8+
- **Solution**: Removed all `json_encode`/`json_decode` calls, using array notation:
  ```php
  // Before (❌ Broken)
  json_decode($school->data ?? '{}')->address
  
  // After (✓ Fixed)
  $school->data['address'] ?? ''
  ```
- **Files Fixed**:
  - Lines 57-69: School data access in `index()`
  - Lines 138-157: School update in `store()`
  - Lines 204-224: School creation in `createHR()` and `createSchool()`

**3. Safe Update/Sync Logic**
- **Stages & Grades**: `updateOrCreate` by name to avoid duplicates
- **Subjects**: Sync with soft-delete protection
- **Classrooms**: Smart sync that preserves classrooms with students
- **Safety Checks**: Won't delete items with dependent data (students, schedules)

**4. School Deletion (`destroy()` method)**
```php
public function destroy($id)
{
    // 1. Verify ownership (HR or Admin)
    // 2. Check for students → Block if found
    // 3. Check for teachers → Block if found
    // 4. Delete school + cascade (stages, subjects, classrooms)
}
```

**5. Expanded Configuration**
- **Default Sections**: Extended from `['A', 'B', 'C', 'D', 'E']` to full alphabet A-Z plus common names:
  ```php
  'sections' => ['A', 'B', ..., 'Z', 'Boys', 'Girls', 'Mixed']
  ```

#### Routes (`routes/r_hr.php`)
- **Added**: `DELETE /admin/hr/schools/{id}` → `HRSetupWizardController@destroy`
- **Updated Middleware**: All wizard routes now accessible by `admin|hr_admin|super_admin`

#### Models (`app/Models/Stage.php`)
- **Critical Fix**: Added missing `grades()` relationship
  ```php
  public function grades()
  {
      return $this->hasMany(Grade::class);
  }
  ```
- **Impact**: Fixes 500 error when switching schools (was trying to eager-load `grades` but relationship didn't exist)

### Frontend Improvements

#### SetupWizard.vue (`resources/js/Pages/my_class/super_admin/HR/SetupWizard.vue`)

**Step 1: HR Profile (Read-Only Mode)**
- **Before**: Showed "Create New User" toggle, editable user fields
- **After**: 
  - Displays current user name/email as **read-only**
  - Shows informational banner: "You are currently logged in as [Name]"
  - Only allows editing HR public name, phone, address
  - Prevents redundant account creation

**Step 2: School Information (Context Selector)**
- **Added**: School selector dropdown at top of step
  ```vue
  <q-select
    v-model="selectedSchoolId"
    :options="schools"
    label="Choose a School"
    @update:model-value="switchSchoolContext"
  />
  ```
- **Behavior**: Selecting a school reloads wizard with that school's data pre-filled
- **Backend Integration**: Calls `MySchoolsController::selectSchool` then redirects to wizard with `?school_id=X`

**Step 5: Classrooms (Dynamic Sections)**
- **Before**: Fixed checkboxes for A, B, C, D, E only
- **After**: Dynamic `q-select` with:
  - **Multi-select**: Choose multiple sections
  - **Use Chips**: Visual chips for selected sections
  - **Custom Input**: Type any value (e.g., "F", "Special", "Advanced") and press Enter to add
  ```vue
  <q-select
    v-model="classroom.sections"
    :options="defaultSections"  // A-Z + Boys/Girls/Mixed
    use-input
    use-chips
    multiple
    new-value-mode="add-unique"
  />
  ```

**Script Updates**
- **Props**: Added `schools` (list) and `editingSchoolId` (current context)
- **Refs**: Added `selectedSchoolId` initialized from existing setup
- **Methods**: 
  - `switchSchoolContext(schoolId)`: Posts to select endpoint then reloads wizard
  - Updated `formData` initialization to pre-fill all 6 steps from `existingSetup`

## Bug Fixes Summary

| Bug | Cause | Fix | Impact |
|-----|-------|-----|--------|
| 500 Error on school switch | Missing `Stage::grades()` relationship | Added relationship method | ✓ Can now switch schools without error |
| JSON decode errors | Double-decoding array-cast attributes | Removed `json_decode()` calls | ✓ Data loads correctly |
| Super Admin 403 | Routes only allowed `admin\|hr_admin` | Added `super_admin` to middleware | ✓ Admins can access wizard |
| Limited sections | Hardcoded A-E checkboxes | Dynamic input with A-Z defaults | ✓ Can add custom sections |

## Architecture Changes

### Data Flow (Before vs After)

**Before**:
```
User → Wizard → Create School → Exit
(One-time setup only)
```

**After**:
```
User → School List → Select School → Wizard (Edit Mode) → Update
                   ↓
                Create New → Wizard (Create Mode) → Create
```

### Database Schema Validation
Confirmed each school's data is independent:
- `stages.school_id`
- `grades.school_id` 
- `subjects.school_id`
- `classrooms.school_id`
- `academic_years.school_id`

Reference implementation: `database/seeders/InitialSchoolStructureSeeder.php`

## Implementation Status

### ✅ Completed
- [x] Read-only HR profile step
- [x] School selector dropdown
- [x] Custom sections input
- [x] Backend school list with metadata
- [x] Safe deletion with checks
- [x] Bug fixes (JSON, relationships, middleware)

### 🚧 In Progress
- [ ] Frontend school list table/cards view
- [ ] "Add New School" button
- [ ] Delete confirmation modal
- [ ] Edit/Delete action buttons

### 📝 Planned (See `implementation_plan.md`)
- Full school management interface  
- Independent per-school customization UI
- Progress indicators for each school

## Testing Recommendations

1. **School Switching**: Select different schools, verify data loads correctly
2. **Custom Sections**: Add sections like "F", "Advanced", "Remedial" 
3. **Delete Safety**: Try deleting school with students → Should fail with error message
4. **Multi-School Setup**: Create 2+ schools with different configurations:
   - School A: Primary only (Grades 1-6), sections A-E
   - School B: Full structure (1-12), sections Boys/Girls
   - Verify each maintains independent data

## References
- Implementation Plan: `.gemini/antigravity/brain/.../implementation_plan.md`
- Seeder Reference: `database/seeders/InitialSchoolStructureSeeder.php`
- Task Breakdown: `.gemini/antigravity/brain/.../task.md`
