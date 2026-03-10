# Course Management Interface

A Vue 3 + Quasar component for displaying course structure with a two-panel layout featuring a collapsible tree view and lesson display.

## Overview

This interface provides teachers with a comprehensive view of their assigned courses, organized in a hierarchical structure:
- **Courses** → **Levels** → **Sections** → **Lessons**

## Features

### 🎯 Core Functionality
- **Two-panel layout** using Quasar QSplitter
- **Collapsible tree view** for course navigation
- **Real-time search** across all course content
- **Responsive design** that works on all screen sizes
- **Interactive lesson selection** with visual feedback

### 🔍 Search Capabilities
- Search across course names, level titles, section titles, and lesson content
- Real-time filtering of the tree structure
- Visual indicators showing filtered results
- Clear search functionality

### 📱 User Experience
- **Loading states** with spinners
- **Error handling** with retry functionality
- **Empty states** with helpful messages
- **Smooth animations** and transitions
- **Keyboard navigation** support

## Components

### CoursePreview.vue
The main component that provides the complete interface:
- Fetches data from the API
- Manages state and search functionality
- Handles user interactions

### CourseManagementInterface.vue
A standalone demo component with sample data:
- Perfect for testing and development
- Includes sample course structure
- No API dependencies

### SingleCoursePreview.vue
Enhanced single course preview component:
- Integrates with CourseManagementInterface
- Supports both API data and prop-based data
- Provides search and filtering capabilities
- Includes lesson numbering and progress tracking

### SingleCourseDemo.vue
Demo page showcasing the SingleCoursePreview:
- Lists available courses
- Allows selection and preview of course structure
- Includes fallback sample data for testing

## API Endpoints

### Get All Courses with Structure
```
GET /course-management/api/courses/with-structure
```

### Get Single Course Structure
```
GET /course-management/api/courses/{course}/structure
```

### Get Teacher's Courses
```
GET /course-management/api/teacher/courses
```

## Usage Examples

### Basic Usage
```vue
<template>
  <CourseManagementInterface />
</template>

<script setup>
import { CourseManagementInterface } from './preview_course'
</script>
```

### With API Data
```vue
<template>
  <SingleCoursePreview 
    :course-id="selectedCourseId"
    :course-data="courseData"
  />
</template>

<script setup>
import { SingleCoursePreview } from './preview_course'
import { ref } from 'vue'

const selectedCourseId = ref(1)
const courseData = ref({/* course data */})
</script>
```

## Styling

All components use Quasar's built-in styling utilities:
- **Colors**: Primary, secondary, accent colors
- **Spacing**: Consistent padding and margins
- **Typography**: Responsive text sizing
- **Icons**: Material Design icons from @quasar/extras

## Available Components

1. **CoursePreview.vue** - Main course preview component
2. **CourseCardInterface.vue** - Card-based interface for courses
3. **Access.vue** - Access control and permissions
4. **Demo.vue** - Demo page with sample data
5. **CourseManagementInterface.vue** - New Vue 3 + Quasar interface
6. **SingleCoursePreview.vue** - Single course preview with tree view
7. **SingleCourseDemo.vue** - Demo page for single course preview

## New Components Added

### CourseManagementInterface.vue
A Vue 3 + Quasar component that provides a two-panel layout for course management:
- **Left Panel**: Tree view of courses → levels → sections using q-tree
- **Right Panel**: Displays selected section's lessons with interactive cards
- Features: QSplitter layout, collapsible tree, lesson selection, responsive design

### SingleCoursePreview.vue
Enhanced single course preview component that:
- Integrates with the new CourseManagementInterface
- Supports both API data and prop-based data
- Provides search and filtering capabilities
- Includes lesson numbering and progress tracking

### SingleCourseDemo.vue
Demo page showcasing the new SingleCoursePreview component:
- Lists available courses
- Allows selection and preview of course structure
- Includes fallback sample data for testing

## Installation

All components are ready to use with Laravel + Inertia.js + Quasar setup. No additional installation required.

## Development

To test the components:
1. Visit `/course-management/teachers/preview-course` for the main demo
2. Use the SingleCourseDemo component for isolated testing
3. Check the CourseManagementInterface for the new Vue 3 + Quasar interface

## Browser Support

- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+