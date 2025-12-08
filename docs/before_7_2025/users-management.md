# 👥 Users & Roles Management System

A comprehensive user management system with role-based access control and advanced search capabilities.

## 🎯 Overview

The Users & Roles Management system provides:
- **Tabbed Interface** - Separate tabs for Teachers, Students, Parents, and Others
- **Advanced Search** - Search by name, email, subject, classroom, or any related data
- **School Filtering** - Filter users by specific schools
- **Role Management** - Assign and manage user roles efficiently
- **Beautiful UI** - Modern Quasar-based interface with responsive design

## ✨ Features

### 🔍 **Advanced Search Capabilities**
- **Name Search** - Find users by any part of their name
- **Email Search** - Search by email addresses
- **Subject Search** - Find teachers by their subjects
- **Classroom Search** - Find students by their classrooms
- **School Filtering** - Filter by specific schools
- **Role Filtering** - Filter by assigned roles

### 📊 **User Categories**
1. **Teachers** - With subjects and classrooms
2. **Students** - With classroom, grade, and stage information
3. **Parents** - With children's classroom information
4. **Others** - General users without specific roles

### 🎨 **User Interface**
- **Statistics Cards** - Overview of user counts
- **Tabbed Navigation** - Easy switching between user types
- **Card Layout** - Beautiful user cards with relevant information
- **Responsive Design** - Works on all devices
- **Dark Mode Support** - Consistent theming

## 🚀 Usage

### Accessing the System
1. Login as admin or superadmin
2. Navigate to **Admin** → **User Management**
3. Click on **Users & Roles** tab

### Searching Users
1. Use the search box to find users by:
   - Name (partial matches)
   - Email addresses
   - Subjects (for teachers)
   - Classrooms (for students)
   - Any related information

2. Use filters to narrow results:
   - **School Filter** - Select specific school
   - **Role Filter** - Filter by assigned roles

### Managing Roles
1. Click **Manage Roles** on any user card
2. Select/deselect roles as needed
3. Save changes

### User Details
- Click **View Details** to see comprehensive user information
- View all related data (subjects, classrooms, children, etc.)
- See current role assignments

## 🛠️ Technical Implementation

### Backend Components
- Enhanced `PermissionsController` with `getEnhancedUserData()` method
- Relationships loading for Teachers, Students, Parents
- School and classroom data integration
- Advanced filtering and search logic

### Frontend Components
- `UsersRoles.vue` - Main component with tabs and search
- `UsersList.vue` - User cards display component
- `UserDetailsDialog.vue` - Detailed user information modal
- Quasar UI components for modern interface

### Data Structure
```javascript
{
  schools: [...],
  teachers: [
    {
      id, name, email, roles,
      school_name, subjects, classrooms,
      teacher_data, user_data
    }
  ],
  students: [
    {
      id, name, email, roles,
      school_name, classroom_name,
      grade_name, stage_name,
      student_data, user_data
    }
  ],
  parents: [
    {
      id, name, email, roles,
      school_name, children_classrooms,
      children_count, parent_data, user_data
    }
  ],
  others: [...],
  stats: { total, teachers, students, parents, others }
}
```

## 📱 Responsive Features

- **Mobile-friendly** card layouts
- **Touch-optimized** interactions
- **Adaptive** search and filter controls
- **Collapsible** information sections

## 🎨 Visual Features

- **Color-coded** user types (Teachers: Green, Students: Blue, Parents: Orange)
- **Icon-based** navigation and identification
- **Hover effects** and smooth animations
- **Status badges** for roles and information
- **Gradient backgrounds** and modern styling

## 🔐 Security

- **Role-based access** control
- **Admin/Superadmin** only access
- **Secure data** loading with relationships
- **Permission validation** for role management

---

**Created**: {{ date('Y-m-d') }}  
**Version**: 1.0.0  
**Author**: Users Management System
