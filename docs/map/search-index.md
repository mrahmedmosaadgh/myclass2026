# MyClass8 Documentation Search Index

## Overview
Comprehensive index of all documentation topics, keywords, and quick access links for the MyClass8 project.

## 🔍 Quick Search

### By Topic
- [Course Management](#course-management)
- [User Management](#user-management)
- [Offline System](#offline-system)
- [Qudrat Assessment](#qudrat-assessment)
- [API Documentation](#api-documentation)
- [Database Schema](#database-schema)
- [Migration Guides](#migration-guides)
- [Development Setup](#development-setup)
- [Security](#security)
- [Testing](#testing)

### By File Type
- [Controllers](#controllers)
- [Models](#models)
- [Views](#views)
- [Routes](#routes)
- [Migrations](#migrations)
- [Seeders](#seeders)
- [Configuration](#configuration)

## 📚 Topic Index

### Course Management
**Keywords**: course, lesson, level, section, teacher, import, excel, curriculum
**Files**:
- [Course Management Map](./course-management.md)
- [CourseImportController.php](../../app/Http/Controllers/CourseManagement/CourseImportController.php)
- [CourseLevelController.php](../../app/Http/Controllers/CourseManagement/CourseLevelController.php)
- [Course Management TODO](../CourseManagement/todo/1.md)
- [Course Management Done](../CourseManagement/done.md)

### User Management
**Keywords**: user, authentication, authorization, profile, role, permission
**Files**:
- [User Context API](../before_7_2025/api/user-context-endpoints.md)
- [User Management Guide](../before_7_2025/users-management.md)
- [useUserContext Composable](../before_7_2025/composables/useUserContext.md)
- [User Context Migration](../before_7_2025/migration/user-context-migration.md)

### Offline System
**Keywords**: offline, service worker, cache, sync, pwa, progressive web app
**Files**:
- [Offline System Map](./offline-system.md)
- [Offline README](../offline/README.md)
- [Offline Implementation](../offline/implementation.md)
- [Offline Examples](../offline/examples/LessonManager.vue)

### Qudrat Assessment
**Keywords**: qudrat, assessment, skills, quantitative, testing, evaluation
**Files**:
- [Qudrat System Map](./qudrat-system.md)
- [Skills Documentation](../qudrat/skills.md)

### API Documentation
**Keywords**: api, endpoint, rest, json, authentication, crud
**Files**:
- [User Context API](../before_7_2025/api/user-context-endpoints.md)
- [Curriculum API](../before_7_2025/curriculum/api-endpoints.md)
- [API Routes](../../routes/api.php)

### Database Schema
**Keywords**: database, migration, schema, table, relationship, model
**Files**:
- [Curriculum Database Schema](../before_7_2025/curriculum/database-schema.md)
- [Course Teacher Migration](../../database/migrations/2025_07_17_000001_create_course_teacher_table.php)
- [Course Management Seeder](../../database/seeders/CourseManagementSeeder.php)

### Migration Guides
**Keywords**: migration, upgrade, legacy, transition, compatibility
**Files**:
- [User Context Migration](../before_7_2025/migration/user-context-migration.md)
- [Legacy Documentation](./legacy-documentation.md)

### Development Setup
**Keywords**: setup, installation, configuration, environment, dependencies
**Files**:
- [Quick Reference Guide](./quick-reference.md)
- [Project Guidelines](../before_7_2025/PROJECT_GUIDELINES_AND_BEST_PRACTICES.md)
- [README.md](../../README.md)

### Security
**Keywords**: security, csrf, token, authentication, authorization, protection
**Files**:
- [CSRF Quick Reference](../before_7_2025/CSRF_Quick_Reference.md)
- [CSRF Token Fix](../before_7_2025/CSRF_Token_Fix_Documentation.md)

### Testing
**Keywords**: test, phpunit, vitest, unit test, integration test
**Files**:
- [phpunit.xml](../../phpunit.xml)
- [Quick Reference Testing](./quick-reference.md#testing)

## 🗂️ File Type Index

### Controllers
| File | Purpose | Location |
|------|---------|----------|
| CourseImportController.php | Excel import functionality | `app/Http/Controllers/CourseManagement/` |
| CourseLevelController.php | Level management | `app/Http/Controllers/CourseManagement/` |
| CourseController.php | Course CRUD operations | `app/Http/Controllers/CourseManagement/` |
| LessonController.php | Lesson management | `app/Http/Controllers/CourseManagement/` |

### Models
| Model | Purpose | Location |
|-------|---------|----------|
| Course | Course data model | `app/Models/CourseManagement/` |
| Level | Course level model | `app/Models/CourseManagement/` |
| Lesson | Lesson content model | `app/Models/CourseManagement/` |
| User | User authentication | `app/Models/` |

### Views
| Component | Purpose | Location |
|-----------|---------|----------|
| Course/Index.vue | Course listing page | `resources/js/Pages/CourseManagement/Course/` |
| Course/Create.vue | Course creation form | `resources/js/Pages/CourseManagement/Course/` |
| Level/Create.vue | Level creation form | `resources/js/Pages/CourseManagement/Level/` |
| Lesson/Create.vue | Lesson creation form | `resources/js/Pages/CourseManagement/Lesson/` |
| ImportExcel.vue | Excel import component | `resources/js/Components/Common/` |

### Routes
| File | Purpose | Key Routes |
|------|---------|------------|
| course_management.php | Course system routes | `/course-management/*` |
| web.php | Main web routes | `/`, `/login`, `/register` |
| api.php | API endpoints | `/api/*` |

### Migrations
| File | Purpose | Table |
|------|---------|-------|
| 2025_07_17_000001_create_course_teacher_table.php | Teacher assignments | `course_teacher` |
| Curriculum migrations | Course structure | `courses`, `levels`, `sections`, `lessons` |
| User context migrations | User management | `users`, `user_profiles` |

### Seeders
| File | Purpose | Data |
|------|---------|------|
| CourseManagementSeeder.php | Test course data | Sample courses, levels, lessons |
| DatabaseSeeder.php | Main seeder | Calls all other seeders |

### Configuration
| File | Purpose | Key Settings |
|------|---------|--------------|
| .env | Environment variables | Database, cache, queue settings |
| vite.config.js | Build configuration | Vue, Inertia, Tailwind setup |
| tailwind.config.js | Styling framework | Custom colors, components |
| phpunit.xml | Testing configuration | Test suites, coverage |

## 🔍 Search by Keywords

### A-C
- **API**: [User Context API](../before_7_2025/api/user-context-endpoints.md), [Curriculum API](../before_7_2025/curriculum/api-endpoints.md)
- **Assessment**: [Qudrat System](./qudrat-system.md), [Skills](../qudrat/skills.md)
- **Authentication**: [User Management](../before_7_2025/users-management.md), [CSRF](../before_7_2025/CSRF_Quick_Reference.md)
- **Authorization**: [User Context](../before_7_2025/composables/useUserContext.md)
- **Cache**: [Offline System](./offline-system.md), [Quick Reference](./quick-reference.md)
- **Configuration**: [Environment Setup](./quick-reference.md), [Project Guidelines](../before_7_2025/PROJECT_GUIDELINES_AND_BEST_PRACTICES.md)
- **Course**: [Course Management](./course-management.md), [CourseImportController.php](../../app/Http/Controllers/CourseManagement/CourseImportController.php)
- **CSRF**: [Security Guide](../before_7_2025/CSRF_Token_Fix_Documentation.md)

### D-L
- **Database**: [Schema](../before_7_2025/curriculum/database-schema.md), [Migrations](../../database/migrations/)
- **Development**: [Setup Guide](./quick-reference.md), [Directory Structure](./directory-structure.md)
- **Excel**: [Import Feature](./course-management.md), [ImportExcel.vue](../../resources/js/Components/Common/ImportExcel.vue)
- **Import**: [Course Import](./course-management.md), [Excel Import](./course-management.md)
- **Installation**: [Quick Start](./quick-reference.md), [Setup Guide](./quick-reference.md)
- **JavaScript**: [Vue Components](../../resources/js/Pages/), [Composables](../before_7_2025/composables/)
- **Laravel**: [Controllers](../../app/Http/Controllers/), [Models](../../app/Models/), [Routes](../../routes/)

### M-Q
- **Migration**: [User Context](../before_7_2025/migration/user-context-migration.md), [Database Schema](../before_7_2025/curriculum/database-schema.md)
- **Models**: [Course Models](../../app/Models/CourseManagement/), [User Models](../../app/Models/)
- **Offline**: [Offline System](./offline-system.md), [Service Worker](../offline/implementation.md)
- **PHP**: [Controllers](../../app/Http/Controllers/), [Models](../../app/Models/)
- **Progress**: [Course Management TODO](../CourseManagement/todo/1.md), [Course Management Done](../CourseManagement/done.md)
- **PWA**: [Offline System](./offline-system.md), [Implementation](../offline/IMPLEMENTATION_COMPLETE.md)
- **Qudrat**: [Assessment System](./qudrat-system.md), [Skills](../qudrat/skills.md)

### R-Z
- **Routes**: [Course Management](../../routes/course_management.php), [API](../../routes/api.php)
- **Security**: [CSRF Protection](../before_7_2025/CSRF_Token_Fix_Documentation.md), [User Management](../before_7_2025/users-management.md)
- **Service Worker**: [Offline System](./offline-system.md), [Implementation](../offline/implementation.md)
- **Setup**: [Quick Start](./quick-reference.md), [Development Setup](./quick-reference.md)
- **Testing**: [PHPUnit](../../phpunit.xml), [Vitest](./quick-reference.md)
- **User**: [User Context](../before_7_2025/api/user-context-endpoints.md), [User Management](../before_7_2025/users-management.md)
- **Vue**: [Components](../../resources/js/Pages/), [Composables](../before_7_2025/composables/)
- **Web**: [Routes](../../routes/web.php), [Views](../../resources/js/Pages/)

## 🎯 Quick Actions

### Most Common Tasks
1. **Create New Course**: 
   - Route: `/course-management/courses/create`
   - Controller: `CourseController@create`
   - View: `resources/js/Pages/CourseManagement/Course/Create.vue`

2. **Import Courses**:
   - Route: `/course-management/import`
   - Controller: `CourseImportController@index`
   - Component: `ImportExcel.vue`

3. **View Course Progress**:
   - Route: `/course-management/courses`
   - Controller: `CourseController@index`
   - View: `resources/js/Pages/CourseManagement/Course/Index.vue`

4. **Setup Development**:
   - Guide: [Quick Reference](./quick-reference.md)
   - Setup: [Development Setup](./quick-reference.md#development-setup)

### Emergency Fixes
- **Cache Issues**: `php artisan cache:clear`
- **Route Issues**: `php artisan route:clear`
- **View Issues**: `php artisan view:clear`
- **Config Issues**: `php artisan config:clear`

## 🔗 Cross-References

### Related Topics
- **Course Management ↔ API**: See [Course Management Map](./course-management.md) and [API Documentation](../before_7_2025/api/user-context-endpoints.md)
- **Offline ↔ Security**: See [Offline System](./offline-system.md) and [CSRF Documentation](../before_7_2025/CSRF_Token_Fix_Documentation.md)
- **User Management ↔ Database**: See [User Context](../before_7_2025/api/user-context-endpoints.md) and [Database Schema](../before_7_2025/curriculum/database-schema.md)
- **Migration ↔ Legacy**: See [Migration Guide](../before_7_2025/migration/user-context-migration.md) and [Legacy Documentation](./legacy-documentation.md)

---
*Use Ctrl+F (or Cmd+F) to search within this document for specific keywords*