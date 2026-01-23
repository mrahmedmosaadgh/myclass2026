# Teacher Schedule Reward System Dialog Integration

**Date**: 2026-01-23  
**Type**: Feature Enhancement  
**Status**: ✅ Complete

## Summary

Integrated the reward system as a full-screen dialog within the teacher schedule view, replacing the previous new-tab approach. Teachers can now click a visible icon button on any class cell to instantly access the reward system with all context pre-filled (classroom, subject, period, date).

## Changes Made

### 1. TimetableCell.vue
- **Removed**: Right-click context menu for reward system
- **Added**: Visible amber star icon button in top-right corner of cells (readonly mode only)
- **Event**: Emits `@open-reward` event with classroom, subject, period, and date data
- **Styling**: Button appears with 80% opacity, becomes fully visible on hover

### 2. TimetableGrid.vue
- **Added**: `@open-reward` event forwarding from TimetableCell to parent
- **Handler**: `handleOpenReward()` passes event data up the component tree

### 3. TeacherScheduleView.vue
- **Import**: Added `RewardSystemContent` component (reward_sys.vue)
- **Dialog**: Full-screen `q-dialog` with:
  - Minimize button (closes dialog without losing state)
  - Close button (fully closes dialog)
  - Primary-colored toolbar with star icon
  - Full-height scrollable content area
- **State Management**:
  - `showRewardDialog` - controls dialog visibility
  - `rewardContext` - stores selected cell data
- **Handler**: `handleOpenReward()` receives event and opens dialog with context

### 4. reward_sys.vue
- **Props Added**:
  - `classroomId` (Number) - pre-select classroom
  - `subjectId` (Number) - pre-select subject  
  - `period` (Number) - set period number
  - `date` (String) - set date
  - `isDialog` (Boolean) - flag for dialog mode

- **Initialization Priority** (in `onMounted`):
  1. **Props** (highest) - when used as dialog component
  2. **URL parameters** - when accessed via direct link
  3. **localStorage** (lowest) - fallback for normal usage

- **Auto-initialization**: Automatically loads classroom data and student list when props or URL params are provided

## User Flow

1. Teacher navigates to schedule: `/schedules/teacher/{id}/{name}`
2. Teacher sees amber star button on each class cell
3. Teacher clicks star button
4. Full-screen dialog opens on same page
5. Reward system loads with:
   - ✅ Correct classroom pre-selected
   - ✅ Correct subject pre-selected
   - ✅ Date set to today
   - ✅ Period number set
   - ✅ Student list automatically loaded
6. Teacher can minimize or close dialog
7. No new tabs - everything stays on same page

## Technical Implementation

### Component Communication Flow
```
TimetableCell (click star)
  ↓ emit('open-reward', data)
TimetableGrid
  ↓ forward event
TeacherScheduleView
  ↓ handleOpenReward()
  ↓ set rewardContext
  ↓ showRewardDialog = true
RewardSystemContent (dialog)
  ↓ receive props
  ↓ auto-initialize
```

### Props Priority Logic
```javascript
if (props.classroomId && props.subjectId) {
  // Use props (dialog mode)
} else if (urlParams.has('classroom_id')) {
  // Use URL params
} else {
  // Use localStorage
}
```

## Files Modified

- `resources/js/Pages/my_table_mnger/weekly_system/components/timetable/TimetableCell.vue`
- `resources/js/Pages/my_table_mnger/weekly_system/components/timetable/TimetableGrid.vue`
- `resources/js/Pages/my_table_mnger/weekly_system/TeacherScheduleView.vue`
- `resources/js/Pages/my_table_mnger/reward_sys/reward_sys.vue`

## Benefits

✅ **Better UX** - Visible button instead of hidden right-click menu  
✅ **Same page** - No context switching with new tabs  
✅ **Easy minimize** - Dedicated minimize button  
✅ **Full screen** - Maximum workspace  
✅ **Pre-filled context** - No manual selection needed  
✅ **Backward compatible** - URL params and standalone mode still work  
✅ **Reusable component** - reward_sys.vue works as page or dialog

## Testing

Verified:
- ✅ Star button appears on all class cells in teacher schedule
- ✅ Button is visible and clickable
- ✅ Dialog opens full-screen on same page
- ✅ Classroom, subject, period, and date are pre-filled
- ✅ Student list loads automatically
- ✅ Minimize button works
- ✅ Close button works
- ✅ Can reopen dialog multiple times
- ✅ Standalone reward_sys page still works
- ✅ URL parameters still work

## Future Enhancements

- [ ] Add keyboard shortcut to open reward system (e.g., Ctrl+R)
- [ ] Add animation when dialog opens/closes
- [ ] Consider adding quick actions in the button dropdown
- [ ] Add badge showing student count on the button
