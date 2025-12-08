# Design Document

## Overview

The reward system settings feature will add a comprehensive settings management system to the existing Vue component. The design focuses on integrating seamlessly with the current Quasar-based UI while providing intuitive controls for avatar editing and attendance management. The solution will use reactive Vue 3 composition API patterns consistent with the existing codebase.

## Architecture

### Component Structure
- **Main Component**: `reward_sys.vue` (existing) - will be enhanced with settings functionality
- **Settings Panel**: Integrated dialog/drawer within the main component
- **State Management**: Reactive refs for settings state with localStorage persistence
- **Visual State Management**: Computed properties and conditional rendering for UI updates

### Data Flow
1. Settings state stored in reactive refs
2. Settings changes trigger immediate UI updates via computed properties
3. Attendance state managed per classroom selection
4. Settings persistence handled through localStorage
5. Visual states applied through conditional classes and v-show directives

## Components and Interfaces

### Settings State Interface
```typescript
interface SettingsState {
  avatarEditingEnabled: boolean
  attendance: Record<string, boolean> // studentId -> isPresent
  currentClassroomId: string | null
}
```

### Settings Panel Component Structure
```vue
<!-- Settings Dialog -->
<q-dialog v-model="showSettingsDialog" persistent>
  <q-card style="min-width: 600px">
    <q-card-section>
      <div class="text-h6">Classroom Settings</div>
    </q-card-section>
    
    <!-- Avatar Editing Toggle -->
    <q-card-section>
      <q-toggle 
        v-model="settings.avatarEditingEnabled"
        label="Enable Avatar Editing"
        color="primary"
      />
    </q-card-section>
    
    <!-- Attendance Section -->
    <q-card-section>
      <div class="text-subtitle1 mb-3">Student Attendance</div>
      <div class="attendance-list">
        <!-- Student attendance toggles -->
      </div>
    </q-card-section>
  </q-card>
</q-dialog>
```

### Student Card Modifications
The existing student cards will be enhanced with:
- Conditional avatar editing buttons based on `settings.avatarEditingEnabled`
- Conditional styling based on attendance status
- Disabled interaction states for absent students

### Settings Button Integration
A settings button will be added to the main control panel:
```vue
<q-btn 
  color="grey-7" 
  icon="settings" 
  label="Settings"
  @click="showSettingsDialog = true" 
  class="shadow-sm rounded-lg" 
  size="md" 
/>
```

## Data Models

### Settings Reactive State
```javascript
const settings = ref({
  avatarEditingEnabled: true,
  attendance: {},
  currentClassroomId: null
})
```

### Computed Properties
```javascript
const isStudentPresent = computed(() => (studentId) => {
  return settings.value.attendance[studentId] !== false
})

const shouldShowAvatarButtons = computed(() => {
  return settings.value.avatarEditingEnabled
})

const getStudentCardClass = computed(() => (studentId) => {
  const baseClass = 'student-card cursor-pointer transition-all duration-300'
  const presentClass = 'hover:shadow-xl hover:-translate-y-1'
  const absentClass = 'opacity-50 grayscale cursor-not-allowed'
  
  return isStudentPresent.value(studentId) 
    ? `${baseClass} ${presentClass}`
    : `${baseClass} ${absentClass}`
})
```

### Persistence Functions
```javascript
const saveSettings = () => {
  localStorage.setItem('reward-system-settings', JSON.stringify(settings.value))
}

const loadSettings = () => {
  const saved = localStorage.getItem('reward-system-settings')
  if (saved) {
    Object.assign(settings.value, JSON.parse(saved))
  }
}
```

## Error Handling

### Settings Persistence
- Graceful fallback if localStorage is not available
- JSON parsing error handling for corrupted settings data
- Default settings restoration on error

### Attendance State Management
- Automatic reset when classroom changes
- Validation of student IDs against current classroom roster
- Cleanup of orphaned attendance records

### UI State Consistency
- Immediate visual feedback for all setting changes
- Proper cleanup of event listeners and watchers
- Error boundaries for settings dialog operations

## Testing Strategy

### Unit Tests
1. **Settings State Management**
   - Test toggle functionality for avatar editing
   - Test attendance state updates
   - Test settings persistence and loading

2. **Computed Properties**
   - Test student presence calculations
   - Test conditional class applications
   - Test avatar button visibility logic

3. **UI Integration**
   - Test settings dialog open/close
   - Test immediate UI updates on setting changes
   - Test classroom change effects on attendance

### Integration Tests
1. **Full Settings Workflow**
   - Open settings → change avatar editing → verify UI update
   - Mark students absent → verify card styling changes
   - Change classroom → verify attendance reset

2. **Persistence Testing**
   - Save settings → refresh page → verify settings maintained
   - Test localStorage error scenarios
   - Test settings migration/upgrade scenarios

### Visual Testing
1. **Student Card States**
   - Present student card appearance
   - Absent student card appearance (grayed out)
   - Avatar button visibility states

2. **Settings Panel**
   - Toggle switch states and labels
   - Attendance list layout and interactions
   - Responsive design on different screen sizes

## Implementation Notes

### Integration Points
- Settings button will be added to the existing control panel grid
- Student card template will be modified with conditional rendering
- Existing avatar upload functions will check settings before executing

### Performance Considerations
- Use computed properties for efficient reactivity
- Debounce localStorage writes to avoid excessive I/O
- Lazy load attendance state only when needed

### Accessibility
- Proper ARIA labels for all toggle switches
- Keyboard navigation support for settings dialog
- Screen reader announcements for state changes

### Browser Compatibility
- localStorage fallback for older browsers
- CSS feature detection for advanced styling
- Progressive enhancement approach