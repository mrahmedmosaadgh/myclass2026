# Weekly Timetable System - Complete Reference Guide

## 📚 Table of Contents
1. [System Overview](#system-overview)
2. [Core Data Structures](#core-data-structures)
3. [The Three Main Tables](#the-three-main-tables)
4. [How Everything Works Together](#how-everything-works-together)
5. [Recommended Workflow](#recommended-workflow)
6. [Key Features](#key-features)
7. [Common Operations](#common-operations)
8. [Data Integrity Rules](#data-integrity-rules)
9. [Troubleshooting](#troubleshooting)

---

## System Overview

The Weekly Timetable System is a comprehensive school scheduling solution that manages:
- **Subject-Teacher Assignments** (Who teaches what to which class)
- **Schedule Copies** (Different versions of timetables)
- **Actual Schedules** (When and where classes happen)

### Key Concept
The system separates **planning** (what needs to be scheduled) from **actual scheduling** (when it happens). This allows flexibility in creating and managing different timetable versions.

---

## Core Data Structures

### 1. School Context
Every operation happens within a school context stored in `schoolData` Pinia store:
- `school_id` - Which school
- `academic_year_id` - Which academic year
- `semester_id` - Which semester
- `schedule_copy_id` - Which timetable version is active

### 2. Time Structure
- **Days**: 1-7 (Sunday to Saturday) - typically using 1-5 for weekdays
- **Periods**: 1-12 (configurable per school)
- No fixed time slots - just numbered periods

---

## The Three Main Tables

### 📘 1. `classroom_subject_teachers` (CST)

**Purpose**: Defines WHAT needs to be scheduled.

**Schema**:
```php
{
  id: integer,
  school_id: integer,
  academic_year_id: integer,
  classroom_id: integer,      // Which class
  subject_id: integer,        // Which subject
  teacher_id: integer,        // Which teacher
  classes_per_week: integer,  // How many periods per week (KEY FIELD!)
  color_custom: string,       // Background color for display
  color_custom_text: string,  // Text color for display
  deleted_at: timestamp,      // Soft delete support
  created_at, updated_at
}
```

**Key Points**:
- ✅ One record = One subject-teacher assignment to a classroom
- ✅ `classes_per_week` determines how many schedule records should exist
- ✅ Example: Math assigned to Grade 1A with Mr. Ahmed, 5 classes/week
- ✅ Soft-deletable (can be restored)

**Example**:
```
Classroom: Grade 1A
Subject: Math
Teacher: Mr. Ahmed
classes_per_week: 5
→ This means we need 5 schedule entries for Math in Grade 1A's timetable
```

---

### 📗 2. `schedule_copies`

**Purpose**: Different versions/snapshots of timetables.

**Schema**:
```php
{
  id: integer,
  school_id: integer,
  academic_year_id: integer,
  semester_id: integer,
  name: string,               // e.g., "Fall 2026 - Draft 1"
  description: string,
  status: enum,               // draft, pending, active, archived
  week_number: integer,       // Optional: for multi-week schedules
  copy_date: date,
  active: boolean,            // Only one active per school
  created_by, last_modified_by,
  deleted_at, created_at, updated_at
}
```

**Key Points**:
- ✅ Think of it as "versions" of your timetable
- ✅ Only ONE can be `active` at a time
- ✅ You can have multiple drafts, archives, etc.
- ✅ Each copy has its own set of schedule records

**Common Statuses**:
- `draft` - Work in progress
- `pending` - Ready for review
- `active` - Currently being used
- `archived` - Old/historical version

---

### 📙 3. `schedules`

**Purpose**: The actual timetable entries - WHEN and WHERE classes happen.

**Schema**:
```php
{
  id: integer,
  copy_id: integer,           // Which schedule copy this belongs to
  cst_id: integer,            // Which CST assignment
  school_id: integer,
  period_order: integer,      // 1st, 2nd, 3rd... occurrence of this subject
  day_number: integer,        // 1-7 (null if not yet assigned)
  period_number: integer,     // 1-12 (null if not yet assigned)
  active: boolean,
  created_at, updated_at
}
```

**Key Points**:
- ✅ Each CST should have `classes_per_week` schedule records
- ✅ `period_order` tracks 1st class, 2nd class, 3rd class, etc.
- ✅ `day_number` and `period_number` can be NULL (unassigned)
- ✅ When both are set = scheduled, when NULL = needs to be placed

**Example**:
```
CST: Math - Grade 1A - Mr. Ahmed (5 classes/week)
→ Creates 5 schedule records:
  1. period_order=1, day=1, period=2  (Sunday, 2nd period)
  2. period_order=2, day=2, period=3  (Monday, 3rd period)
  3. period_order=3, day=3, period=1  (Tuesday, 1st period)
  4. period_order=4, day=null, period=null  (Not yet assigned)
  5. period_order=5, day=null, period=null  (Not yet assigned)
```

---

## How Everything Works Together

### The Flow

```
Step 1: Define Assignments (CST)
┌─────────────────────────────────┐
│ Create CST records:             │
│ - Assign subjects to classrooms │
│ - Set classes_per_week          │
│ - Assign teachers               │
└──────────┬──────────────────────┘
           │
           ▼
Step 2: Create Schedule Copy
┌─────────────────────────────────┐
│ Create a new schedule copy      │
│ - Give it a name                │
│ - Set status (draft/active)     │
└──────────┬──────────────────────┘
           │
           ▼
Step 3: Generate Schedule Records
┌─────────────────────────────────┐
│ Auto-generate from CSTs:        │
│ - For each CST...               │
│ - Create N schedule records     │
│   (N = classes_per_week)        │
│ - With period_order 1,2,3...    │
│ - day/period initially NULL     │
└──────────┬──────────────────────┘
           │
           ▼
Step 4: Assign to Timetable Grid
┌─────────────────────────────────┐
│ Use Timetable Editor:           │
│ - Drag/drop or click to assign  │
│ - Set day_number & period_number│
│ - System checks for conflicts   │
└──────────┬──────────────────────┘
           │
           ▼
Step 5: Verify & Sync
┌─────────────────────────────────┐
│ Use Schedule Sync feature:      │
│ - Check if counts match         │
│ - Fix missing/extra records     │
│ - Ensure data integrity         │
└─────────────────────────────────┘
```

---

## Recommended Workflow

### For New Timetable Creation

**Step 1: Review CST Assignments** 📋
```
Location: http://127.0.0.1:8000/weekly-system/schedule-copies
Action: Click "CST Overview" button
Purpose: 
- Verify all classrooms have subject assignments
- Check classes_per_week values are correct
- Total expected schedules = sum of all classes_per_week
```

**Step 2: Edit CST if Needed** ✏️
```
In CST Overview:
- Toggle "Edit Mode"
- Modify classes_per_week values
- Delete unwanted assignments (soft delete)
- Restore accidentally deleted ones
Important: After editing, you'll need to sync!
```

**Step 3: Create Schedule Copy** 📝
```
Location: Schedule Copies page
Action: Click "Create" button
Fill in:
- Name (e.g., "Spring 2026 Semester 1")
- Description
- ✅ Check "Auto-generate schedule entries"
- Status: "draft"
This creates all schedule records automatically!
```

**Step 4: Open Timetable Editor** 🗓️
```
Location: http://127.0.0.1:8000/weekly-system/timetable-editor
Select:
- Schedule Copy (the one you just created)
- Classroom
You'll see:
- Empty grid (days × periods)
- Unassigned subjects on the side
```

**Step 5: Assign Periods** 🎯
```
Methods:
A. Manual Assignment:
   - Click empty cell → Select CST → Assign
   - System checks for teacher conflicts
   
B. Random Fill:
   - Click "Random Fill" button
   - Review preview
   - Apply selections
   - Fix conflicts manually
```

**Step 6: Check for Issues** ⚠️
```
Look at "Current Classroom" statistics:
- Conflict count (should be 0)
- Subject Breakdown table:
  - Expected vs Actual columns
  - ✅ = correct
  - ⬇️ = missing periods
  - ⬆️ = extra periods
```

**Step 7: Use Schedule Sync** 🔄
```
Go back to: Schedule Copies page
On your schedule copy: Click "Sync" button
Review:
- Missing records (create them)
- Extra records (delete them)
- Apply fixes
```

**Step 8: Activate** ✅
```
When satisfied:
- Set status to "active"
- Only one schedule copy can be active at a time
- This becomes the "live" timetable
```

---

## Key Features

### 1. CST Overview & Management
**Location**: Schedule Copies page → "CST Overview" button

**View Mode**:
- Summary cards (Classrooms, Assignments, Expected Schedules)
- Breakdown by classroom
- Subject-Teacher list with classes/week

**Edit Mode**:
- Inline edit of `classes_per_week`
- Delete assignments (soft delete)
- Save changes per row

**Show Deleted**:
- View soft-deleted CSTs
- Restore deleted assignments
- Prevents conflicts

**Warning**: After editing CSTs, always run Schedule Sync!

---

### 2. Schedule Sync Manager
**Location**: Schedule Copies page → "Sync" button on each copy

**Purpose**: Ensures schedule records match CST expectations

**How it works**:
```
For each CST:
  Expected = classes_per_week
  Actual = count of schedule records
  
  If Actual < Expected:
    Status: Missing
    Action: Can create missing records
    
  If Actual > Expected:
    Status: Extra
    Action: Can delete extra records
    
  If Actual == Expected:
    Status: OK ✅
```

**Features**:
- Visual status cards (OK / Missing / Extra)
- Classroom-by-classroom breakdown
- Select which fixes to apply
- "Select All Missing" / "Select All Extra" buttons
- Deletes unassigned records first (safer)

---

### 3. Random Fill
**Location**: Timetable Editor → "Random Fill" button

**Purpose**: Automatically assign unassigned periods

**Process**:
1. Identifies empty slots in timetable
2. Finds available CSTs for that classroom
3. Checks for teacher conflicts
4. Generates preview with:
   - ✅ Clean assignments (no conflicts)
   - ⚠️ Conflicting assignments (teacher busy)
   - ❌ Unfillable (no CST available)
5. User selects what to apply
6. Option to "Force fill even with conflicts"

**Best Practice**: Use for initial population, then manually fix conflicts

---

### 4. Subject Breakdown Validation
**Location**: Timetable Editor → Current Classroom statistics

**Shows**:
| Subject | Teacher | Expected | Actual | Status |
|---------|---------|----------|--------|--------|
| Math    | Ahmed   | 5        | 5      | ✅     |
| Arabic  | Sara    | 6        | 4      | ⬇️ -2  |
| Science | Kamal   | 3        | 5      | ⬆️ +2  |

**Status Icons**:
- ✅ Perfect match
- ⬇️ Missing periods (hover for count)
- ⬆️ Extra periods (hover for count)

---

## Common Operations

### ✏️ Editing an Existing Timetable

**Option A: Modify CST then Sync**
1. Open CST Overview
2. Enable Edit Mode
3. Change `classes_per_week` (e.g., Math from 5 to 6)
4. Save
5. Go to affected Schedule Copy
6. Click "Sync" → Create missing record
7. Open Timetable Editor
8. Assign the new unassigned period

**Option B: Direct in Timetable Editor**
1. Open Timetable Editor
2. Click assigned cell → "Edit" or "Clear"
3. Reassign to different slot
4. System checks conflicts

---

### 🔄 Creating Schedule Copy from Existing

**Method 1: Duplicate in Backend**
Currently not implemented - use Method 2

**Method 2: Create New + Import**
1. Create new Schedule Copy
2. Use AI Import feature (if available)
3. Or use Random Fill + manual adjustments

---

### 🗑️ Deleting a Subject Assignment

**Soft Delete Flow**:
1. CST Overview → Edit Mode
2. Click Delete on CST
3. Confirms not last subject in classroom
4. Soft deletes CST
5. Schedule records still exist!
6. Use Schedule Sync → Delete extra records
7. Or leave them for reference

**To Undo**:
1. CST Overview → Show Deleted
2. Click Restore
3. CST is back
4. Schedule Sync → Create any missing records

---

### 📊 Generating Reports

**Weekly Plan Views**:
- Teacher view: Shows teacher's schedule
- Classroom view: Shows classroom's schedule
- Can print or download PDF

---

## Data Integrity Rules

### ✅ Always Valid States

**Rule 1: CST → Schedule Relationship**
```
For each CST:
  schedule_count == classes_per_week
  
If not equal: Use Schedule Sync to fix
```

**Rule 2: No Double Booking**
```
For each (day, period) in a classroom:
  Maximum 1 schedule record
```

**Rule 3: Teacher Conflicts**
```
For each (day, period, teacher):
  Teacher can only be in 1 classroom
  
Warning shown in Timetable Editor
"Busy Teachers" indicator
```

**Rule 4: Schedule Copy Isolation**
```
Each schedule copy is independent
Changes to Copy A don't affect Copy B
```

**Rule 5: Soft Delete Safety**
```
Deleted CSTs can be restored
Schedule records preserve history
Use Schedule Sync to clean up
```

---

### ⚠️ Warning States

**Yellow Warnings** (Fixable):
- Missing schedule records → Create them
- Extra schedule records → Delete them
- Teacher conflicts → Reassign

**Red Errors** (Must fix):
- Last subject in classroom → Can't delete
- Invalid classes_per_week (< 1 or > 20) → Edit CST
- Missing CST reference → Data corruption, contact admin

---

## Troubleshooting

### Problem: "Expected 5 periods, only 3 assigned"

**Cause**: CST has `classes_per_week = 5` but only 3 schedule records exist

**Solution**:
```
1. Go to Schedule Copies page
2. Click "Sync" on the affected copy
3. It will show "Missing: 2 records"
4. Select to create missing
5. Click "Apply"
6. Go to Timetable Editor
7. Assign the 2 new unassigned periods
```

---

### Problem: "Too many periods in timetable"

**Cause**: More schedule records than `classes_per_week`

**Solution**:
```
Option A: Reduce schedule records
1. Schedule Copies → Sync
2. Select extra records to delete
3. Apply (unassigned ones deleted first)

Option B: Increase classes_per_week
1. CST Overview → Edit Mode
2. Increase the number
3. Save
```

---

### Problem: "Teacher conflict - Mr. Ahmed busy"

**Cause**: Teacher assigned to 2 classrooms at same time

**Solution**:
```
1. In Timetable Editor, note the conflict
2. Click the conflicting period
3. Clear or reassign to different slot
4. System suggests available slots
```

---

### Problem: "Can't delete subject - last one in classroom"

**Cause**: Trying to delete the only subject for a classroom

**Reason**: Classrooms must have at least 1 subject

**Solution**:
```
1. Add another subject to the classroom first
2. Then delete the unwanted one
```

---

### Problem: "Schedule records missing after CST edit"

**Cause**: Edited CST but didn't sync

**Solution**:
```
Always after CST changes:
1. Schedule Copies → Sync
2. Review and apply fixes
3. Then check Timetable Editor
```

---

## Best Practices

### 🎯 Start of School Year

1. ✅ Set up CST assignments first
2. ✅ Verify in CST Overview (all correct?)
3. ✅ Create Schedule Copy with auto-generate
4. ✅ Use Random Fill for initial placement
5. ✅ Manually fix conflicts
6. ✅ Run Schedule Sync to verify
7. ✅ Set to "active"

### 🔄 Mid-Year Changes

1. ✅ Edit CST (add/remove subjects)
2. ✅ Run Schedule Sync immediately
3. ✅ Update Timetable Editor assignments
4. ✅ Verify no conflicts
5. ✅ Consider creating new Schedule Copy for major changes

### 💾 Before Deleting

1. ✅ Check if subject is actually needed
2. ✅ Note it's soft delete (restorable)
3. ✅ Run Schedule Sync after
4. ✅ Clean up schedule records

### ✨ Performance

1. ✅ Use School Context (sets  school_id automatically)
2. ✅ One active schedule copy at a time
3. ✅ Archive old copies instead of deleting
4. ✅ Regularly sync to prevent drift

---

## Quick Reference Cheat Sheet

### 📍 Page URLs

```
Schedule Copies:     /weekly-system/schedule-copies
Timetable Editor:    /weekly-system/timetable-editor
School Browser:      /weekly-system/school-browser
```

### 🎨 Color Coding

- **Blue**: Subjects, CST badges
- **Green**: Expected/correct values, success
- **Orange/Yellow**: Warning, modifications, missing
- **Red**: Errors, conflicts, deleted
- **Gray**: Inactive, disabled

### 🔑 Critical Fields

```
CST:
- classes_per_week: How many schedule records needed

Schedule:
- period_order: 1st, 2nd, 3rd... occurrence
- day_number + period_number: When it happens (null = unassigned)

Schedule Copy:
- status: draft, pending, active, archived
- active: boolean (only one true per school)
```

---

## Summary

The Weekly Timetable System is built on three pillars:

1. **CST** = What needs to be taught (Planning)
2. **Schedule Copy** = Version management (Organization)
3. **Schedule** = When it happens (Execution)

Always maintain the relationship:
```
CST.classes_per_week == COUNT(Schedule records for that CST)
```

Use the tools:
- **CST Overview** → Manage assignments
- **Schedule Sync** → Verify integrity
- **Random Fill** → Quick setup
- **Timetable Editor** → Manual control

Follow the workflow → Create CSTs → Create Copy → Generate → Assign → Sync → Activate

---

**Document Version**: 1.0  
**Last Updated**: January 12, 2026  
**Author**: Based on system implementation and features

For support or questions, refer to this document or consult the development team.
