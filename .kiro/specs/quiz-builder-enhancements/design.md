# Design Document: Quiz Builder Enhancements

## Overview

The Quiz Builder Enhancements will extend the existing Vue 3-based quiz creation system with advanced filtering, bulk operations, points-based scoring, and section organization capabilities. The design maintains the current 3-panel responsive layout while adding new functionality through component composition and state management enhancements.

The system will leverage the existing Quasar UI framework, vuedraggable library, and Inertia.js integration while introducing new components for filtering, scoring, and section management. All enhancements will be backward compatible with existing quiz data and API endpoints.

## Architecture

### Component Hierarchy

```
QuizBuilder.vue (Enhanced)
├── QuestionPool.vue (Enhanced)
│   ├── AdvancedFilters.vue (New)
│   │   ├── TopicFilter.vue (New)
│   │   ├── BloomsTaxonomyFilter.vue (New)
│   │   └── AuthorFilter.vue (New)
│   └── BulkOperations.vue (New)
├── QuizCanvas.vue (Enhanced)
│   ├── SectionBreak.vue (New)
│   ├── QuestionItem.vue (Enhanced with points)
│   └── SectionManager.vue (New)
├── QuizSettings.vue (Enhanced)
│   └── ScoringSettings.vue (New)
└── LiveStats.vue (Enhanced with points display)
```

### State Management

The design will extend the existing Composition API state management with new reactive stores:

- **FilterStore**: Manages filter state and persistence
- **ScoringStore**: Handles point calculations and scoring logic
- **SectionStore**: Manages section organization and numbering
- **BulkOperationStore**: Coordinates multi-question operations

### Data Flow

1. **Filter Application**: User interactions with filters trigger reactive updates to the question pool display
2. **Bulk Operations**: Multi-select state enables batch operations that update both canvas and statistics
3. **Points Calculation**: Point assignments trigger reactive updates to total scores and statistics
4. **Section Management**: Section operations maintain question organization and numbering consistency

## Components and Interfaces

### AdvancedFilters Component

**Purpose**: Provides cascading filter controls for enhanced question selection

**Props**:
- `availableGrades: Grade[]`
- `availableSubjects: Subject[]`
- `availableTopics: Topic[]`
- `authors: Author[]`

**Events**:
- `@filter-changed: (filters: FilterState) => void`

**Key Methods**:
- `applyFilters(filters: FilterState): void`
- `clearFilters(): void`
- `persistFilters(): void`

### BulkOperations Component

**Purpose**: Enables multi-question selection and batch operations

**Props**:
- `filteredQuestions: Question[]`
- `selectedQuestions: Question[]`
- `multiSelectMode: boolean`

**Events**:
- `@add-all-filtered: () => void`
- `@add-selected: (questions: Question[]) => void`
- `@toggle-multi-select: (enabled: boolean) => void`

### ScoringSettings Component

**Purpose**: Manages point assignment and scoring configuration

**Props**:
- `questions: QuizQuestion[]`
- `defaultPointsByDifficulty: PointsMap`

**Events**:
- `@points-updated: (questionId: string, points: number) => void`
- `@passing-score-changed: (threshold: number) => void`

**Key Methods**:
- `calculateTotalPoints(): number`
- `validatePassingScore(threshold: number): boolean`
- `applyDefaultPoints(question: Question): number`

### SectionManager Component

**Purpose**: Handles section creation, organization, and display

**Props**:
- `sections: Section[]`
- `questions: QuizQuestion[]`

**Events**:
- `@section-added: (section: Section) => void`
- `@section-updated: (sectionId: string, updates: Partial<Section>) => void`
- `@questions-reordered: (newOrder: QuizQuestion[]) => void`

## Data Models

### Enhanced Question Model

```typescript
interface QuizQuestion extends Question {
  points: number;
  sectionId?: string;
  orderInSection: number;
}
```

### Filter State Model

```typescript
interface FilterState {
  grade?: string;
  subject?: string;
  topic?: string;
  bloomsLevel?: string;
  author?: string;
  usedInQuiz?: 'used' | 'unused' | 'all';
  searchTerm?: string;
}
```

### Section Model

```typescript
interface Section {
  id: string;
  name: string;
  instructions?: string;
  orderIndex: number;
  collapsed: boolean;
  questions: QuizQuestion[];
}
```

### Scoring Configuration Model

```typescript
interface ScoringConfig {
  defaultPoints: {
    easy: number;
    medium: number;
    hard: number;
  };
  passingScoreThreshold?: number;
  totalPossiblePoints: number;
}
```

## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system—essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

### Property 1: Filter System Consistency
*For any* combination of grade, subject, topic, Bloom's level, author, and usage status filters, the Filter_System should display only questions that match ALL applied filter criteria, and the filtered results should be consistent across multiple applications of the same filter combination.
**Validates: Requirements 1.1, 1.2, 1.3, 1.4, 1.5**

### Property 2: Filter State Persistence
*For any* valid filter configuration, applying filters then reloading the application should restore the exact same filter state and produce identical filtered results.
**Validates: Requirements 1.6**

### Property 3: Bulk Operations Completeness
*For any* set of filtered questions, the "Add All Filtered" operation should add exactly the questions currently visible in the pool, and multi-select batch operations should add exactly the questions that were selected.
**Validates: Requirements 2.1, 2.3**

### Property 4: Statistics Update Consistency
*For any* bulk operation or point value change, the Live_Stats should immediately reflect the new state with accurate totals, counts, and distributions.
**Validates: Requirements 2.5, 3.4**

### Property 5: Default Points Assignment
*For any* question added to the canvas, the Points_System should assign points based on the question's difficulty level according to the configured default values.
**Validates: Requirements 3.1**

### Property 6: Custom Points Persistence
*For any* valid point value assigned to a question, the Points_System should store and maintain that value until explicitly changed, regardless of other operations performed on the quiz.
**Validates: Requirements 3.3**

### Property 7: Passing Score Validation
*For any* passing score threshold, the Quiz_Builder should accept values that are less than or equal to the total possible points and reject values that exceed the total.
**Validates: Requirements 3.5**

### Property 8: Section Organization Integrity
*For any* section containing questions, adding, removing, or reordering questions should maintain the logical organization within sections and preserve section boundaries in question numbering.
**Validates: Requirements 4.1, 4.4, 4.5**

### Property 9: Section Collapse State
*For any* section with questions, the collapse/expand functionality should work independently for each section and preserve the visibility state of questions within collapsed sections.
**Validates: Requirements 4.3**

### Property 10: Smart Selection Algorithm Compliance
*For any* smart selection request (random or balanced), the algorithm should select questions only from the currently filtered pool and respect the specified selection criteria (randomness for random selection, difficulty distribution for balanced selection).
**Validates: Requirements 5.1, 5.2, 5.4**

### Property 11: Question Pool Statistics Accuracy
*For any* question pool, the displayed statistics should accurately reflect the actual distribution of questions by difficulty, topic, author, and other attributes in the current filtered set.
**Validates: Requirements 5.3**

### Property 12: Backward Compatibility Preservation
*For any* existing quiz data or API interaction, the enhanced system should process and respond identically to the original system, maintaining full compatibility with existing data formats and endpoints.
**Validates: Requirements 6.1, 6.2, 6.4**

### Property 13: UI Functionality Preservation
*For any* existing UI interaction (drag-and-drop, responsive layout), the enhanced system should maintain identical behavior and visual presentation across all supported screen sizes and interaction methods.
**Validates: Requirements 6.3, 6.5**

## Error Handling

### Filter System Errors
- **Invalid Filter Combinations**: When cascading filters result in no available options, display appropriate empty state messages
- **Persistence Failures**: If localStorage is unavailable, gracefully degrade to session-only filter state
- **API Filter Errors**: Handle server-side filtering errors with user-friendly messages and fallback to client-side filtering

### Bulk Operation Errors
- **Memory Constraints**: Prevent bulk operations that would exceed browser memory limits
- **Concurrent Modifications**: Handle race conditions when multiple users modify the same quiz
- **Partial Failures**: Provide detailed feedback when bulk operations partially succeed

### Scoring System Errors
- **Invalid Point Values**: Validate point inputs and prevent negative or non-numeric values
- **Calculation Overflow**: Handle edge cases where point totals exceed safe integer limits
- **Threshold Validation**: Provide clear feedback when passing score thresholds are invalid

### Section Management Errors
- **Orphaned Questions**: Ensure questions are never left without a valid section assignment
- **Circular Dependencies**: Prevent section organization that could create infinite loops
- **Numbering Conflicts**: Resolve conflicts in question numbering across sections

## Testing Strategy

### Dual Testing Approach

The testing strategy employs both unit tests and property-based tests to ensure comprehensive coverage:

**Unit Tests** focus on:
- Specific examples of filter combinations and expected results
- Edge cases like empty question pools or invalid inputs
- Integration points between components
- Error conditions and recovery scenarios
- UI interaction examples (confirmation dialogs, section creation)

**Property-Based Tests** focus on:
- Universal properties that hold across all valid inputs
- Comprehensive input coverage through randomization
- Correctness properties defined in the design document
- System behavior under various data combinations

### Property-Based Testing Configuration

- **Testing Library**: Use `fast-check` for JavaScript/TypeScript property-based testing
- **Test Iterations**: Minimum 100 iterations per property test to ensure thorough coverage
- **Test Tagging**: Each property test references its corresponding design document property
- **Tag Format**: `// Feature: quiz-builder-enhancements, Property N: [property description]`

### Testing Implementation Guidelines

**Unit Testing Balance**:
- Focus unit tests on specific examples, edge cases, and integration points
- Avoid excessive unit tests for scenarios covered by property tests
- Emphasize testing of user interactions and error conditions

**Property Test Implementation**:
- Each correctness property must be implemented as a single property-based test
- Generate random test data for questions, filters, sections, and user interactions
- Verify that properties hold across all generated inputs
- Use property test failures to identify edge cases for additional unit tests

### Test Coverage Areas

1. **Filter System Testing**
   - Property tests for filter consistency and persistence
   - Unit tests for specific filter combinations and edge cases

2. **Bulk Operations Testing**
   - Property tests for operation completeness and statistics updates
   - Unit tests for confirmation dialogs and error scenarios

3. **Scoring System Testing**
   - Property tests for point assignment and validation
   - Unit tests for specific point values and threshold examples

4. **Section Management Testing**
   - Property tests for organization integrity and numbering
   - Unit tests for section creation and collapse functionality

5. **Compatibility Testing**
   - Property tests for backward compatibility preservation
   - Unit tests for specific existing data formats and API calls