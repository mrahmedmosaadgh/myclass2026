# Implementation Plan: AI Schedule Import

## Overview

This implementation plan creates an AI-powered schedule import dialog for the existing Timetable Editor. The feature will be built using Laravel (PHP) for backend services and Vue.js for frontend components, following the existing weekly system architecture patterns.

## Tasks

- [ ] 1. Create backend parser and mapper services
  - Create ScheduleParserService class with text parsing methods
  - Create ScheduleMapperService class for CST mapping logic
  - Implement parsing strategies for table formats, natural language, and mixed inputs
  - Add validation methods for days, subjects, and schedule conflicts
  - _Requirements: 2.1, 2.2, 2.3, 3.1, 5.1, 7.1, 7.2_

- [ ]* 1.1 Write property test for table format parsing
  - **Property 2: Table format parsing universality**
  - **Validates: Requirements 2.1, 2.2, 2.3, 7.1, 7.2**

- [ ]* 1.2 Write property test for data validation consistency
  - **Property 3: Data validation consistency**
  - **Validates: Requirements 2.4, 2.5, 4.3**

- [ ]* 1.3 Write property test for natural language extraction
  - **Property 4: Natural language extraction accuracy**
  - **Validates: Requirements 3.1, 3.3**

- [ ] 2. Implement AI import API controller and routes
  - Create AIScheduleImportController with parse, preview, and execute endpoints
  - Add API routes under /weekly-system/api/ai-import namespace
  - Implement request validation for import operations
  - Add proper error handling and response formatting
  - _Requirements: 1.2, 4.1, 6.1, 8.3_

- [ ]* 2.1 Write property test for subject mapping consistency
  - **Property 6: Subject mapping consistency**
  - **Validates: Requirements 5.1, 5.2**

- [ ]* 2.2 Write property test for import operation atomicity
  - **Property 7: Import operation atomicity**
  - **Validates: Requirements 6.1, 6.2, 6.3, 6.5**

- [ ] 3. Create AIImportDialog Vue component
  - Build modal dialog component with text input area and format instructions
  - Add real-time parsing feedback and validation indicators
  - Implement classroom context display and format examples
  - Add proper event handling for dialog open/close states
  - _Requirements: 1.1, 1.2, 1.3, 1.4_

- [ ]* 3.1 Write property test for dialog interaction consistency
  - **Property 1: Dialog interaction consistency**
  - **Validates: Requirements 1.2, 1.4**

- [ ] 4. Build SchedulePreviewTable component
  - Create preview table with editable cells and validation status indicators
  - Add dropdown selectors for CST assignment resolution
  - Implement conflict highlighting and warning displays
  - Add import action buttons (Import All, Import Selected, Cancel)
  - _Requirements: 4.1, 4.2, 4.3, 4.4, 4.5, 5.3, 5.5_

- [ ]* 4.1 Write property test for preview data completeness
  - **Property 5: Preview data completeness**
  - **Validates: Requirements 4.1, 4.2, 5.3, 5.5**

- [ ] 5. Enhance TimetableEditor with AI import functionality
  - Add "AI Import" button to the existing timetable editor toolbar
  - Integrate AIImportDialog component with proper props and event handling
  - Implement import completion handling and grid refresh logic
  - Ensure seamless integration with existing editor functionality
  - _Requirements: 1.1, 6.4, 8.2, 8.5_

- [ ]* 5.1 Write property test for UI refresh consistency
  - **Property 8: UI refresh consistency**
  - **Validates: Requirements 6.4, 8.5**

- [ ] 6. Implement comprehensive error handling
  - Add frontend error handling with user-friendly messages
  - Implement backend error responses with detailed validation feedback
  - Add partial import support with success/failure reporting
  - Integrate with existing notification system for error display
  - _Requirements: 6.5, 7.4, 8.4_

- [ ]* 6.1 Write property test for format flexibility
  - **Property 9: Format flexibility**
  - **Validates: Requirements 7.3, 7.5**

- [ ]* 6.2 Write property test for error handling consistency
  - **Property 10: Error handling consistency**
  - **Validates: Requirements 7.4, 8.4**

- [ ] 7. Add data preservation and conflict resolution
  - Implement logic to preserve existing schedule data during updates
  - Add conflict detection for overlapping teacher assignments
  - Create conflict resolution interface in preview table
  - Ensure CST assignment validation and selection options
  - _Requirements: 5.2, 5.4, 6.2_

- [ ]* 7.1 Write unit tests for conflict resolution
  - Test conflict detection with known overlapping schedules
  - Test data preservation during schedule updates
  - Test CST assignment validation and selection

- [ ] 8. Implement format detection and normalization
  - Add automatic format detection for different input types
  - Implement data cleaning and whitespace normalization
  - Add support for mixed format inputs with intelligent extraction
  - Create helpful error messages with format examples
  - _Requirements: 7.1, 7.2, 7.3, 7.5_

- [ ]* 8.1 Write unit tests for format detection
  - Test automatic detection of table vs natural language formats
  - Test data cleaning with various whitespace and formatting issues
  - Test error messages for unrecognizable formats

- [ ] 9. Add integration with existing weekly system
  - Ensure compatibility with existing schedule copy and classroom selection
  - Integrate with current CST assignment system and validation rules
  - Add proper authentication and authorization checks
  - Verify integration with existing database constraints and relationships
  - _Requirements: 8.1, 8.3_

- [ ]* 9.1 Write integration tests for weekly system compatibility
  - Test integration with existing schedule copy system
  - Test CST assignment integration with current database structure
  - Test authentication and authorization with existing user roles

- [ ] 10. Final testing and validation
  - Run comprehensive test suite including property-based tests
  - Perform end-to-end testing with real schedule data
  - Validate performance with large datasets and complex schedules
  - Ensure all existing timetable editor functionality remains intact
  - _Requirements: All requirements validation_

- [ ]* 10.1 Write comprehensive property test suite
  - Run all 10 property tests with minimum 100 iterations each
  - Validate parsing, mapping, and import operations across diverse inputs
  - Test UI consistency and error handling with random data generation

## Notes

- Tasks marked with `*` are optional and can be skipped for faster MVP
- Each task references specific requirements for traceability
- Property tests validate universal correctness properties with 100+ iterations
- Unit tests validate specific examples and edge cases
- Integration tests ensure compatibility with existing weekly system components