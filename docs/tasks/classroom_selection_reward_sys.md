---
description: Implement classroom selection for reward system
---
# Task: classroom selection for reward system
## Context
When the user clicks the "Reward System" button, currently it opens `/reward_sys` in an iframe.
The user wants to handle the case "No classroom selected".
It seems `/reward_sys` might require a classroom ID.
The user says: "if 'No classroom selected' you should give me the list of my classroom so i choose and save my choises in local storage".

## Plan
1.  **Check Local Storage**: Check if a `classroom_id` is saved in `localStorage`.
2.  **If Saved**:
    *   Open the Reward System dialog with `src="/reward_sys?classroom_id=..."`.
3.  **If Not Saved**:
    *   Show a **Classroom Selection Dialog** instead of (or before) the Reward System dialog.
    *   This dialog needs to fetch the list of classrooms for the current user (teacher).
    *   API Endpoint: We need an API to list classrooms. Commonly `/api/classrooms` or similar. I'll need to assume or find an existing one. `useTeacherStore` might have it, or I might need to fetch it.
    *   User selects a classroom -> Save to `localStorage` -> Open Reward System.
4.  **Change Button Logic**: `goToRewardSystem` needs to handle this logic.

## Implementation Details in `LessonPlayer.vue`
1.  **State**:
    *   `showClassroomSelector`: boolean ref.
    *   `availableClassrooms`: array ref.
    *   `selectedClassroom`: ref (model for selection).
    *   `rewardUrl`: computed or ref to hold the iframe url.
2.  **Update `goToRewardSystem`**:
    *   `const storedClassroomId = localStorage.getItem('selected_classroom_id');`
    *   If `storedClassroomId`: `showRewardDialog = true`.
    *   Else: Fetch classrooms, `showClassroomSelector = true`.
3.  **Fetch Classrooms**:
    *   Need to find where to fetch. `axios.get(route('my_class.index'))`? Or `my_table_mnger`.
    *   If I can't find an API validation, I might need to make a best guess or ask.
    *   *Self-correction*: The user said "list of my classroom". I will try to fetch from an endpoint. Often `route('classroom.index')` or similar.
    *   Let's check `my_class` routes.

## Files to Modify
*   `c:/my_project/myclass2026-main2/resources/js/Pages/my_table_mnger/lesson_presentation/components/LessonPlayer.vue`

## Pending Info
*   What is the API to get classrooms?
*   I will search for "classroom" or "my_class" using `find_by_name` or `grep_search`.

