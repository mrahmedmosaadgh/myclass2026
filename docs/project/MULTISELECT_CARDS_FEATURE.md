# 🎯 Multi-Select Cards Feature

## ✅ Feature Overview

Students cards are now **selectable** with multi-select support, allowing teachers to quickly apply behaviors to multiple students at once.

---

## 🎨 Visual Changes

### Card States

#### **Normal State**
```
┌──────────────┐
│ ☐  [10]      │ ← Checkbox (unchecked)
│   Monster    │
│   Ahmed      │
│    Ali       │
└──────────────┘
```

#### **Selected State**
```
┌──────────────┐
│ ☑  [10]      │ ← Checkbox (checked, blue)
│   Monster    │ ← Blue border
│   Ahmed      │ ← Light blue background
│    Ali       │
└──────────────┘
```

#### **Disabled State** (Absent Students)
```
┌──────────────┐
│ ☐  [10]      │ ← Grayed out
│   Monster    │ ← 50% opacity
│   Ahmed      │ ← Grayscale filter
│    Ali       │
└──────────────┘
```

---

## 🎮 How to Use

### Step 1: Select Students
Click on student cards to select/deselect them:
- Click once → Selected (blue border, checkbox checked)
- Click again → Deselected (normal state)
- Multiple students can be selected

### Step 2: Choose Behavior
Use the quick action controls at the top:
- **+ Behavior dropdown** - Select positive behavior
- **- Behavior dropdown** - Select negative behavior

### Step 3: Apply
Click the corresponding button:
- **Green + button** - Apply positive behavior to selected students
- **Red - button** - Apply negative behavior to selected students

### Step 4: Clear (Optional)
Click **"Clear Selection"** to deselect all students

---

## 🎯 Quick Actions Panel

Located above the student cards:

```
┌─────────────────────────────────────────────────┐
│ Quick Actions              Selected: 3 students │
├─────────────────────────────────────────────────┤
│                                                 │
│ [+ Behavior ▼]  [+]   [- Behavior ▼]  [-]  [Clear] │
│                                                 │
└─────────────────────────────────────────────────┘
```

**Features:**
- Shows count of selected students
- Dropdowns for positive/negative behaviors
- Quick apply buttons
- Clear selection button
- Buttons disabled when no students selected

---

## 🔒 Rules & Restrictions

### Cannot Select Absent Students
- Absent students appear grayed out
- Clicking shows warning: "Cannot select absent students"
- Checkbox is visible but card is disabled

### Auto-Clear on Tab Switch
- Selection is cleared when switching tabs
- Prevents confusion between different views

### Validation
- Must select at least 1 student
- Must choose a behavior
- Both conditions required to enable apply button

---

## 💡 User Experience

### Visual Feedback
1. **Hover Effect** - Card lifts slightly
2. **Selection** - Blue border + light blue background
3. **Checkbox** - Animated check mark
4. **Counter** - Shows selected count in real-time
5. **Button States** - Disabled when conditions not met

### Keyboard Support
- Click to select/deselect
- Visual focus indicators
- Accessible design

---

## 🎨 Color Coding

| State | Border | Background | Opacity |
|-------|--------|------------|---------|
| Normal | Transparent | White | 100% |
| Selected | Blue (#3498db) | Light Blue | 100% |
| Disabled | Transparent | White | 50% |
| Hover | Transparent | White | 100% (lifted) |

---

## 📊 Workflow Example

### Scenario: Reward 3 Students for Participation

**Step 1: Select Students**
```
Click Ahmed → Selected (1)
Click Sara → Selected (2)
Click Omar → Selected (3)

Counter shows: "Selected: 3 students"
```

**Step 2: Choose Behavior**
```
Click "+ Behavior" dropdown
Select "Great participation (+5)"
```

**Step 3: Apply**
```
Click green + button
→ Loading indicator
→ Success notification
→ Points updated for all 3 students
→ Selection cleared
```

**Result:**
- Ahmed: +5 points
- Sara: +5 points
- Omar: +5 points
- All cards deselected
- Ready for next action

---

## 🔧 Technical Implementation

### Card3 Component Props

```vue
<card3
  :name="student.name"
  :positive-points="15"
  :negative-points="5"
  :student="student"
  :is-selected="true"
  :is-disabled="false"
  @toggle-select="handleToggle"
/>
```

### Selection State Management

```javascript
// In reward_sys.vue
const selectedIds = ref([])

function toggleSelected(studentId) {
  const idx = selectedIds.value.indexOf(studentId)
  if (idx === -1) {
    selectedIds.value.push(studentId)  // Add
  } else {
    selectedIds.value.splice(idx, 1)   // Remove
  }
}
```

### Apply Behavior

```javascript
async function applyPositiveBehavior() {
  await applyBehaviorToStudents(selectedPositiveBehaviorId.value)
  selectedPositiveBehaviorId.value = null
  selectedIds.value = []  // Clear selection
}
```

---

## 🎯 Benefits

### For Teachers:
- ✅ **Faster workflow** - Select multiple students at once
- ✅ **Visual feedback** - Clear indication of selection
- ✅ **Fewer clicks** - Bulk operations instead of one-by-one
- ✅ **Error prevention** - Can't select absent students

### For System:
- ✅ **Consistent UI** - Same selection pattern everywhere
- ✅ **Reusable component** - Card3 can be used elsewhere
- ✅ **Accessible** - Keyboard and screen reader support
- ✅ **Performant** - Efficient state management

---

## 🔄 Integration Points

### Works With:
- ✅ Attendance tracking (disabled for absent)
- ✅ Positive behaviors
- ✅ Negative behaviors
- ✅ Point calculations
- ✅ History tracking

### Future Enhancements:
- 🔮 Select all button
- 🔮 Select by criteria (e.g., all with >10 points)
- 🔮 Drag to select multiple
- 🔮 Keyboard shortcuts (Ctrl+A, etc.)

---

## 📱 Responsive Design

### Desktop
- Cards in grid layout
- Quick actions panel above
- Hover effects enabled

### Tablet
- Responsive grid (2-3 columns)
- Touch-friendly card size
- Tap to select

### Mobile
- Single column layout
- Larger touch targets
- Simplified quick actions

---

## 🐛 Troubleshooting

### Selection Not Working
- Check if student is absent (disabled)
- Verify `selectedIds` array is updating
- Check console for errors

### Apply Button Disabled
- Ensure at least 1 student selected
- Ensure behavior is chosen
- Check if `applyingBehavior` is stuck on true

### Cards Not Showing Selection
- Verify `:is-selected` prop is passed
- Check if `selectedIds` includes student ID
- Inspect CSS classes

---

## 📝 Code Snippets

### Select All Present Students
```javascript
function selectAllPresent() {
  selectedIds.value = students.value
    .filter(s => studentAttendance.value[s.id])
    .map(s => s.id)
}
```

### Deselect All
```javascript
function clearSelection() {
  selectedIds.value = []
}
```

### Check if Any Selected
```javascript
const hasSelection = computed(() => selectedIds.value.length > 0)
```

---

**Status:** ✅ Complete
**Last Updated:** 2025-11-17
**Version:** 2.5
