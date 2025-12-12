# Implementation Plan

- [x] 1. Set up backend API endpoints and controllers





  - Create QuestionController with CRUD methods
  - Implement filtering, pagination, and search logic
  - Add validation rules for question creation/update
  - _Requirements: 1.1-1.7, 3.1-3.5, 4.1-4.9, 5.1-5.6_

- [ ]* 1.1 Write property test for question creation
  - **Property 3: Valid question persistence**
  - **Validates: Requirements 1.6**

- [ ]* 1.2 Write property test for validation errors
  - **Property 4: Validation error display**
  - **Validates: Requirements 1.7**

- [ ]* 1.3 Write property test for search filtering
  - **Property 12: Search filter accuracy**
  - **Validates: Requirements 4.1**

- [ ]* 1.4 Write property test for combined filters
  - **Property 19: Combined filter accuracy**
  - **Validates: Requirements 4.8**

- [x] 2. Implement question duplication and status management





  - Add duplicate endpoint to QuestionController
  - Implement status change logic
  - Handle cascading operations (copy options, update related records)
  - _Requirements: 8.1-8.5, 9.1-9.6_

- [ ]* 2.1 Write property test for question duplication
  - **Property 33: Question duplication completeness**
  - **Validates: Requirements 8.2**

- [ ]* 2.2 Write property test for duplicate status
  - **Property 34: Duplicate status initialization**
  - **Validates: Requirements 8.3**

- [ ]* 2.3 Write property test for status updates
  - **Property 36: Status update persistence**
  - **Validates: Requirements 9.2**

- [x] 3. Build question deletion with cascading





  - Implement delete endpoint with cascade to options
  - Add soft delete support (optional)
  - Handle foreign key constraints
  - _Requirements: 6.1-6.5_

- [ ]* 3.1 Write property test for cascading delete
  - **Property 24: Cascading delete**
  - **Validates: Requirements 6.3**

- [ ]* 3.2 Write property test for delete cancellation
  - **Property 25: Delete cancellation preservation**
  - **Validates: Requirements 6.4**

- [x] 4. Create bulk import functionality


  - Implement file upload endpoint
  - Add Excel/CSV parsing logic using PhpSpreadsheet
  - Build validation for import data
  - Create batch insert logic for questions and options
  - _Requirements: 7.1-7.8_

- [ ]* 4.1 Write property test for file type validation
  - **Property 27: File type validation**
  - **Validates: Requirements 7.2**

- [ ]* 4.2 Write property test for import parsing
  - **Property 28: Import file parsing**
  - **Validates: Requirements 7.3**

- [ ]* 4.3 Write property test for bulk creation
  - **Property 30: Bulk question creation**
  - **Validates: Requirements 7.5**

- [ ]* 4.4 Write property test for import with options
  - **Property 32: Import with options creation**
  - **Validates: Requirements 7.8**

- [x] 5. Create export functionality


  - Implement export endpoint with format selection
  - Add Excel export using PhpSpreadsheet
  - Add CSV export
  - Format options data for re-import compatibility
  - _Requirements: 12.1-12.6_

- [ ]* 5.1 Write property test for export completeness
  - **Property 42: Export data completeness**
  - **Validates: Requirements 12.2**

- [ ]* 5.2 Write property test for export-import round trip
  - **Property 43: Export-import round trip**
  - **Validates: Requirements 12.6**

- [x] 6. Checkpoint - Ensure all backend tests pass



  - Ensure all tests pass, ask the user if questions arise.

- [x] 7. Create QuestionCard component (if not exists)


  - Build reusable question display card
  - Add action buttons (edit, duplicate, delete)
  - Show question metadata (type, difficulty, subject, status)
  - Add visual indicators for question status
  - _Requirements: 3.2_

- [ ]* 7.1 Write property test for question display completeness
  - **Property 9: Question list display completeness**
  - **Validates: Requirements 3.2**




- [x] 8. Build QuestionFilters component


  - Create filter sidebar with all filter options
  - Implement cascading dropdowns (grade → subject → topic)
  - Add clear filters functionality
  - Emit filter changes to parent
  - _Requirements: 4.1-4.9_

- [ ]* 8.1 Write property test for cascading filters
  - **Property 5: Cascading grade-subject filter**
  - **Property 6: Cascading subject-topic filter**
  - **Validates: Requirements 2.2, 2.3**

- [ ]* 8.2 Write property test for filter reset
  - **Property 20: Filter reset completeness**
  - **Validates: Requirements 4.9**

- [x] 9. Create QuestionBank.vue main page



  - Build page layout with filters and question list
  - Implement search functionality with debouncing
  - Add pagination controls
  - Connect to API endpoints
  - Handle loading and error states
  - _Requirements: 3.1-3.5, 4.1-4.9_

- [ ]* 9.1 Write property test for pagination
  - **Property 11: Pagination navigation**
  - **Validates: Requirements 3.5**

- [ ]* 9.2 Write property test for multiple filter types
  - **Property 13: Type filter accuracy**
  - **Property 14: Difficulty filter accuracy**
  - **Property 15: Subject filter accuracy**
  - **Property 16: Grade filter accuracy**
  - **Property 17: Topic filter accuracy**
  - **Property 18: Status filter accuracy**
  - **Validates: Requirements 4.2-4.7**

- [x] 10. Build OptionEditor component


  - Create option input fields with add/remove/reorder
  - Add correct answer checkbox/radio
  - Implement drag-and-drop reordering
  - Validate at least one correct answer
  - _Requirements: 1.4, 1.5_

- [ ]* 10.1 Write property test for option management
  - **Property 2: Option management operations**


  - **Validates: Requirements 1.4**



- [ ] 11. Create QuestionForm component
  - Build dynamic form that adapts to question type
  - Add rich text editor for question text
  - Implement curriculum alignment dropdowns
  - Add cognitive settings (difficulty, Bloom level)
  - Include hints and explanation fields
  - _Requirements: 1.2, 1.3, 2.1-2.6, 10.1-10.5_

- [ ]* 11.1 Write property test for dynamic form adaptation
  - **Property 1: Dynamic form adaptation**
  - **Validates: Requirements 1.2, 1.3**

- [ ]* 11.2 Write property test for difficulty validation
  - **Property 7: Difficulty level validation**
  - **Validates: Requirements 2.4**

- [ ]* 11.3 Write property test for Bloom level validation
  - **Property 8: Bloom level validation**
  - **Validates: Requirements 2.5**

- [ ]* 11.4 Write property test for hints storage
  - **Property 39: Hints array storage**


  - **Validates: Requirements 10.2**

- [ ]* 11.5 Write property test for explanation storage
  - **Property 40: Explanation structure storage**


  - **Validates: Requirements 10.3, 10.5**

- [ ] 12. Create QuestionEditor.vue page
  - Build page layout with QuestionForm
  - Handle create vs edit mode
  - Implement save and cancel actions
  - Add form validation
  - Show success/error notifications
  - _Requirements: 1.1-1.7, 5.1-5.6_

- [ ]* 12.1 Write property test for edit form population
  - **Property 21: Edit form population**


  - **Validates: Requirements 5.2**

- [ ]* 12.2 Write property test for update persistence
  - **Property 22: Question update persistence**
  - **Validates: Requirements 5.4**

- [ ]* 12.3 Write property test for edit cancellation
  - **Property 23: Edit cancellation preservation**
  - **Validates: Requirements 5.5**

- [ ] 13. Build ImportPreview component
  - Display preview table of parsed questions
  - Show validation status for each row
  - Highlight errors and warnings
  - Allow filtering to show only errors
  - _Requirements: 7.4, 7.6_

- [ ]* 13.1 Write property test for preview generation
  - **Property 29: Import preview generation**
  - **Validates: Requirements 7.4**





- [ ]* 13.2 Write property test for error reporting
  - **Property 31: Import error reporting**
  - **Validates: Requirements 7.6**

- [ ] 14. Create QuestionImport.vue page
  - Build file upload interface with drag-and-drop
  - Add template download link
  - Implement import preview with ImportPreview component


  - Handle import confirmation and progress
  - Show import results summary
  - _Requirements: 7.1-7.8_

- [ ] 15. Add routing and navigation
  - Add routes for QuestionBank, QuestionEditor, QuestionImport

  - Update navigation menu to include Question Bank
  - Add breadcrumbs for navigation context


  - _Requirements: All_

- [x] 16. Implement delete confirmation dialogs

  - Create reusable confirmation dialog component
  - Add to question delete action
  - Handle confirm and cancel actions
  - _Requirements: 6.1, 6.4_

- [x] 17. Add duplicate functionality to UI

  - Add duplicate button to QuestionCard
  - Implement duplicate action handler
  - Navigate to edit mode after duplication
  - Show success notification
  - _Requirements: 8.1-8.5_

- [ ]* 17.1 Write property test for duplicate text marking
  - **Property 35: Duplicate text marking**
  - **Validates: Requirements 8.4**



- [ ] 18. Implement status change UI
  - Add status dropdown/buttons to question cards
  - Implement status change handler
  - Show confirmation for status changes
  - Update UI after status change
  - _Requirements: 9.1-9.6_


- [ ]* 18.1 Write property test for quiz builder visibility
  - **Property 37: Active status quiz builder visibility**
  - **Property 38: Archived status quiz builder hiding**
  - **Validates: Requirements 9.3, 9.4**


- [ ] 19. Add analytics display
  - Show usage count, success rate, discrimination index on question cards
  - Format analytics for readability
  - Add tooltips explaining metrics
  - _Requirements: 11.1-11.5_


- [ ]* 19.1 Write property test for usage count increment
  - **Property 41: Usage count increment**
  - **Validates: Requirements 11.2**

- [x] 20. Implement export UI

  - Add export button to QuestionBank page
  - Create export format selection dialog
  - Handle export download
  - Show export progress and completion
  - _Requirements: 12.1-12.6_



- [x] 21. Add accessibility features


  - Implement keyboard shortcuts (Ctrl+N for new, etc.)
  - Add ARIA labels to all interactive elements
  - Ensure proper focus management
  - Test with screen reader
  - Add skip links for navigation
  - _Requirements: All (accessibility)_


- [x] 22. Implement error handling and loading states



  - Add loading spinners for async operations
  - Show error messages for failed API calls
  - Implement retry logic for failed requests
  - Add empty states for no results


  - _Requirements: All_

- [ ] 23. Add form validation feedback
  - Show inline validation errors
  - Highlight invalid fields
  - Display validation summary at top of form
  - Prevent submission with invalid data
  - _Requirements: 1.7, 2.4, 2.5_

- [ ] 24. Optimize performance
  - Implement debounced search (300ms)
  - Add pagination with configurable page size
  - Cache metadata (question types, grades, subjects)
  - Lazy load question options
  - _Requirements: All (performance)_

- [ ] 25. Final checkpoint - Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 26. Integration testing
  - Test complete question creation flow
  - Test edit and update flow
  - Test delete with confirmation
  - Test import with sample file
  - Test export and verify format
  - Test all filter combinations
  - Test duplicate functionality
  - Test status changes
  - _Requirements: All_

- [ ] 27. Update QuizBuilder to use Question Bank
  - Ensure QuizBuilder fetches from questions API
  - Filter by status (show only active by default)
  - Test integration between Question Bank and QuizBuilder
  - _Requirements: 9.3, 9.4_
