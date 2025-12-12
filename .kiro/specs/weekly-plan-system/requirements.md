# Requirements Document

## Introduction

The Weekly Plan System is a modular feature that enables teachers to create and manage customizable weekly plans for their assigned subjects and classes. The system provides flexibility to edit, reorder, and customize sessions while maintaining stability even when the underlying fixed schedule changes. This system operates as an editable copy of the lesson schedule, allowing teachers to adapt their plans week by week without affecting the core scheduling system.

## Requirements

### Requirement 1

**User Story:** As a teacher, I want to create a weekly plan for my assigned subject and class, so that I can organize my lessons and activities for each week of the semester.

#### Acceptance Criteria

1. WHEN a teacher accesses the weekly plan system THEN the system SHALL display all their assigned subject-class combinations from classroom_subject_teachers
2. WHEN a teacher selects a subject-class combination THEN the system SHALL create or retrieve the weekly plan header for that combination
3. IF no weekly plan exists THEN the system SHALL create a new weekly_plan_header with the selected cst_id, academic_year_id, and semester
4. WHEN a weekly plan is created THEN the system SHALL generate initial sessions based on the classes_per_week value from classroom_subject_teachers

### Requirement 2

**User Story:** As a teacher, I want to edit individual sessions within my weekly plan, so that I can customize the content, type, and details for each session.

#### Acceptance Criteria

1. WHEN a teacher views a weekly plan THEN the system SHALL display all sessions organized by week number and session index
2. WHEN a teacher clicks on a session THEN the system SHALL allow editing of title, type, notes, and materials
3. WHEN a teacher changes session type THEN the system SHALL accept values: lesson, quiz, exam, activity, break
4. WHEN a teacher saves session changes THEN the system SHALL update the weekly_plan_sessions record with the new data
5. WHEN a teacher adds custom data THEN the system SHALL store it in the JSON data field supporting zoom_link, homework, and skill_tags

### Requirement 3

**User Story:** As a teacher, I want to reorder sessions within a week, so that I can adjust the sequence of my lessons and activities.

#### Acceptance Criteria

1. WHEN a teacher drags a session to a new position THEN the system SHALL update the session_index values accordingly
2. WHEN sessions are reordered THEN the system SHALL maintain the period_code references for schedule alignment
3. WHEN reordering is complete THEN the system SHALL save the new order immediately
4. IF reordering fails THEN the system SHALL revert to the previous order and display an error message

### Requirement 4

**User Story:** As a teacher, I want to add or remove sessions from my weekly plan, so that I can accommodate schedule changes or add extra activities.

#### Acceptance Criteria

1. WHEN a teacher adds a new session THEN the system SHALL create a new weekly_plan_sessions record with the next available session_index
2. WHEN a teacher removes a session THEN the system SHALL delete the record and adjust remaining session indices
3. WHEN adding sessions THEN the system SHALL allow selection of session type and assignment of period_code
4. WHEN sessions exceed classes_per_week THEN the system SHALL allow it but mark as extra sessions

### Requirement 5

**User Story:** As a teacher, I want my weekly plans to remain stable when the fixed schedule changes, so that my customizations are not lost.

#### Acceptance Criteria

1. WHEN the fixed schedule changes THEN the system SHALL maintain all existing weekly_plan_sessions records
2. WHEN period codes need updating THEN the system SHALL provide a utility to update period_code values without losing session data
3. IF a period_code becomes invalid THEN the system SHALL flag the session for manual review rather than deleting it
4. WHEN schedule changes occur THEN the system SHALL preserve all custom titles, notes, materials, and JSON data

### Requirement 6

**User Story:** As a teacher, I want to navigate between different weeks of my semester, so that I can plan and edit sessions across the entire academic period.

#### Acceptance Criteria

1. WHEN a teacher accesses their weekly plan THEN the system SHALL display weeks 1-18 by default for semester planning
2. WHEN a teacher navigates between weeks THEN the system SHALL load sessions for the selected week number
3. WHEN viewing a week THEN the system SHALL display session_index, period_code, title, type, and basic details
4. IF a week has no sessions THEN the system SHALL display an empty state with option to generate sessions

### Requirement 7

**User Story:** As a system administrator, I want the weekly plan system to integrate seamlessly with existing data structures, so that it works with current classroom assignments and academic years.

#### Acceptance Criteria

1. WHEN creating weekly plans THEN the system SHALL use existing classroom_subject_teachers relationships
2. WHEN referencing academic periods THEN the system SHALL link to academic_years table
3. WHEN storing schedule references THEN the system SHALL use period_code format compatible with existing schedule system
4. WHEN accessing the system THEN the system SHALL respect existing authentication and authorization mechanisms

### Requirement 8

**User Story:** As a developer, I want the weekly plan system to be modular and maintainable, so that it can be easily extended and doesn't interfere with existing functionality.

#### Acceptance Criteria

1. WHEN implementing the system THEN all code SHALL be organized under /weeklyplansystem directory structure
2. WHEN creating database tables THEN migrations SHALL follow Laravel conventions with proper foreign key relationships
3. WHEN building APIs THEN controllers SHALL follow RESTful patterns with proper validation
4. WHEN creating frontend components THEN Vue.js components SHALL be reusable and follow existing project patterns
5. WHEN adding new features THEN the system SHALL use JSON data fields for extensibility without schema changes