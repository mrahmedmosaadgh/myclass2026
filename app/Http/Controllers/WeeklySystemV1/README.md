# Weekly System V1 - Backend Controllers

This folder contains controllers for the Feature-First Weekly System architecture.

## Structure

```
WeeklySystemV1/
├── WeeklySystemController.php    # Main controller (diverging responses)
├── CurriculumController.php       # Curriculum CRUD operations
├── CurriculumLessonController.php # Lesson planning within curriculum
├── WeeklyPlanController.php       # Weekly plan management
├── TimetableController.php        # Timetable editor
├── ScheduleCopyController.php     # Schedule versioning
└── Api/
    ├── Admin/                     # Admin-specific API endpoints
    └── Teacher/                   # Teacher-specific API endpoints
```

## Key Patterns

### 1. Diverging Responses

Single controller method renders different views based on user role:

```php
public function curriculumLessonsIndex(Request $request)
{
    $user = auth()->user();
    
    if ($user->hasRole('school-admin')) {
        return Inertia::render('features/weekly_system_v1/curriculum_lessons/AdminCurriculumView', [...]);
    }
    
    return Inertia::render('features/weekly_system_v1/curriculum_lessons/TeacherCurriculumView', [...]);
}
```

### 2. Service Layer

Business logic is extracted to services:

```php
class WeeklySystemController extends Controller
{
    public function __construct(
        protected CurriculumService $curriculumService,
        protected WeeklyPlanService $weeklyPlanService
    ) {}
}
```

### 3. API Resources

Consistent JSON responses using Laravel API Resources:

```php
return CurriculumResource::collection($curricula);
```

## TODO: Implementation Steps

1. Create `WeeklySystemController.php` with diverging response methods
2. Extract service layer methods from existing controllers
3. Create API resources for consistent responses
4. Implement admin-specific API endpoints
5. Implement teacher-specific API endpoints

See `PLAN.md` for detailed implementation guide.
