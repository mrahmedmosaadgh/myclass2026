# MyClass8 Directory Structure Map

## Overview
Complete visual representation of the MyClass8 project directory structure, focusing on documentation and key components.

## 📁 Project Root Structure

```mermaid
graph TD
    A[MyClass8 Root] --> B[app/]
    A --> C[database/]
    A --> D[docs/]
    A --> E[resources/]
    A --> F[routes/]
    A --> G[public/]
    A --> H[config/]
    A --> I[tests/]
    A --> J[bootstrap/]
    A --> K[storage/]
    
    B --> B1[Http/Controllers/]
    B --> B2[Models/]
    B --> B3[Actions/]
    B --> B4[Providers/]
    
    C --> C1[migrations/]
    C --> C2[seeders/]
    C --> C3[factories/]
    
    D --> D1[map/]
    D --> D2[before_7_2025/]
    D --> D3[CourseManagement/]
    D --> D4[offline/]
    D --> D5[qudrat/]
    
    E --> E1[js/]
    E --> E2[css/]
    E --> E3[views/]
    
    F --> F1[web.php]
    F --> F2[course_management.php]
    F --> F3[api.php]
```

## 📚 Documentation Directory Deep Dive

### docs/ - Main Documentation
```mermaid
graph LR
    A[docs/] --> B[map/]
    A --> C[before_7_2025/]
    A --> D[CourseManagement/]
    A --> E[offline/]
    A --> F[qudrat/]
    
    B --> B1[README.md]
    B --> B2[course-management.md]
    B --> B3[legacy-documentation.md]
    B --> B4[offline-system.md]
    B --> B5[qudrat-system.md]
    B --> B6[directory-structure.md]
    B --> B7[quick-reference.md]
    B --> B8[search-index.md]
    
    C --> C1[documentation-portal.md]
    C --> C2[PROJECT_GUIDELINES.md]
    C --> C3[api/]
    C --> C4[curriculum/]
    C --> C5[migration/]
    
    D --> D1[done.md]
    D --> D2[todo/]
    
    E --> E1[README.md]
    E --> E2[implementation.md]
    E --> E3[examples/]
    
    F --> F1[skills.md]
```

### before_7_2025/ - Legacy Documentation
```mermaid
graph TD
    A[before_7_2025/] --> B[Core Docs]
    A --> C[API Docs]
    A --> D[Curriculum Docs]
    A --> E[Migration Docs]
    
    B --> B1[documentation-portal.md]
    B --> B2[PROJECT_GUIDELINES.md]
    B --> B3[CSRF_Quick_Reference.md]
    B --> B4[network-status-indicator.md]
    
    C --> C1[user-context-endpoints.md]
    
    D --> D1[api-endpoints.md]
    D --> D2[business-rules.md]
    D --> D3[database-schema.md]
    D --> D4[curriculum-management.md]
    
    E --> E1[user-context-migration.md]
```

## 🏗️ Key Application Directories

### app/Http/Controllers/
```mermaid
graph TD
    A[Controllers/] --> B[CourseManagement/]
    A --> C[Acadimy/]
    A --> D[Admin/]
    A --> E[AI/]
    A --> F[Api/]
    A --> G[Student/]
    A --> H[Teacher/]
    
    B --> B1[CourseImportController.php]
    B --> B2[CourseLevelController.php]
    B --> B3[CourseController.php]
    B --> B4[LessonController.php]
```

### resources/js/Pages/
```mermaid
graph TD
    A[Pages/] --> B[CourseManagement/]
    A --> C[CourseManagement2/]
    A --> D[Dashboard/]
    A --> E[Auth/]
    A --> F[API/]
    
    B --> B1[Course/]
    B --> B2[Level/]
    B --> B3[Lesson/]
    B --> B4[Import/]
    B --> B5[Teacher/]
    
    B1 --> B11[Index.vue]
    B1 --> B12[Create.vue]
    B1 --> B13[components/]
    
    B2 --> B21[Create.vue]
    B2 --> B22[Show.vue]
    
    B3 --> B31[Create.vue]
    B3 --> B32[Edit.vue]
    B3 --> B33[Show.vue]
```

## 📊 File Statistics

### Documentation Files
| Directory | Files | Purpose | Status |
|-----------|--------|---------|---------|
| `docs/map/` | 8 | Project navigation maps | 🟢 Active |
| `docs/before_7_2025/` | 12+ | Legacy documentation | 🟡 Historical |
| `docs/CourseManagement/` | 3 | Progress tracking | 🟢 Active |
| `docs/offline/` | 6+ | Offline system docs | 🟢 Active |
| `docs/qudrat/` | 1 | Assessment system | 🟢 Active |

### Key Configuration Files
```mermaid
graph LR
    A[Config Files] --> B[composer.json]
    A --> C[package.json]
    A --> D[vite.config.js]
    A --> E[tailwind.config.js]
    A --> F[phpunit.xml]
    A --> G[firebase-rules.json]
    
    B --> H[PHP Dependencies]
    C --> I[JS Dependencies]
    D --> J[Build Configuration]
    E --> K[Styling Framework]
    F --> L[Testing Setup]
    G --> M[Firebase Rules]
```

## 🔍 Quick Navigation Paths

### Development Entry Points
1. **Start Here**: `docs/map/README.md`
2. **Course Features**: `docs/map/course-management.md`
3. **Legacy Context**: `docs/map/legacy-documentation.md`
4. **Offline Features**: `docs/map/offline-system.md`
5. **Assessment System**: `docs/map/qudrat-system.md`

### Code Navigation
1. **Controllers**: `app/Http/Controllers/CourseManagement/`
2. **Models**: `app/Models/CourseManagement/`
3. **Views**: `resources/js/Pages/CourseManagement/`
4. **Routes**: `routes/course_management.php`
5. **Migrations**: `database/migrations/`

### Configuration Files
- **Environment**: `.env`
- **Database**: `config/database.php`
- **Cache**: `config/cache.php`
- **Queue**: `config/queue.php`
- **Services**: `config/services.php`

## 🚨 Important Notes

### Directory Conventions
- **PascalCase**: Used for Vue components
- **kebab-case**: Used for file names
- **camelCase**: Used for JavaScript variables
- **snake_case**: Used for PHP variables and database columns

### File Organization
- **Controllers**: Grouped by feature domain
- **Models**: Organized by system modules
- **Views**: Mirror the route structure
- **Assets**: Compiled through Vite

### Documentation Maintenance
- **Active docs**: Update with each feature
- **Legacy docs**: Preserve for reference
- **Map files**: Auto-generated and maintained
- **Examples**: Kept in sync with current implementation

## 🔗 Related Maps
- [Main Project Map](./README.md)
- [Course Management Map](./course-management.md)
- [Legacy Documentation](./legacy-documentation.md)
- [Offline System Map](./offline-system.md)
- [Qudrat System Map](./qudrat-system.md)

---
*Last Updated: July 17, 2025*