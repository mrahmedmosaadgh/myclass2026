# 2026-02-07 14:00 | Quiz Builder Repair Plan

## 🎯 Task Overview
The `QuizBuilder.vue` component requires immediate repair as it contains numerous template references to methods that are undefined in the script section. This plan details the necessary implementation to restore full functionality.

## 🚨 Critical Issues
1. **Missing Event Handlers**: The template uses `handleFilterChanged`, `addSelectedQuestions`, `handleSmartSelection`, etc., which are not defined.
2. **Broken Drag & Drop**: `handleDrop` exists but does nothing (empty logic).
3. **Data Loss on Save**: `saveQuiz` sends a flat list of questions, discarding any section/grouping structure created in the UI.

## 🛠 Implementation Plan

### 1. Filter Integration
- Implement `handleFilterChanged` to bridge `AdvancedFilters` component with `useFilterStore`.
- Implement `handleFiltersClear` to reset filter state.

### 2. Bulk Operations
- Map the following template events to `useBulkOperations` composable:
  - `addAllFilteredQuestions`
  - `addSelectedQuestions`
  - `handleToggleQuestionSelection`
  - `handleToggleMultiSelectMode`
  - `handleClearSelection`
  - `handleSelectAllFiltered`

### 3. Smart Selection
- Implement `handleSmartSelection` to trigger algorithms from `useSmartSelection`.
- Implement `handleSelectionFeedback` to display selection results.

### 4. Scoring & Sections
- Map section events to `useSectionStore`:
  - `handleSectionAdded`
  - `handleSectionUpdated`
  - `handleSectionDeleted`
  - `handleSectionsReordered`
  - `handleQuestionAssigned`
- Map scoring events to `useScoringStore`:
  - `handlePointsUpdated`
  - `handlePassingScoreChanged`
  - `handleScoringConfigUpdated`

### 5. UI Interactivity
- Implement `previewQuestion` to show the question preview modal.
- Fix `handleDrop` to parse dropped data and add questions to the canvas.

### 6. Data Persistence
- Update `saveQuiz` to structure the payload correctly, including:
  - Total points
  - Passing score settings
  - Section structure (array of sections with their questions)

## 📋 Action Items
- [ ] Update `QuizBuilder.vue` script section with missing methods.
- [ ] Verify `QuExamController` (backend) can accept the enhanced payload structure (or note it for backend task).
