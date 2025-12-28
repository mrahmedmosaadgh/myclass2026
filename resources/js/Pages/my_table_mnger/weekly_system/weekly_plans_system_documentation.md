# Weekly Plans System - Complete Documentation

## 🎯 System Overview

The Weekly Plans system is a comprehensive teacher planning tool that allows educators to create, manage, and customize weekly lesson plans for their assigned subjects and classes. It provides flexibility while maintaining stability even when the underlying school schedule changes.

### Key Characteristics
- **Modular Design**: All code organized under `/weeklyplansystem` namespace
- **Schedule Independence**: Uses soft references (`period_code`) instead of hard foreign keys
- **Flexibility**: Fully editable weekly plans that persist regardless of schedule changes
- **Teacher-Centric**: Each teacher manages their own weekly plans for their assigned classes
- **Semester-Based**: Supports planning for 1-36 weeks per semester

## 🏗️ Core Architecture

### Database Structure

#### `weekly_plans` Table
**Purpose**: Container for weekly plans (one per subject/class/week combination)

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint | Primary key |
| `academic_year_id` | bigint | Foreign key to `academic_years` |
| `semester_number` | integer | Semester (1 or 2) |
| `week_number` | integer | Week number (1-18 or 1-36) |
| `cst_id` | bigint | Foreign key to `classroom_subject_teachers` |
| `cw` | text | Classwork content |
| `hw` | text | Homework content |
| `notes` | text | Teacher notes |
| `created_at` | timestamp | Creation timestamp |
| `updated_at` | timestamp | Last update timestamp |

#### `weekly_plan_sessions` Table
**Purpose**: Individual editable sessions within a weekly plan

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint | Primary key |
| `weekly_plan_id` | bigint | Foreign key to `weekly_plans` |
| `session_index` | integer | Order within week (1, 2, 3...) |
| `period_code` | string | Soft reference: `year.semester.week.day` |
| `type` | enum | Session type: `lesson`, `quiz`, `exam`, `extra`, `note` |
| `title` | string | Session title |
| `data` | json | Flexible storage for materials, links, homework, tags |
| `created_at` | timestamp | Creation timestamp |
| `updated_at` | timestamp | Last update timestamp |

### Key Relationships
- **Academic Years** → Weekly Plans (one-to-many)
- **Classroom Subject Teachers (CST)** → Weekly Plans (one-to-many)  
- **Weekly Plans** → Weekly Plan Sessions (one-to-many)

## 🔄 Smart Design Features

### Schedule Independence Strategy
- Uses **soft references** (`period_code` format: `year.semester.week.day`) instead of hard foreign keys
- Weekly plans remain intact when school schedules change
- Only the period_code mapping needs updating if schedules change

### Flexible Session Types
- **Lesson** - Regular teaching sessions
- **Quiz** - Assessment sessions  
- **Exam** - Formal examinations
- **Extra** - Additional sessions
- **Note** - Notes or reminders

### JSON Data Field Structure
The `data` field in `weekly_plan_sessions` supports flexible content:
```json
{
    "zoom_link": "https://zoom.us/j/123456789",
    "materials": ["worksheet.pdf", "presentation.pptx"],
    "homework": "Complete exercises 1-5",
    "tags": ["algebra", "geometry"],
    "custom_fields": {
        "difficulty": "medium",
        "duration": "45 minutes"
    }
}
```

## 👨‍🏫 Teacher Workflow

### Main Interface (`MyWeeklyPlans.vue`)

#### Navigation Controls
- **Week Selector**: Teachers can select specific weeks (1-18 or 1-36)
- **Semester Selection**: Switch between semester 1 and 2
- **Progress Tracking**: Visual progress bar showing completion percentage

#### Grid Display
- **Day Columns**: Organized by day (Sunday-Thursday)
- **Plan Cards**: Each card represents a class session
- **Status Indicators**: Color-coded borders (green=completed, yellow=partial, red=empty)
- **Copy/Paste**: Copy CW/HW/Notes between different class sessions

#### Filtering Options
- **Classroom Filter**: Filter by specific classrooms
- **Day Filter**: Filter by specific days of the week

### Session Management Features
- **Drag-and-drop** reordering of sessions
- **Bulk updates** for multiple sessions
- **Modal editing** for detailed session management
- **Real-time status updates** based on CW/HW content

## 🔧 Technical Implementation

### Backend (Laravel)

#### Models
- **WeeklyPlan Model**: Manages weekly plan containers with relationships to academic years and CST
- **WeeklyPlanSession Model**: Handles individual sessions with flexible data storage

#### Controllers
- **WeeklyPlanController**: CRUD operations for weekly plans with teacher authorization
- **WeeklyPlanSessionController**: Session management including reordering and bulk updates

#### API Endpoints
```
/api/weeklyplansystem/headers           # Weekly plan CRUD
/api/weeklyplansystem/sessions          # Session management
/api/weeklyplansystem/sessions/reorder  # Drag-and-drop reordering
/api/weeklyplansystem/sessions/bulk-update  # Bulk operations
```

### Frontend (Vue.js + Quasar)

#### Key Components
- **MyWeeklyPlans.vue**: Main teacher interface for viewing and managing weekly plans
- **WeeklyPlanEditor.vue**: Modal component for editing individual plans
- **WeekSelector.vue**: Component for week navigation
- **SessionCard.vue**: Individual session display with drag-and-drop support

#### Features
- **Responsive Design**: Grid layout adapts to different screen sizes
- **Real-time Updates**: Automatic status calculation based on content
- **User Feedback**: Toast notifications for copy/paste operations
- **Error Handling**: Comprehensive error handling with user-friendly messages

## 📊 Key Features

### 1. Teacher-Centric Design
- Each teacher manages only their assigned classes
- Authorization ensures teachers can only access their own plans
- Personalized progress tracking per teacher

### 2. Schedule Resilience
- Plans persist regardless of schedule changes
- Soft references maintain data integrity
- Easy migration when schedules are updated

### 3. Flexible Content Management
- JSON data fields allow custom content storage
- Support for various session types and custom fields
- Extensible architecture for future enhancements

### 4. Visual User Experience
- Color-coded status indicators
- Progress bars and completion tracking
- Intuitive drag-and-drop interface
- Mobile-responsive design

## 🔗 Integration Points

### Existing System Integration
- Integrates with `academic_years` table for academic year management
- Connects to `classroom_subject_teachers` for teacher-class assignments
- Uses Laravel authentication and authorization system
- Compatible with existing school management structure

### Data Flow
1. Teacher logs in and accesses "My Weekly Plans"
2. System loads teacher's assigned classes from CST table
3. Weekly plans are displayed in organized grid layout
4. Teacher can edit, copy, paste, and manage sessions
5. Changes are saved via API and reflected in real-time

## 📈 Implementation Status

### ✅ Completed Features
- Database foundation with proper relationships
- Backend API with full CRUD operations
- Frontend components with responsive design
- Authorization and validation systems
- Session management with drag-and-drop
- Copy/paste functionality between sessions
- Progress tracking and status indicators

### 🔄 Current Workflow
1. **Plan Generation**: Admin generates weekly plans for teachers
2. **Teacher Access**: Teachers access their assigned plans
3. **Content Management**: Teachers fill in CW, HW, and notes
4. **Session Customization**: Teachers can reorder and customize sessions
5. **Progress Tracking**: System tracks completion status automatically

## 🚀 Usage Examples

### Basic Usage
1. Navigate to "My Weekly Plans" from teacher dashboard
2. Select week and semester using controls
3. View weekly plan grid organized by day
4. Click on any plan card to edit content
5. Use copy/paste to duplicate content between sessions

### Advanced Features
- Use drag-and-drop to reorder sessions within a week
- Apply filters to focus on specific classrooms or days
- Monitor progress using the completion percentage indicator
- Utilize JSON data field for custom session content

## 📚 Related Files

### Backend Files
- `app/Models/WeeklyPlan.php` - Weekly plan model
- `app/Models/WeeklyPlanSession.php` - Session model
- `app/Http/Controllers/WeeklyPlanController.php` - Plan controller
- `app/Http/Controllers/WeeklyPlanSessionController.php` - Session controller
- `routes/weekly_plans.php` - API routes

### Frontend Files
- `resources/js/Pages/my_table_mnger/weekly_system/teacher/MyWeeklyPlans.vue` - Main interface
- `resources/js/Pages/my_table_mnger/weekly_system/components/weekly-plans/WeeklyPlanEditor.vue` - Editor modal
- `resources/js/Pages/my_table_mnger/weekly_system/components/weekly-plans/WeekSelector.vue` - Week navigation

### Documentation
- `docs/weeklyplansystem/ideas.md` - Design concepts
- `docs/weeklyplansystem/done.md` - Implementation status
- `help_ok/doc/weekly_plans/weekly_plans_usage_guide.md` - Usage guide

---

*This documentation provides a comprehensive overview of the Weekly Plans system as implemented in the MyClass2026 application.*