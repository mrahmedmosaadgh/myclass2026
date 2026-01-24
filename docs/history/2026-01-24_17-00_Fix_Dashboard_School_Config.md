# 2026-01-24 17:00 | Fix Dashboard School Config Display

**Detailed description of changes:**

### 1. Frontend Fixes
- **School Data Store**: Updated `resources/js/Stores/schoolData.js` to correctly map the API response properties. The store now looks for `active_academic_year_id` and `active_semester_id` (the actual database columns) instead of expecting `academic_year_id` validation keys.

### 2. Impact
- **Dashboard**: The School Configuration dialog now correctly pre-populates the "Academic Year" and "Semester" fields with the saved values.
- **Data Flow**: Aligned the frontend Store with the Backend Model and Database Schema standard established in the previous fix.

### 3. Remaining Tasks
- [ ] Monitor for any other UI components relying on the old property names.
