# Requirements Document

## Introduction

A multi-school, year-specific teacher management system that allows administrators to bulk import teacher assignments from Excel files. The system automatically creates teachers in both the `teachers` and `users` tables, manages classroom-subject assignments, and supports school selection with academic year scoping.

## Glossary

- **System**: The teacher Excel import management system
- **Admin**: School administrator with import permissions
- **Teacher**: Educational staff member with classroom assignments
- **Academic_Year**: A specific school year period with start/end dates
- **Assignment**: A teacher-classroom-subject relationship for a specific academic year
- **Excel_Import**: The process of uploading and processing Excel files containing teacher data
- **Sync_Mode**: The import behavior (update existing vs full replacement)
- **School_Context**: The selected school scope for import operations

## Requirements

### Requirement 1

**User Story:** As an admin, I want to select a school and academic year before importing, so that teacher assignments are properly scoped and organized.

#### Acceptance Criteria

1. WHEN an admin accesses the import page, THE System SHALL display a school selection dropdown
2. WHEN a school is selected, THE System SHALL display the active academic year for that school
3. IF no active academic year exists for the selected school, THEN THE System SHALL prompt the admin to create one first
4. WHEN both school and academic year are selected, THE System SHALL enable the Excel upload functionality
5. THE System SHALL persist the selected school and academic year context throughout the import process

### Requirement 2

**User Story:** As an admin, I want to upload Excel files with teacher assignment data, so that I can bulk import multiple teacher assignments efficiently.

#### Acceptance Criteria

1. WHEN uploading an Excel file, THE System SHALL accept .xlsx and .xls formats
2. THE System SHALL require these columns: Classroom, Subject, Teacher Name, Periods_per_Week
3. THE System SHALL support optional columns: Teacher Email, Phone, National ID, Gender, Date of Birth
4. WHEN processing the Excel file, THE System SHALL validate that required columns are present
5. THE System SHALL display a preview of the imported data before processing
6. THE System SHALL show validation errors for any invalid rows in the preview

### Requirement 3

**User Story:** As an admin, I want the system to automatically create teachers and users, so that new teachers are immediately available in the system.

#### Acceptance Criteria

1. WHEN processing a teacher name not in the system, THE System SHALL create a new teacher record
2. WHEN creating a teacher, THE System SHALL generate a unique t_id automatically
3. WHEN creating a teacher, THE System SHALL create a corresponding user record with role 'teacher'
4. THE System SHALL set the user email to the teacher's t_id if no email is provided
5. THE System SHALL set a default password of '12345678' for new user accounts
6. WHEN a teacher already exists, THE System SHALL link to the existing teacher record
7. THE System SHALL associate the teacher with the selected school

### Requirement 4

**User Story:** As an admin, I want to create classroom and subject records automatically, so that I don't need to pre-create all organizational structures.

#### Acceptance Criteria

1. WHEN processing a classroom name not in the system, THE System SHALL create a new classroom record
2. WHEN processing a subject name not in the system, THE System SHALL create a new subject record
3. THE System SHALL associate new classrooms and subjects with the selected school
4. WHEN a classroom or subject already exists for the school, THE System SHALL use the existing record
5. THE System SHALL validate that classroom and subject names are not empty

### Requirement 5

**User Story:** As an admin, I want to manage teacher assignments with different sync modes, so that I can choose between updating existing assignments or replacing all assignments.

#### Acceptance Criteria

1. THE System SHALL provide a sync mode selection with options: "Update Existing" and "Full Sync"
2. WHEN "Update Existing" is selected, THE System SHALL update existing assignments and add new ones
3. WHEN "Full Sync" is selected, THE System SHALL replace all assignments for the school and academic year
4. THE System SHALL create classroom_subject_teacher records with the selected school and academic year
5. WHEN an assignment already exists, THE System SHALL update the periods_per_week value
6. THE System SHALL validate that periods_per_week is a positive number

### Requirement 6

**User Story:** As an admin, I want to see detailed import results, so that I can understand what was processed and identify any issues.

#### Acceptance Criteria

1. WHEN import processing completes, THE System SHALL display a summary report
2. THE System SHALL show counts of: rows processed, teachers created, assignments created, assignments updated
3. THE System SHALL list any validation errors with row numbers and descriptions
4. THE System SHALL provide an option to download the error report as a file
5. WHEN errors occur, THE System SHALL continue processing valid rows and report all errors at the end

### Requirement 7

**User Story:** As an admin, I want to handle large Excel files efficiently, so that the system remains responsive during bulk imports.

#### Acceptance Criteria

1. THE System SHALL process Excel files using chunk reading for files larger than 1000 rows
2. THE System SHALL display a progress indicator during file processing
3. THE System SHALL use database transactions to ensure data consistency
4. WHEN processing fails, THE System SHALL rollback all changes for that import session
5. THE System SHALL set a maximum file size limit of 10MB for Excel uploads

### Requirement 8

**User Story:** As an admin, I want to manage teacher status and maintain historical records, so that I can track teacher assignments over time.

#### Acceptance Criteria

1. WHEN a teacher is soft-deleted, THE System SHALL set the corresponding user account to inactive
2. THE System SHALL preserve all historical classroom_subject_teacher assignments
3. WHEN updating teacher active status, THE System SHALL sync the user active status
4. THE System SHALL prevent assignment of inactive teachers to new classrooms
5. THE System SHALL maintain referential integrity between teachers, users, and assignments