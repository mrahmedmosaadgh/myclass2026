# 2026-01-05 21:18 | Context Management Implementation

## Overview
Implemented context management system for the schools table, integrating active context fields directly into the School model instead of using a separate active_contexts table. This consolidates school, academic year, semester, and schedule copy contexts into the schools table as per the existing database schema.

## Key Changes
- Updated School model to align with the actual database schema
- Created UserContextService to handle context resolution logic
- Created ContextController with API endpoints for context management
- Added API routes for context management functionality
- Removed unnecessary week_number and locked fields to match actual database structure

## Technical Details
The implementation follows the principle of consolidating context fields into the core entity table (schools) rather than creating a separate context management table. This approach reduces table association complexity and improves query efficiency for context-related operations.

The UserContextService handles resolution of:
- School context (based on user's assigned school)
- Academic year context (preferring active academic year)
- Semester context (preferring active semester)
- Schedule copy context (preferring active schedule copy)

The ContextController provides API endpoints for:
- Getting current user context
- Updating context for a specific school
- Setting a school as active context
- Updating contexts for all schools

All implementation aligns with the existing database schema that includes academic_year_id, semester_id, schedule_copy_id, resolved_by, and resolved_at fields in the schools table.