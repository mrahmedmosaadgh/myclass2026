# History: MyTable Schedule Component & PWA Study

**Date:** 2026-03-06 01:30
**Task:** Study and document features of the "MyTable" schedule system.

## 📋 Overview
The "MyTable" system (located in `resources/js/Pages/MicroComponentTest/mytable/`) consists of two primary implementations for visualizing school schedules:
1.  **Standalone PWA Version** (`mytable/table/`): A pure HTML/JS implementation designed as a Progressive Web App for quick access.
2.  **Vue Component Version** (`mytable/`): A modularized set of Vue components for a more modern timeline-based visualization.

---

## 🛠 Features Breakdown

### 1. Standalone PWA Schedule (`index.html`)
This version is a high-performance, standalone tool with the following features:
-   **Dynamic Schedule Loading**: Reads schedule data from `full_schedule.json`.
-   **Live Period Highlighting**: Automatically detects and highlights the current day and active class period based on real-time.
-   **Progress Visualization**:
    -   **Cell Fill**: A light red background fill that grows as the period progresses.
    -   **Live Indicator Line**: A pulsing horizontal line showing exact current time within a class cell.
-   **Header Intelligence**:
    -   **Real-time Clock**: Digital clock showing current time.
    -   **Countdown Dynamic Label**: Shows "MM:SS left in Period X" with a pulse animation.
-   **View Toggles**: Ability to switch between "Full Weekly View" and "Today's View" (filtering only current day's rows).
-   **PWA Readiness**:
    -   Full `manifest.json` and `sw.js` integration for "Install App" capability.
    -   Offline support (cached assets).
-   **Notifications & Sound**:
    -   **Push Notifications**: Alerts the user when a new period starts.
    -   **Audio Feedback**: Plays a notification sound (`notification1.mp3`) at the start of each period.
-   **Color Coding**: Subjects like "7A" and "4A" have designated color variables for quick identification.

### 2. Vue Timeline Components (`mytable/*.vue`)
A modern rewrite focusing on a horizontal timeline visualization:
-   **Modular Architecture**:
    -   `ScheduleTimeline`: Main orchestrator.
    -   `TimelineHeader`: Displays time slots (hours) horizontally.
    -   `TimelineRow`: Represents a row (e.g., a specific day or group).
    -   `TimelineBar`: Individual class/event blocks positioned using precision percentage calculations.
    -   `TimeIndicator`: A vertical red line that moves across the entire timeline to indicate current time.
-   **Flexible Time Range**: Supports dynamic `startHour` and `endHour` props to limit the timeline view.
-   **Automatic Refresh**: Synchronizes current time every minute to keep the indicator and highlighting accurate.
-   **Interactive Elements**: Hover effects and click events on schedule bars for detailed interactions.

---

## 📂 Source Files Studied
-   `resources/js/Pages/MicroComponentTest/mytable/table/index.html`
-   `resources/js/Pages/MicroComponentTest/mytable/table/full_schedule.json`
-   `resources/js/Pages/MicroComponentTest/mytable/ScheduleTimeline.vue`
-   `resources/js/Pages/MicroComponentTest/mytable/TimelineHeader.vue`
-   `resources/js/Pages/MicroComponentTest/mytable/TimelineRow.vue`
-   `resources/js/Pages/MicroComponentTest/mytable/TimelineBar.vue`
-   `resources/js/Pages/MicroComponentTest/mytable/TimeIndicator.vue`

---

## 🚀 Conclusion
The "MyTable" module is a feature-rich scheduling system tailored for teacher/student organization. It successfully bridges the gap between a standalone "Utility App" (PWA) and a "System Component" (Vue). The visual progress tracking (fills and lines) is a standout UX feature providing immediate awareness of class timing.
