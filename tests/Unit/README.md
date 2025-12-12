# Reward System Settings Unit Tests

This directory contains comprehensive unit tests for the reward system settings functionality.

## Test Files

### RewardSystemSettings.test.js
Main test suite covering core functionality:
- **Settings State Management and Persistence** (6 tests)
  - Default initialization
  - localStorage save/load operations
  - Error handling for storage operations
  - Corrupted data handling

- **Avatar Editing Toggle Functionality and UI Updates** (5 tests)
  - Toggle state management
  - UI updates when settings change
  - Button visibility control
  - Interaction prevention when disabled

- **Attendance State Changes and Visual Effects** (7 tests)
  - Student presence tracking
  - Attendance toggle functionality
  - CSS class application for present/absent states
  - Bulk attendance operations

- **Computed Properties for Student Presence and Card Classes** (4 tests)
  - Reactive presence checking
  - Dynamic CSS class computation
  - Edge case handling
  - Reactivity validation

- **Integration Tests** (4 tests)
  - Combined avatar editing and attendance logic
  - Complex state change scenarios
  - UI consistency across features

### RewardSystemSettingsEdgeCases.test.js
Extended test suite covering error scenarios and edge cases:
- **Settings Data Validation** (5 tests)
  - Null/invalid data handling
  - Data structure validation
  - Type checking and correction

- **Student ID Validation** (4 tests)
  - Invalid student data detection
  - Attendance cleanup operations
  - Validation error recovery

- **Storage Error Handling** (5 tests)
  - localStorage quota exceeded scenarios
  - Storage access failures
  - Fallback mechanisms (sessionStorage, memory)

- **Classroom Change Error Handling** (3 tests)
  - Invalid classroom ID handling
  - Student data validation during transitions
  - Error recovery mechanisms

- **Integration Error Scenarios** (3 tests)
  - Corrupted data recovery
  - Data integrity maintenance
  - Complex error handling workflows

## Test Coverage

The test suite covers all requirements specified in the task:

### Requirements 1.1, 1.2, 1.3 (Avatar Editing Control)
✅ Settings toggle functionality
✅ UI updates when avatar editing is disabled/enabled
✅ Button visibility control
✅ Interaction prevention

### Requirements 2.1, 2.2, 2.3 (Attendance Management)
✅ Student presence tracking
✅ Attendance state changes
✅ Visual effects for absent students
✅ CSS class application

### Additional Coverage
✅ Settings persistence (localStorage)
✅ Error handling and validation
✅ Edge cases and data corruption scenarios
✅ Computed properties reactivity
✅ Integration scenarios

## Running Tests

```bash
# Run all tests
npm run test

# Run specific test file
npm run test:run tests/Unit/RewardSystemSettings.test.js

# Run tests with UI
npm run test:ui
```

## Test Statistics
- **Total Tests**: 46
- **Main Functionality**: 26 tests
- **Edge Cases & Error Handling**: 20 tests
- **Coverage**: All specified requirements covered
- **Status**: ✅ All tests passing

## Mock Strategy

The tests use comprehensive mocking for:
- localStorage/sessionStorage operations
- Console methods (log, warn, error)
- Quasar components and plugins
- Vue reactivity system
- DOM interactions

This ensures tests run reliably in isolation without external dependencies.