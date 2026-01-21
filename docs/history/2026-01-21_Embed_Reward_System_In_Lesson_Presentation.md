# Embed Reward System in Lesson Presentation

## Objective
Integrate the Reward System component as a "Seamless" dialog into the Lesson Presentation pages (`/lesson-presentation/edit` and `/lesson-presentation/student/{id}`) without affecting the original page or requiring page reloads when minimizing.

## Changes Implemented

### 1. Refactored `reward_sys.vue`
- Added `isDialog` prop (Boolean, default `false`).
- Conditionally hid the large header card and other page-specific elements when `isDialog` is true.
- Added `localStorage` persistence for `activeTab` to remember the user's last view (Attendance, Behavior, etc.) when the component is re-mounted.
- Ensured `onMounted` retrieves persisted state (`selectedClassroomId`, `activeTab`, etc.).

### 2. Lesson Presentation Editor (`lesson_presentation.vue`)
- Imported `RewardSystem` component.
- Added a "Reward System" button (Trophy icon) to the toolbar.
- Implemented a `q-dialog` with:
    - **Seamless Mode**: Uses `:seamless="isRewardMinimized"` to keep the dialog open but non-modal when minimized, allowing interaction with the background.
    - **Maximize/Minimize**: Toggles between a full-screen modal and a small bottom-right floating widget using `v-show` and dynamic props.
    - **Persistence**: Wrapped `RewardSystem` in `<keep-alive>` to preserve scroll position and component state during minimize/restore cycles.
- Fixed Quasar prop warning by changing invalid position `bottom-right` to `bottom`.

### 3. Student Lesson View (`StudentLessonView.vue`)
- Implemented identical "Seamless Minimize/Maximize" logic as the Editor.
- Conditionally displayed the entry button only for users with `role === 'teacher' || 'admin'`.

### 4. Technical Improvements
- **State Persistence**: The Reward System now saves `selectedClassroomId`, `selectedSubjectId`, and `activeTab` to Local Storage. This ensures that even if the page is refreshed, the user returns to their last context.
- **Zero Reload**: The "Minimize" action does not close/unmount the component; it simply changes its visual state, preserving all internal data (like selected students) instantly.

## Status
- [x] Refactor `reward_sys.vue`
- [x] Embed in `lesson_presentation.vue`
- [x] Embed in `StudentLessonView.vue`
- [x] Implement Persistence
- [x] Implement Seamless Minimize/Maximize
- [x] Verify Audio & Functionality

## Next Steps
- None. Feature is complete and verified.
