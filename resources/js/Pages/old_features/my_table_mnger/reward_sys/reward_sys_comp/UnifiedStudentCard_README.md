# UnifiedStudentCard Component

A reusable Vue component that handles both **attendance management** and **student selection** use cases in the reward system.

## Features

- **Dual Mode Support**: Attendance mode and Selection mode
- **Visual States**: Present, absent, selected, disabled
- **Points Display**: Shows student points with color-coded badges
- **Attendance Indicators**: Visual indicators for attendance status
- **Avatar Management**: Integrated avatar editing capabilities
- **Responsive Design**: Works on all screen sizes

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `student` | Object | required | Student object with id, firstName, lastName, avatar |
| `cardMode` | String | 'selection' | Mode: 'attendance' or 'selection' |
| `selected` | Boolean | false | Whether student is selected (selection mode) |
| `selectedId` | String/Number | null | ID of selected student |
| `isPresent` | Boolean | true | Whether student is present (attendance mode) |
| `disableBehavior` | Boolean | false | Disable interaction (selection mode) |
| `allowDisabledClick` | Boolean | false | Allow clicking disabled cards |
| `showPointsBadge` | Boolean | true | Show points badge |
| `studentSummary` | Object | `{positive: 0, negative: 0, total: 0}` | Points summary |
| `avatarEditEnabled` | Boolean | false | Enable avatar editing |

## Events

| Event | Parameters | Description |
|-------|------------|-------------|
| `select` | `studentId` | Emitted when student is selected (selection mode) |
| `toggle-attendance` | `studentId, newStatus` | Emitted when attendance is toggled (attendance mode) |

## Usage Examples

### Attendance Mode
```vue
<UnifiedStudentCard
  :student="student"
  card-mode="attendance"
  :is-present="studentAttendance[student.id]"
  :show-points-badge="true"
  :student-summary="{
    positive: studentBehaviors[student.id]?.points_plus || 0,
    negative: studentBehaviors[student.id]?.points_minus || 0,
    total: (studentBehaviors[student.id]?.points_plus || 0) - (studentBehaviors[student.id]?.points_minus || 0)
  }"
  @toggle-attendance="handleToggleAttendance"
/>
```

### Selection Mode
```vue
<UnifiedStudentCard
  :student="student"
  card-mode="selection"
  :selected="selectedIds.includes(student.id)"
  :disable-behavior="!studentAttendance[student.id]"
  :show-points-badge="true"
  :student-summary="{
    positive: studentBehaviors[student.id]?.points_plus || 0,
    negative: studentBehaviors[student.id]?.points_minus || 0,
    total: (studentBehaviors[student.id]?.points_plus || 0) - (studentBehaviors[student.id]?.points_minus || 0)
  }"
  @select="handleSelectStudent"
/>
```

## Visual States

### Attendance Mode
- **Present**: Normal appearance with green attendance indicator
- **Absent**: Grayscale with red attendance indicator
- **Clickable**: Always clickable to toggle attendance

### Selection Mode
- **Available**: Normal appearance, clickable
- **Selected**: Glowing border with animation
- **Disabled**: Grayscale, not clickable (absent students)
- **Points Badge**: Color-coded based on total points

## Points Badge Colors
- **Excellent** (>10 points): Green gradient
- **Good** (>0 points): Blue gradient  
- **Neutral** (0 points): Gray gradient
- **Warning** (<0 points): Red gradient

## Student Object Structure
```javascript
{
  id: 1,
  firstName: "John",
  lastName: "Doe", 
  avatar: "path/to/avatar.jpg" // optional
}
```

## Dependencies
- Quasar Framework (q-icon, q-tooltip)
- AvatarManager component
- Vue 3 Composition API

## Migration from StudentCard

Replace existing StudentCard usage:

**Before:**
```vue
<StudentCard
  :student="student"
  :selected="selected"
  :disable-behavior="!isPresent"
  @select="handleSelect"
/>
```

**After:**
```vue
<UnifiedStudentCard
  :student="student"
  card-mode="selection"
  :selected="selected"
  :disable-behavior="!isPresent"
  @select="handleSelect"
/>
```

## Example Implementation

See `UnifiedStudentCard_Example.vue` for a complete working example with both modes.