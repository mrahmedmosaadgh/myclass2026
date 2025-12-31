# Implementation Plan: Teacher Excel Import

## Overview

This implementation plan creates a comprehensive teacher management system with Excel import functionality. The system will leverage existing ImportExcel.vue component and follow Laravel best practices for service-oriented architecture. Tasks are organized to build incrementally, ensuring each step validates core functionality early through code.

## Tasks

- [x] 1. Set up core infrastructure and models
  - Create TeacherImportService class with basic structure
  - Add enhanced methods to existing Teacher model for import functionality
  - Create migration for any missing indexes on classroom_subject_teachers table
  - Set up route definitions for teacher import endpoints
  - _Requirements: 3.1, 3.2, 3.3, 8.1, 8.3_

- [ ]* 1.1 Write property test for teacher creation with user account
  - **Property 4: Teacher creation with user account**
  - **Validates: Requirements 3.1, 3.2, 3.3, 3.5, 3.7**

- [x] 2. Implement TeacherImportController with school/academic year validation
  - Create TeacherImportController with index, getSchools, and getActiveAcademicYear methods
  - Implement school selection and academic year validation logic
  - Add academic year requirement validation (must have active year)
  - _Requirements: 1.1, 1.2, 1.3_

- [ ]* 2.1 Write property test for school context persistence
  - **Property 1: School context persistence**
  - **Validates: Requirements 1.2, 1.5, 5.4**

- [x] 3. Create Excel file validation and processing logic
  - Implement validateImport method in TeacherImportController
  - Add file format validation (.xlsx, .xls only)
  - Implement required column validation (Classroom, Subject, Teacher Name, Periods_per_Week)
  - Add optional column support (Teacher Email, Phone, National ID, Gender, Date of Birth)
  - Set file size limit to 10MB
  - _Requirements: 2.1, 2.2, 2.3, 2.4, 7.5_

- [ ]* 3.1 Write property test for file format and structure validation
  - **Property 2: File format and structure validation**
  - **Validates: Requirements 2.1, 2.2, 2.4**

- [ ]* 3.2 Write property test for optional column handling
  - **Property 3: Optional column handling**
  - **Validates: Requirements 2.3**

- [x] 4. Implement core TeacherImportService methods
  - Create createOrUpdateTeacher method with unique t_id generation
  - Implement createOrUpdateClassroom and createOrUpdateSubject methods
  - Add teacher-user linking logic with default password and email handling
  - Implement school association for all created entities
  - _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5, 3.6, 3.7, 4.1, 4.2, 4.3, 4.4_

- [ ]* 4.1 Write property test for teacher email defaulting
  - **Property 5: Teacher email defaulting**
  - **Validates: Requirements 3.4**

- [ ]* 4.2 Write property test for existing teacher linking
  - **Property 6: Existing teacher linking**
  - **Validates: Requirements 3.6**

- [ ]* 4.3 Write property test for classroom and subject creation
  - **Property 7: Classroom and subject creation**
  - **Validates: Requirements 4.1, 4.2, 4.3**

- [x] 5. Checkpoint - Ensure basic entity creation works
  - Ensure all tests pass, ask the user if questions arise.

- [x] 6. Implement assignment management and sync modes
  - Create createOrUpdateAssignment method in TeacherImportService
  - Implement "Update Existing" sync mode logic
  - Implement "Full Sync" mode with assignment replacement
  - Add periods_per_week validation (positive numbers only)
  - _Requirements: 5.2, 5.3, 5.4, 5.5, 5.6_

- [ ]* 6.1 Write property test for entity reuse
  - **Property 8: Entity reuse**
  - **Validates: Requirements 4.4**

- [ ]* 6.2 Write property test for name validation
  - **Property 9: Name validation**
  - **Validates: Requirements 4.5**

- [ ]* 6.3 Write property test for update existing sync mode
  - **Property 10: Update existing sync mode**
  - **Validates: Requirements 5.2**

- [ ]* 6.4 Write property test for full sync mode
  - **Property 11: Full sync mode**
  - **Validates: Requirements 5.3**

- [x] 7. Implement import processing and error handling
  - Create processImport method with transaction management
  - Implement chunk processing for large files (>1000 rows)
  - Add comprehensive error collection and reporting
  - Implement partial failure processing (continue on row errors)
  - _Requirements: 6.5, 7.1, 7.3, 7.4_

- [ ]* 7.1 Write property test for assignment updates
  - **Property 12: Assignment updates**
  - **Validates: Requirements 5.5**

- [ ]* 7.2 Write property test for periods validation
  - **Property 13: Periods validation**
  - **Validates: Requirements 5.6**

- [ ]* 7.3 Write property test for partial failure processing
  - **Property 14: Partial failure processing**
  - **Validates: Requirements 6.5**

- [x] 8. Create Vue.js frontend component
  - Create TeacherImport.vue component with school/academic year selection
  - Integrate existing ImportExcel.vue component
  - Add sync mode selection UI (Update Existing vs Full Sync)
  - Implement import results display with error reporting
  - Add progress indicators and user feedback
  - _Requirements: 1.4, 1.5, 5.1_

- [ ]* 8.1 Write unit tests for Vue component interactions
  - Test school selection and academic year display
  - Test sync mode selection functionality
  - Test import results display

- [x] 9. Implement teacher status management
  - Enhance Teacher model boot method for user synchronization
  - Add soft delete handling with user deactivation
  - Implement active status synchronization between teacher and user
  - Add validation to prevent inactive teacher assignments
  - _Requirements: 8.1, 8.2, 8.3, 8.4, 8.5_

- [ ]* 9.1 Write property test for large file chunk processing
  - **Property 15: Large file chunk processing**
  - **Validates: Requirements 7.1**

- [ ]* 9.2 Write property test for transaction consistency
  - **Property 16: Transaction consistency**
  - **Validates: Requirements 7.3, 7.4**

- [ ]* 9.3 Write property test for file size limits
  - **Property 17: File size limits**
  - **Validates: Requirements 7.5**

- [x] 10. Implement advanced teacher management features
  - Add teacher-user status synchronization methods
  - Implement historical data preservation logic
  - Add referential integrity validation
  - Create inactive teacher assignment prevention
  - _Requirements: 8.1, 8.2, 8.3, 8.4, 8.5_

- [ ]* 10.1 Write property test for teacher-user status synchronization
  - **Property 18: Teacher-user status synchronization**
  - **Validates: Requirements 8.1, 8.3**

- [ ]* 10.2 Write property test for historical data preservation
  - **Property 19: Historical data preservation**
  - **Validates: Requirements 8.2**

- [ ]* 10.3 Write property test for inactive teacher assignment prevention
  - **Property 20: Inactive teacher assignment prevention**
  - **Validates: Requirements 8.4**

- [ ] 11. Final integration and testing
  - Wire all components together in routes and controllers
  - Add comprehensive error handling and user feedback
  - Implement import report generation and download functionality
  - Add logging and monitoring for import operations
  - _Requirements: 6.1, 6.2, 6.3, 6.4_

- [ ]* 11.1 Write property test for referential integrity maintenance
  - **Property 21: Referential integrity maintenance**
  - **Validates: Requirements 8.5**

- [ ]* 11.2 Write integration tests for complete import workflow
  - Test end-to-end import process with various Excel files
  - Test error scenarios and recovery
  - Test multi-school import scenarios

- [ ] 12. Final checkpoint - Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.

## Notes

- Tasks marked with `*` are optional and can be skipped for faster MVP
- Each task references specific requirements for traceability
- Property tests validate universal correctness properties using PHPUnit with Eris
- Unit tests validate specific examples and edge cases
- The system builds incrementally with validation at each step
- All database operations use transactions for consistency
- The existing ImportExcel.vue component is reused for file handling