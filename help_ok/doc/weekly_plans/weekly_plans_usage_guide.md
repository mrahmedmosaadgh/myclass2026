# Weekly Plans Usage Guide

This guide provides practical examples of how to use the Weekly Plans feature in the MyClass7 system.

## Table of Contents

1. [Creating Weekly Plans](#creating-weekly-plans)
2. [Viewing Weekly Plans](#viewing-weekly-plans)
3. [Linking Period Activities to Weekly Plans](#linking-period-activities-to-weekly-plans)
4. [Reporting and Analysis](#reporting-and-analysis)
5. [API Usage Examples](#api-usage-examples)

## Creating Weekly Plans

### Via Web Interface

1. Navigate to `/weekly-plans/create`
2. Fill in the form:
   - Select Academic Year (e.g., "2024-2025")
   - Select Semester (1 or 2)
   - Enter Week Number (e.g., 1-18)
   - Enter Period Number (e.g., 1-8)
   - Select Classroom-Subject-Teacher combination
   - Optionally select a Curriculum Lesson
   - Add any notes about the plan
3. Click "Create Weekly Plan"

The system will automatically generate a unique code for this weekly plan (e.g., "12.1.1.1").

### Via API

```javascript
// Example using axios
const createWeeklyPlan = async () => {
  try {
    const response = await axios.post('/weekly-plans', {
      academic_year_id: 12,
      semester: 1,
      week_number: 1,
      period_number: 2,
      cst_id: 45,
      curriculum_lesson_id: 123,
      notes: 'Introduction to algebra concepts'
    });
    
    console.log('Weekly plan created:', response.data);
  } catch (error) {
    console.error('Error creating weekly plan:', error);
  }
};
```

## Viewing Weekly Plans

### By Academic Year

Navigate to `/weekly-plans?academic_year_id=12` to view all weekly plans for academic year with ID 12.

### By Semester

Navigate to `/weekly-plans?academic_year_id=12&semester=1` to view all weekly plans for semester 1 of academic year 12.

### By Week

Navigate to `/weekly-plans?academic_year_id=12&semester=1&week_number=3` to view all weekly plans for week 3 of semester 1 in academic year 12.

### By Classroom-Subject-Teacher

Navigate to `/weekly-plans?cst_id=45` to view all weekly plans for the classroom-subject-teacher with ID 45.

## Linking Period Activities to Weekly Plans

When a teacher conducts a class session, they can link it to a weekly plan:

### Via Web Interface

1. Navigate to the period activity form
2. Fill in the regular period activity details
3. In the "Weekly Plan" dropdown, select the appropriate plan
   - The system will automatically populate the `lesson_code` field
4. Complete and submit the form

### Via API

```javascript
// Example using axios
const createPeriodActivity = async () => {
  try {
    const response = await axios.post('/api/period-activities', {
      schedule_id: 123,
      calendar_id: 456,
      teacher_id: 789,
      teacher_present: true,
      period_status: 'completed',
      lesson_code: '12.1.1.2', // This links to a weekly plan
      lesson_notes: 'Covered all planned material plus extra examples',
      // ... other fields
    });
    
    console.log('Period activity created:', response.data);
  } catch (error) {
    console.error('Error creating period activity:', error);
  }
};
```

## Reporting and Analysis

### Curriculum Coverage Report

To see how much of the planned curriculum has been covered:

1. Navigate to `/reports/curriculum-coverage`
2. Select filters:
   - Academic Year
   - Semester
   - Subject
   - Classroom
3. View the report showing:
   - Total weekly plans created
   - Number of plans linked to period activities
   - Percentage of curriculum covered
   - List of uncovered curriculum lessons

### Teacher Adherence Report

To see how closely teachers are following their weekly plans:

1. Navigate to `/reports/teacher-adherence`
2. Select filters:
   - Academic Year
   - Semester
   - Teacher
3. View the report showing:
   - Percentage of period activities linked to weekly plans
   - Number of deviations from planned curriculum
   - Reasons for deviations (from notes)

## API Usage Examples

### Get Weekly Plans by Academic Year

```javascript
const getWeeklyPlansByAcademicYear = async (academicYearId) => {
  try {
    const response = await axios.get(`/api/weekly-plans/by-academic-year/${academicYearId}`);
    return response.data;
  } catch (error) {
    console.error('Error fetching weekly plans:', error);
    return [];
  }
};
```

### Get Weekly Plans by Semester

```javascript
const getWeeklyPlansBySemester = async (academicYearId, semester) => {
  try {
    const response = await axios.get(`/api/weekly-plans/by-semester/${academicYearId}/${semester}`);
    return response.data;
  } catch (error) {
    console.error('Error fetching weekly plans:', error);
    return [];
  }
};
```

### Get Weekly Plans by Week

```javascript
const getWeeklyPlansByWeek = async (academicYearId, semester, weekNumber) => {
  try {
    const response = await axios.get(`/api/weekly-plans/by-week/${academicYearId}/${semester}/${weekNumber}`);
    return response.data;
  } catch (error) {
    console.error('Error fetching weekly plans:', error);
    return [];
  }
};
```

### Get Weekly Plans by CST

```javascript
const getWeeklyPlansByCst = async (cstId) => {
  try {
    const response = await axios.get(`/api/weekly-plans/by-cst/${cstId}`);
    return response.data;
  } catch (error) {
    console.error('Error fetching weekly plans:', error);
    return [];
  }
};
```

### Find Period Activities for a Weekly Plan

```javascript
const getPeriodActivitiesByWeeklyPlan = async (weeklyPlanCode) => {
  try {
    const response = await axios.get(`/api/period-activities?lesson_code=${weeklyPlanCode}`);
    return response.data;
  } catch (error) {
    console.error('Error fetching period activities:', error);
    return [];
  }
};
```

## Best Practices

1. **Create weekly plans in advance**: At the beginning of each semester, create weekly plans for all subjects.

2. **Be consistent with codes**: Always use the system-generated codes for linking period activities to weekly plans.

3. **Add detailed notes**: Include specific information in the notes field to help with reporting and analysis.

4. **Review coverage regularly**: Use the reporting tools to identify gaps in curriculum coverage.

5. **Update plans as needed**: If the curriculum changes, update the weekly plans accordingly.
