# Curriculum Management System

## Overview

The Curriculum Management System is a comprehensive solution for managing educational curricula across multiple schools, subjects, and grades. It provides a centralized platform for creating, organizing, and maintaining curriculum data with intelligent activation logic to ensure data integrity.

## Key Features

### 🎯 **Core Functionality**
- **Multi-School Support**: Manage curricula across multiple schools with proper data isolation
- **Subject-Grade Organization**: Organize curricula by subject and grade level
- **Smart Activation Logic**: Automatic deactivation of conflicting curricula
- **Comprehensive CRUD Operations**: Create, read, update, and delete curricula
- **Advanced Filtering**: Filter by school, subject, grade, and status

### 🔒 **Business Rules**
- **One Active Curriculum Rule**: Only one curriculum can be active per school+subject+grade combination
- **Automatic Deactivation**: When activating a curriculum, others for the same school+subject+grade are automatically deactivated
- **Data Integrity**: Proper foreign key constraints ensure referential integrity
- **Soft Deletes**: Curricula are soft-deleted to maintain historical records

### 🎨 **User Interface**
- **Quasar-Based UI**: Modern, responsive interface using Quasar framework
- **Multi-Step Workflow**: Intuitive school → subject → grade → curriculum flow
- **Real-Time Feedback**: Loading states, notifications, and confirmations
- **Advanced Table**: Sortable, filterable data table with pagination

## System Architecture

### Database Schema

```
curricula
├── id (Primary Key)
├── name (Curriculum Name)
├── description (Optional Description)
├── school_id (Foreign Key → schools.id)
├── subject_id (Foreign Key → subjects.id)
├── grade_id (Foreign Key → grades.id)
├── active (tinyInteger: 0=inactive, 1=active)
├── created_at
├── updated_at
└── deleted_at (Soft Delete)

Related Tables:
├── curriculum_lessons (Detailed lesson content)
├── curriculum_lesson_plans (Teacher-specific plans)
├── curriculum_maps (Academic year mapping)
└── question_banks (Assessment questions)
```

### Model Relationships

```php
Curriculum Model:
├── belongsTo: School, Subject, Grade
├── hasMany: CurriculumLessons, CurriculumLessonPlans
├── hasMany: CurriculumMaps, QuestionBanks
└── scopes: active(), forSchool(), forSubject(), forGrade()
```

## User Guide

### Accessing the System

1. **Navigation**: Admin Menu → Academic Management → Curriculum → Curriculum Management
2. **URL**: `/admin/curriculum/management`
3. **Permissions**: Requires admin role

### Creating a New Curriculum

1. **Click "Add Curriculum"** button in the top-right corner
2. **Select School**: Choose from your assigned schools
3. **Select Subject**: Choose from available subjects for the selected school
4. **Select Grade**: Choose from available grades for the selected school
5. **Enter Details**:
   - Curriculum Name (required)
   - Description (optional)
   - Active Status (checkbox)
6. **Save**: Click "Save" to create the curriculum

### Managing Existing Curricula

#### Filtering Curricula
- **School Filter**: Filter by specific school
- **Subject Filter**: Filter by subject (requires school selection)
- **Grade Filter**: Filter by grade (requires school selection)
- **Status Filter**: Filter by active/inactive status

#### Editing Curricula
1. Click the **Edit** button (pencil icon) in the Actions column
2. Modify the curriculum details
3. Click **Save** to apply changes

#### Activating/Deactivating Curricula
1. Click the **Activate/Deactivate** button (play/pause icon)
2. Confirm the action in the dialog
3. **Note**: Activating a curriculum will automatically deactivate others for the same school+subject+grade

#### Deleting Curricula
1. Click the **Delete** button (trash icon)
2. Confirm deletion in the dialog
3. **Warning**: This action cannot be undone

## Technical Implementation

### Frontend (Vue.js + Quasar)

**File**: `resources/js/Pages/my_class/admin/Curriculum/CurriculumManagement.vue`

**Key Components**:
- Reactive data management with Vue 3 Composition API
- Quasar UI components for modern interface
- Axios for API communication
- Real-time loading states and notifications

**Features**:
- Responsive design for all screen sizes
- Form validation and error handling
- Confirmation dialogs for destructive actions
- Pagination and sorting for large datasets

### Backend (Laravel)

**Controller**: `app/Http/Controllers/Curriculum/CurriculumController.php`

**Key Methods**:
- `getUserSchools()`: Get schools accessible to current user
- `getSchoolSubjects()`: Get subjects for a specific school
- `getSchoolGrades()`: Get grades for a specific school
- `getCurricula()`: Get curricula with filtering and pagination
- `store()`: Create new curriculum
- `update()`: Update existing curriculum
- `activate()/deactivate()`: Manage curriculum status
- `destroy()`: Delete curriculum

### API Endpoints

**Base URL**: `/api/curriculum/`

```
GET    /user-schools                    # Get user's schools
GET    /school/{id}/subjects           # Get school subjects
GET    /school/{id}/grades             # Get school grades
GET    /curricula                      # Get curricula (with filters)
POST   /curricula                      # Create curriculum
PUT    /curricula/{id}                 # Update curriculum
POST   /curricula/{id}/activate        # Activate curriculum
POST   /curricula/{id}/deactivate      # Deactivate curriculum
DELETE /curricula/{id}                 # Delete curriculum
```

## Business Logic

### Activation Logic

When a curriculum is activated:

1. **Find Conflicts**: Identify other active curricula for the same school+subject+grade
2. **Deactivate Others**: Set conflicting curricula to `active = 0`
3. **Activate Target**: Set the target curriculum to `active = 1`
4. **Transaction Safety**: All operations wrapped in database transaction

```php
public function activate()
{
    // Deactivate conflicting curricula
    static::where('school_id', $this->school_id)
          ->where('subject_id', $this->subject_id)
          ->where('grade_id', $this->grade_id)
          ->where('id', '!=', $this->id)
          ->update(['active' => 0]);

    // Activate this curriculum
    $this->update(['active' => 1]);
}
```

### Data Validation

**Required Fields**:
- School ID (must exist in schools table)
- Subject ID (must exist in subjects table)
- Grade ID (must exist in grades table)
- Curriculum Name (string, max 255 characters)

**Optional Fields**:
- Description (text)
- Active Status (boolean, defaults to false)

## Security Considerations

### Authentication & Authorization
- **Authentication**: Requires valid user session
- **Authorization**: Admin role required for access
- **School Access**: Users can only access their assigned schools

### Data Protection
- **SQL Injection**: Protected by Laravel's query builder and Eloquent ORM
- **CSRF Protection**: All forms protected by CSRF tokens
- **Input Validation**: Server-side validation for all inputs
- **Soft Deletes**: Data preserved for audit trails

## Performance Optimization

### Database Optimization
- **Indexes**: Strategic indexes on frequently queried columns
- **Foreign Keys**: Proper constraints for data integrity
- **Pagination**: Large datasets paginated for performance

### Frontend Optimization
- **Lazy Loading**: Subjects and grades loaded only when needed
- **Debounced Requests**: Prevent excessive API calls
- **Loading States**: Clear feedback during operations

## Troubleshooting

### Common Issues

**Issue**: "Failed to load schools"
- **Cause**: User not assigned to any schools
- **Solution**: Assign user to schools in user management

**Issue**: "No subjects available"
- **Cause**: Selected school has no active subjects
- **Solution**: Add subjects to the school or activate existing ones

**Issue**: "Activation failed"
- **Cause**: Database constraint violation
- **Solution**: Check foreign key relationships and data integrity

### Error Messages

- **Validation Errors**: Clear field-specific error messages
- **Network Errors**: User-friendly network failure messages
- **Permission Errors**: Appropriate access denied messages

## Future Enhancements

### Planned Features
- **Curriculum Templates**: Reusable curriculum templates
- **Import/Export**: Bulk curriculum import/export functionality
- **Version Control**: Track curriculum changes over time
- **Approval Workflow**: Multi-step approval process for curriculum changes
- **Analytics**: Usage statistics and reporting

### Integration Opportunities
- **Lesson Planning**: Direct integration with lesson plan creation
- **Assessment Tools**: Link to question banks and assessments
- **Calendar Integration**: Sync with academic calendar
- **Reporting System**: Comprehensive curriculum reports

## Support

For technical support or feature requests, please contact the development team or create an issue in the project repository.

---

*Last Updated: [Current Date]*
*Version: 1.0*
