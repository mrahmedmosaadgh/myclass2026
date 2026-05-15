# Tab-Based Refactor Plan for GroupQuizGenerator

## Current State
- Side-by-side layout with "Setup AI Prompt" (left) and "Paste AI Output" (right)
- Both sections visible simultaneously in a split view
- Limited space for preview area due to split layout

## Target State
- Tab-based interface with two distinct tabs
- Tab 1: "Setup AI Prompt" - Full width for prompt configuration
- Tab 2: "Paste & Preview" - Full width for JSON input and question preview
- Maximum space for preview area in Tab 2

## Implementation Strategy

### 1. Add Tab State Management
- Add `selectedTab` ref with default value 'setup' (or 0)
- Tab options: 'setup' and 'preview' (or 0 and 1)

### 2. Add Tab Navigation UI
- Add tab navigation bar at top of modal body
- Two tab buttons with active/inactive states
- Visual feedback for current tab
- Styled with modern tab design (pill or underline style)

### 3. Restructure Template
- Replace `.generator-split` flex container with tab content
- Use `v-if` or `v-show` to switch between tab content
- Tab 1 content: Existing `.prompt-col` content
- Tab 2 content: Existing `.action-col` content with full width

### 4. Adjust Layout for Full Width
- Remove `.generator-col` wrapper styling (flex: 1, background, etc.)
- Make tab content containers full width
- Adjust preview area to use available space more effectively
- Remove `margin-left: 44px` from `.q-options` since more space available

### 5. Tab Styling
- Modern tab design with smooth transitions
- Active tab: Highlighted background or underline
- Inactive tab: Muted color
- Hover effects for better UX

## Technical Changes

### File: `GroupQuizGenerator.vue`

#### Script Changes
```javascript
const selectedTab = ref('setup'); // 'setup' or 'preview'
```

#### Template Changes
- Add tab navigation bar before `.modal-body` content
- Wrap existing split content in conditional blocks
- Tab 1: `v-if="selectedTab === 'setup'"`
- Tab 2: `v-if="selectedTab === 'preview'"`

#### Style Changes
- Add `.tab-nav` styling
- Add `.tab-btn` styling (active/inactive states)
- Adjust `.generator-col` to work without flex split
- Update `.q-options` layout for full width (remove margin-left)
- Increase preview area max-height for better visibility

## Expected Outcome
- Cleaner, more focused workflow
- Maximum space for preview area
- Better mobile responsiveness
- Clearer separation of concerns
- Improved UX with tab-based navigation
