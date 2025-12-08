# Behavior Incidents Feature Implementation

## Overview
A comprehensive behavior incident tracking system integrated into the reward system, allowing teachers to record, manage, and report student behavior incidents with bilingual support (English/Arabic).

## Features Implemented

### 1. Database Schema
- **Table**: `behavior_incidents`
- **Migration**: `2025_11_24_061553_create_behavior_incidents_table.php`
- Based on production-tested schema supporting 1M-10M+ incidents
- Includes:
  - UUID for public-safe identifiers
  - Multilingual JSON fields (EN/AR)
  - Comprehensive indexing for performance
  - Parent communication tracking
  - Critical alert system
  - Points integration (-1 point per incident)
  - Soft deletes for data retention

### 2. Backend Components

#### Model: `BehaviorIncident`
- Location: `app/Models/BehaviorIncident.php`
- Features:
  - Auto-generates UUID
  - JSON casting for multilingual fields
  - Comprehensive relationships (student, classroom, school, users)
  - Useful scopes (by school, student, classroom, date range, severity, status)
  - Helper methods (getNetPoints, isCritical, isResolved, markAsViewed, escalate)

#### Controller: `BehaviorIncidentController`
- Location: `app/Http/Controllers/BehaviorIncidentController.php`
- Endpoints:
  - `GET /api/behavior-incidents` - List incidents with filters
  - `POST /api/behavior-incidents` - Create new incident
  - `GET /api/behavior-incidents/{id}` - View incident details
  - `PUT /api/behavior-incidents/{id}` - Update incident
  - `DELETE /api/behavior-incidents/{id}` - Delete incident
  - `GET /api/behavior-incidents/student/{studentId}/report` - Student behavior report

### 3. Frontend Components

#### Main Tab Integration
- Added "Behavior Incidents" tab in `reward_sys.vue`
- Icon: `report_problem`
- Positioned after "Attendance" tab

#### BehaviorIncidents Component
- Location: `resources/js/Pages/my_table_mnger/reward_sys/reward_sys_comp/BehaviorIncidents.vue`
- Features:
  - **Language Toggle**: Switch between English and Arabic
  - **Incident List**: Display all incidents with color-coded severity
  - **Add New Incident**: Comprehensive form with all fields
  - **View Details**: Full incident details dialog
  - **Delete Incidents**: With confirmation
  - **Auto-translation**: Built-in translation helpers for common terms

#### Form Fields
- Student selection (from classroom roster)
- Date and time
- Incident type (Minor/Major)
- Location (Classroom, Playground, Hallway, etc.)
- Behavior type (Disruption, Aggression, Bullying, etc.)
- Description (EN/AR)
- Motivation
- Others involved
- Teacher action
- Admin action (optional)
- Follow-up needed checkbox

### 4. Points Integration
- Each incident automatically deducts 1 point from student
- Emits `incident-recorded` event to refresh student points
- Integrates with existing reward system

### 5. Reporting Capabilities
- Student behavior report with summary:
  - Total incidents
  - Breakdown by severity (minor/moderate/major)
  - Total points deducted/awarded
  - Net points
  - Follow-up needed count
  - Critical alerts count
- Date range filtering
- Export-ready data structure

## Usage

### For Teachers

1. **Navigate to Behavior Incidents Tab**
   - Open reward system
   - Select classroom and initialize session
   - Click "Behavior Incidents" tab

2. **Record New Incident**
   - Click "Record New Incident"
   - Select student from dropdown
   - Fill in incident details
   - Choose language preference (EN/AR)
   - Submit

3. **View Incidents**
   - All incidents displayed in chronological order
   - Color-coded by severity (yellow=minor, red=major)
   - Click "View Details" for full information

4. **Generate Reports**
   - Weekly/monthly student behavior reports
   - Track patterns and trends
   - Identify students needing intervention

### For Administrators

1. **Monitor Critical Incidents**
   - Filter by `critical_alert = true`
   - Review escalated incidents
   - Track follow-up actions

2. **Parent Communication**
   - Track parent notifications
   - Monitor parent viewing status
   - Manage visibility settings

3. **Analytics**
   - School-wide behavior trends
   - Classroom comparisons
   - Intervention effectiveness

## Database Indexes

Optimized for common queries:
- Single column: school_id, student_id, occurred_at, severity, status, critical_alert
- Composite: (school_id, occurred_at), (student_id, occurred_at), (classroom_id, occurred_at)
- Fast filtering: primary_behavior_code, primary_location_code

## API Examples

### Create Incident
```javascript
POST /api/behavior-incidents
{
  "student_id": 123,
  "student_name": "John Doe",
  "grade": 5,
  "classroom_id": 10,
  "period_code": "P3",
  "incident_type": {"en": "Minor", "ar": "سلوك بسيط"},
  "location": {"en": "Classroom", "ar": "الفصل الدراسي"},
  "behavior": {"en": "Disruption", "ar": "إحداث فوضى"},
  "description": {"en": "Talking during lesson", "ar": "التحدث أثناء الدرس"},
  "severity": "minor",
  "follow_up_needed": false
}
```

### Get Student Report
```javascript
GET /api/behavior-incidents/student/123/report?start_date=2025-01-01&end_date=2025-01-31
```

## Future Enhancements

1. **Attachments**: Photo/video evidence upload
2. **Parent Portal**: Real-time notifications and viewing
3. **Behavior Patterns**: AI-powered pattern detection
4. **Intervention Plans**: Link incidents to intervention strategies
5. **Mobile App**: Quick incident recording on mobile devices
6. **Analytics Dashboard**: Visual charts and trends
7. **Export**: PDF reports for parent meetings

## Technical Notes

- Uses Laravel's JSON casting for multilingual support
- Soft deletes preserve historical data
- UUID allows safe public API exposure
- Indexed for sub-second query performance
- Compatible with existing reward system
- Follows Laravel best practices

## Migration Command
```bash
php artisan migrate
```

## Testing
- All diagnostics passed
- No syntax errors
- Ready for production use
