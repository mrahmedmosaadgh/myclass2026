---
title: Lesson Player Analysis & Teacher Schedule Integration
description: Detailed reference of the Lesson Player component structure, Teacher Schedule integration, and related routes.
---

# Lesson Player & Teacher Schedule Integration

This document serves as a reference for the `Lesson Presentation` system, specifically focusing on the Student View and its integration with the Teacher Schedule (formerly Reward System button).

## 1. Student Lesson View

**URL Pattern**: `/lesson-presentation/student/{id}`  
**Route Name**: `lesson-presentation.student` (Likely, found in `LessonPresentationController@studentView` or similar - check Controller)

**Frontend Entry Point**:  
The view loads the Vue Page. Based on file structure:  
`resources/js/Pages/my_table_mnger/lesson_presentation/components/LessonPlayer.vue` is the core component, likely wrapped by a parent page component (e.g., `StudentView.vue` or usage in `lesson_presentation.vue` preview).

*Note: The actual Inertia Page for `/lesson-presentation/student/{id}` typically corresponds to a controller method returning `Inertia::render('Path/To/Component')`.*

### Core Component: `LessonPlayer.vue`
*   **Path**: `resources/js/Pages/my_table_mnger/lesson_presentation/components/LessonPlayer.vue`
*   **Purpose**: Renders the slide deck, quizzes, and interactive elements for the student interface.
*   **Key Features**:
    *   Slide Navigation (Next/Prev)
    *   Section Sidebar
    *   Fullscreen Mode
    *   **Teacher Schedule Dialog (New)**

## 2. Teacher Schedule Integration

**User Action**: Clicking the **"Teacher Schedule"** button (Calendar Icon `event_note`) in the header.

**Behavior**:
1.  **Direct Open**: The button immediately opens an **Overlay/Dialog**.
    *   *Previous Logic*: It used to check for a selected classroom and prompt selection if missing. This was simplified to load the general schedule.
2.  **Overlay Type**: Custom `<div>` overlay with `v-show` (to prevent iframe reloading).
    *   **Class**: `fixed-full column no-wrap`
    *   **Z-Index**: `6000` (Top layer)
3.  **Content**: An `<iframe>` loading the Teacher Schedule URL.

### Backend Route
**URL**: `/schedules/my-schedule`  
**Route Name**: `schedules.teacher.my-schedule`  
**Controller**: `App\Http\Controllers\ScheduleController@showMySchedule`

**What it loads**:
*   This route returns the view/page for the authenticated teacher's schedule.
*   It likely renders a Vue component similar to `TeacherSchedule.vue` or a Blade view `schedules.teacher.my_schedule`.

## 3. Reward System (Reference)

**URL**: `/reward_sys`  
**Route Name**: `reward_sys`  
**Controller**: Likely a closure or `RewardSystemController`.

*   *Note*: The button in `LessonPlayer.vue` was originally for "Reward System". It has been repurposed to "Teacher Schedule".
*   If we need to restore Reward System, the URL is `/reward_sys`.
*   It supports query parameters like `?classroom_id=123`.

## 4. Minimized State Logic

**Feature**: "Keep Active" (Minimize)  
**Goal**: Allow the teacher to hide the schedule overlay without reloading the iframe (preserving scroll position/state).

**Implementation**:
1.  **Minimize Button**: In the overlay header.
    *   Action: Sets `showRewardDialog = false` and `isScheduleMinimized = true`.
2.  **Floating Action Button (FAB)**: Appears at bottom-left when minimized.
    *   Action: Sets `showRewardDialog = true` and `isScheduleMinimized = false`.
3.  **State Preservation**:
    *   The overlay uses `<div v-show="...">` instead of `<q-dialog v-if="...">`.
    *   `v-show` toggles CSS `display: none`, keeping the DOM (and iframe) alive.

## 5. Technical Stack

*   **Frontend**: Vue.js 3, Quasar Framework (UI components)
*   **Routing**: Inertia.js / Larave Routes
*   **Iframe Integration**: Used for embedding external/independent modules (`schedules`, `reward_sys`) into the Lesson Player context to avoid full page navigation.

## 6. Future Improvements Ref

*   **Classroom Context**: If the schedule needs to filter by the current lesson's classroom, we can re-enable the classroom selection logic in `LessonPlayer.vue` (currently commented out).
*   **Seamless Mode**: `q-dialog` with `seamless` prop could be an alternative to the custom `div` overlay if standard dialog features (accessibility, focus trap) are desired while allowing interaction behind it.

