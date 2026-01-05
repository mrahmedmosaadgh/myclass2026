# 2026-01-06 00:46 | School Data Store Implementation and Fixes

## Overview
Implemented a shared Pinia store for school data management and fixed several related issues in the weekly planning system. This includes creating a centralized store for school context information and a reusable component to manage the main school data.

## Key Changes

### API & Backend
- Updated SchoolController to include related data (academic year, semester, schedule copy) when fetching school details
- Fixed API response structure to match frontend expectations

### Frontend Components
- Created a Pinia store (`schoolData.js`) to manage school context (school, academic year, semester, schedule copy)
- Developed MainSchoolData component to handle school selection and display
- Integrated the component into WeeklyPlanMenu for global access
- Updated SchoolSettingsDialog to include school selection
- Redesigned UI for better user experience

### Bug Fixes
- Fixed "Cannot access 'loadSchoolData' before initialization" error in SchoolBrowser component
- Fixed "Cannot read properties of null" error when accessing schoolData properties
- Fixed API route usage to use Laravel's route() helper function
- Fixed incorrect property names when accessing API response data
- Fixed Vue prop validation for schoolId to allow null values

### File Structure Updates
- Moved MainSchoolData component to shared components directory
- Updated import paths accordingly
- Made UI more professional with better styling and tooltips

## Technical Details
The implementation follows the project specifications by centralizing the school context management in a Pinia store, which makes it available to all components that need this information. This approach eliminates duplicate state management across components and ensures consistency in the application's school context data.