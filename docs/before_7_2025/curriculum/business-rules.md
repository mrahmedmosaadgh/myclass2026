# Curriculum Management Business Rules

## Overview

This document outlines the business rules and logic that govern the curriculum management system. These rules ensure data integrity, maintain educational standards, and provide a consistent user experience.

## Core Business Rules

### 1. Curriculum Activation Rules

#### Rule: One Active Curriculum Per Context
**Description:** Only one curriculum can be active for any given combination of school, subject, and grade.

**Implementation:**
- When activating a curriculum, the system automatically deactivates all other curricula for the same school+subject+grade combination
- This ensures no conflicts in curriculum selection for teachers and students

**Code Example:**
```php
public function activate()
{
    // Deactivate conflicting curricula
    static::where('school_id', $this->school_id)
          ->where('subject_id', $this->subject_id)
          ->where('grade_id', $this->grade_id)
          ->where('id', '!=', $this->id)
          ->update(['active' => 0]);

    // Activate this curriculum
    $this->update(['active' => 1]);
}
```

**Business Justification:**
- Prevents confusion about which curriculum to follow
- Ensures consistency in educational delivery
- Maintains clear accountability for curriculum implementation

#### Rule: Explicit Activation Required
**Description:** Curricula are created as inactive by default and require explicit activation.

**Implementation:**
- Default `active` value is 0 (inactive)
- Users must consciously choose to activate a curriculum
- Activation triggers the deactivation logic for conflicting curricula

**Business Justification:**
- Prevents accidental activation of incomplete curricula
- Allows for curriculum preparation before implementation
- Provides clear audit trail of activation decisions

### 2. Data Integrity Rules

#### Rule: Referential Integrity
**Description:** All foreign key relationships must be maintained and valid.

**Implementation:**
- Cascade deletes ensure related data is cleaned up
- Foreign key constraints prevent orphaned records
- Soft deletes preserve historical relationships

**Constraints:**
- `school_id` must exist in schools table
- `subject_id` must exist in subjects table
- `grade_id` must exist in grades table
- `curriculum_id` must exist in curricula table (for related entities)

#### Rule: Unique Curriculum Names
**Description:** Curriculum names must be unique within the same school, subject, and grade context.

**Implementation:**
```sql
UNIQUE KEY `curricula_school_id_grade_id_subject_id_name_unique` 
(`school_id`,`grade_id`,`subject_id`,`name`)
```

**Business Justification:**
- Prevents duplicate curricula that could cause confusion
- Ensures clear identification of curricula
- Maintains data quality standards

### 3. Access Control Rules

#### Rule: School-Based Access Control
**Description:** Users can only access curricula for schools they are assigned to.

**Implementation:**
- API endpoints filter results by user's assigned schools
- Superadmin users have access to all schools
- Regular users see only their assigned schools

**Code Example:**
```php
$schools = School::query()
    ->when($user->hasRole('superadmin'), function ($query) {
        return $query; // Superadmin sees all schools
    })
    ->when(!$user->hasRole('superadmin'), function ($query) use ($user) {
        return $query->whereHas('users', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        });
    })
    ->get();
```

#### Rule: Role-Based Permissions
**Description:** Only users with admin role can manage curricula.

**Implementation:**
- Admin routes protected by `role:admin` middleware
- API endpoints require authentication and admin role
- Frontend components check user permissions

### 4. Lesson Management Rules

#### Rule: Unique Lesson Identification
**Description:** Lessons within a curriculum must have unique topic+lesson number combinations.

**Implementation:**
```sql
UNIQUE KEY `curriculum_lessons_unique` 
(`curriculum_id`,`topic_number`,`lesson_number`)
```

**Business Justification:**
- Ensures clear lesson organization and sequencing
- Prevents duplicate lesson entries
- Maintains curriculum structure integrity

#### Rule: Lesson Selection Control
**Description:** Lessons can be individually selected or deselected within a curriculum.

**Implementation:**
- `selected` field controls lesson inclusion (0=not selected, 1=selected)
- Teachers can customize curriculum by selecting relevant lessons
- Selection status affects lesson plan generation and reporting

### 5. Lesson Plan Rules

#### Rule: Flexible Curriculum Association
**Description:** Lesson plans can be linked to curriculum lessons or exist independently.

**Implementation:**
- `curriculum_lesson_id` is nullable in lesson plans table
- Plans can be created from curriculum templates or from scratch
- Teachers have flexibility in lesson planning approach

#### Rule: Teacher Ownership
**Description:** Each lesson plan has a primary teacher and optional co-teachers.

**Implementation:**
- `teacher_id` is required for all lesson plans
- `co_teacher_ids` JSON field stores additional teachers
- Ownership determines access and editing permissions

### 6. Status Management Rules

#### Rule: Progressive Status Workflow
**Description:** Entities follow a defined status progression: draft → active → completed/archived.

**Status Values:**
- **0 (Draft)**: Initial state, work in progress
- **1 (Active)**: Currently in use
- **2 (Completed/Archived)**: Finished or archived

**Implementation:**
- Status transitions are controlled through specific methods
- Business logic prevents invalid status changes
- Audit trails track status changes

### 7. Data Validation Rules

#### Rule: Required Field Validation
**Description:** Critical fields must be provided and valid.

**Required Fields:**
- **Curricula**: name, school_id, subject_id, grade_id
- **Lessons**: curriculum_id, topic_number, topic_title, lesson_number, lesson_title
- **Lesson Plans**: school_id, subject_id, grade_id, classroom_id, teacher_id, title
- **Question Banks**: school_id, title, body, type, created_by_id

#### Rule: Data Type Validation
**Description:** Fields must conform to expected data types and formats.

**Validation Rules:**
- String fields have maximum length limits
- Integer fields must be positive numbers
- JSON fields must contain valid JSON
- Enum fields must use predefined values
- Date fields must be valid dates

### 8. Academic Year Rules

#### Rule: Curriculum Mapping Uniqueness
**Description:** Only one curriculum map per teacher per academic year per subject per grade.

**Implementation:**
```sql
UNIQUE KEY `curriculum_maps_unique` 
(`school_id`,`academic_year_id`,`subject_id`,`grade_id`,`teacher_id`)
```

**Business Justification:**
- Prevents conflicting teaching plans
- Ensures clear responsibility assignment
- Maintains academic year organization

### 9. Question Bank Rules

#### Rule: Flexible Question Association
**Description:** Questions can be linked to curricula, specific lessons, or exist independently.

**Implementation:**
- `curriculum_id` and `curriculum_lessons_id` are nullable
- Questions can be reused across different contexts
- Tagging system provides additional organization

#### Rule: Question Type Validation
**Description:** Questions must conform to their declared type structure.

**Type Requirements:**
- **MCQ**: Must have options JSON with answer choices
- **True/False**: Binary choice questions
- **Fill Blank**: Text completion questions
- **Essay**: Open-ended questions
- **Short Answer**: Brief response questions

### 10. Audit and History Rules

#### Rule: Soft Delete Preservation
**Description:** Important data is soft-deleted to preserve historical records.

**Implementation:**
- `deleted_at` timestamp marks deletion
- Soft-deleted records excluded from normal queries
- Historical data remains available for reporting

#### Rule: Change Tracking
**Description:** All modifications are tracked through timestamps and user attribution.

**Implementation:**
- `created_at` and `updated_at` timestamps on all entities
- `created_by_id` tracks question bank creators
- Audit logs can be implemented for sensitive changes

## Business Process Workflows

### Curriculum Creation Workflow

1. **Selection Phase**
   - User selects school from assigned schools
   - System loads available subjects for selected school
   - System loads available grades for selected school

2. **Creation Phase**
   - User provides curriculum name and description
   - User selects activation status
   - System validates uniqueness and requirements

3. **Activation Phase** (if selected)
   - System checks for existing active curricula
   - System deactivates conflicting curricula
   - System activates new curriculum
   - System logs activation event

### Lesson Planning Workflow

1. **Context Selection**
   - Teacher selects school, subject, grade, classroom
   - System loads available curriculum lessons
   - Teacher can choose to base plan on curriculum or create independently

2. **Plan Development**
   - Teacher creates lesson plan content
   - System saves as draft status
   - Teacher can collaborate with co-teachers

3. **Plan Activation**
   - Teacher reviews and finalizes plan
   - System changes status to active
   - Plan becomes available for execution

4. **Plan Completion**
   - After lesson delivery, teacher marks as completed
   - System archives completed plans
   - Data becomes available for reporting

## Exception Handling

### Data Conflict Resolution
- **Activation Conflicts**: Automatic deactivation of conflicting curricula
- **Unique Constraint Violations**: Clear error messages with suggested alternatives
- **Foreign Key Violations**: Validation prevents orphaned records

### Error Recovery
- **Transaction Rollback**: Database transactions ensure data consistency
- **Graceful Degradation**: System continues operating with reduced functionality
- **User Notification**: Clear error messages guide user actions

## Performance Considerations

### Query Optimization
- Indexes on frequently filtered columns
- Composite indexes for multi-column filters
- Full-text search for content queries

### Data Archival
- Soft deletes preserve history without impacting performance
- Periodic cleanup of old draft records
- Archive completed lesson plans after academic year

## Compliance and Standards

### Educational Standards
- Support for various curriculum standards (national, state, local)
- Flexible metadata structure accommodates different requirements
- Audit trails support compliance reporting

### Data Privacy
- School-based data isolation
- Role-based access control
- Secure handling of student-related information

---

*Last Updated: [Current Date]*
*Business Rules Version: 1.0*
