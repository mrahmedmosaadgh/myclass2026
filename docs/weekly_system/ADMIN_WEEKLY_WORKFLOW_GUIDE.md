# Admin Weekly Plans System - Workflow Guide

This document outlines the standard operating procedures for the Administrator managing the Weekly Plans System. It covers the initial one-time setup and the recurring weekly routine.

---

## 📅 Part 1: Initial Setup (Start of Semester)

**Goal:** Ensure the system knows which schedule and semester are active so that generated plans are accurate.

### Step 1: Set Active School Context
1.  Navigate to the **School Settings** or ensure the correct context is selected in the top bar of the application (if available).
2.  **Verify the following are correct:**
    -   **Active School**: The school you are managing.
    -   **Active Schedule Copy**: The finalized timetable (Master Schedule) that will be used for the semester.
    -   **Active Semester**: The current academic semester (e.g., Semester 1 or 2).
    -   **Academic Year**: The current school year.

> **Note:** The Weekly Plans System automatically uses these global settings. If you change the Active Schedule Copy later, you may need to re-generate plans.

---

## 🔄 Part 2: Weekly Routine (Recurring)

**Goal:** Generate empty plans for teachers to fill, monitor their progress, and distribute the final plans to parents/students.

### Step 1: Select the Target Week
1.  Go to **Weekly Plans Manager** (Menu -> Weekly System -> Manager).
2.  Look at the **Filter Bar** at the top.
3.  **Select the Week Number**:
    -   Use the `<` and `>` arrows or the dropdown to select the upcoming week (e.g., Week 5).
    -   *Tip:* The system usually auto-detects the current week, but verify you are planning for the *next* week if you are preparing ahead.

### Step 2: Generate & Sync (Create Plans)
1.  Click on the **"1- Generate & Sync"** tab.
2.  Review the **"Sync Source"** card to confirm it shows the correct Schedule Copy and Semester.
3.  Click the **"Batch Sync / Generate"** button.
    -   **What happens?** The system reads the Master Schedule and creates a blank "Weekly Plan" entry for every period of every class for the selected week.
    -   *Wait for the success message.*

### Step 3: Monitor Teacher Progress
1.  Click on the **"2- Monitor Progress"** tab.
2.  **Review the Stats Cards:**
    -   **Completed:** Teachers who have filled content for all their classes.
    -   **Partial:** Teachers who have started but not finished.
    -   **Empty:** Teachers who haven't started.
3.  **Action:** Contact teachers with "Empty" or "Partial" status to ensure they complete their plans before the deadline (usually Thursday or Sunday).
    -   You can click "View Details" on a teacher to see exactly which classes are missing.

### Step 4: Final Verification & Distribution (Print/PDF)
**Goal:** Once teachers are done, export the plans for distribution.

1.  Click on the **"3- By Classroom"** tab.
2.  **Filter Classrooms (Optional):**
    -   Use the "Filter Classrooms" dropdown to select specific classes (e.g., Grade 1A, Grade 2B) or click "Select All".
3.  **Review Content:**
    -   Browse the table to ensure Classwork (CW), Homework (HW), and Notes look correct. HTML formatting (bold, lines) should verify correctly.
4.  **Export:**
    -   **For Parents (PDF):** Click the **PDF Icon** (<i class="q-icon material-icons">picture_as_pdf</i>) on the classroom header.
        -   This generates a clean, parent-friendly PDF file.
        -   Save this file and share it via Telegram, WhatsApp, or the Portal.
    -   **For School Records (Print):** Click the **Print Icon** (<i class="q-icon material-icons">print</i>) to print a physical copy.

---

## ❓ Troubleshooting

-   **"No data available" in Tab 3?**
    -   Did you select a classroom?
    -   Did you run "Generate & Sync" (Step 2) for this week?
-   **Wrong Schedule appearing?**
    -   Check the Global School Settings (Top Bar) to ensure the correct "Active Schedule Copy" is selected.
-   **Formatting looks wrong in PDF?**
    -   The PDF generator attempts to render HTML. Simple formatting (bold, lists) works best. Avoid complex copy-pasted layouts from Word.
