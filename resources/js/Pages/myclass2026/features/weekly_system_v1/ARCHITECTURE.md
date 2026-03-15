# Weekly System V1 - Architecture Diagrams

## 1. High-Level System Architecture

```mermaid
graph TB
    User[User Admin/Teacher/Student] --> Router[Laravel Router]
    Router --> Middleware[Auth & Permission Middleware]
    Middleware --> Controller[WeeklySystemV1 Controller]
    
    Controller --> ServiceLayer[Service Layer]
    ServiceLayer --> CurriculumService[CurriculumService]
    ServiceLayer --> WeeklyPlanService[WeeklyPlanService]
    ServiceLayer --> TimetableService[TimetableService]
    
    ServiceLayer --> ModelLayer[Model Layer]
    ModelLayer --> Curriculum[Curriculum Model]
    ModelLayer --> WeeklyPlan[WeeklyPlan Model]
    ModelLayer --> Schedule[Schedule Model]
    ModelLayer --> CST[ClassroomSubjectTeacher]
    
    ModelLayer --> Database[(Database)]
```

---

## 2. Feature-First Folder Structure

```mermaid
graph LR
    subgraph "Old: Role-First"
        A1[roles/school-admin/] --> A2[weekly_system/]
        A2 --> A3[curriculum_lessons/Index.vue]
        
        B1[roles/teacher/] --> B2[weekly_system/]
        B2 --> B3[curriculum_lessons/Index.vue]
    end
    
    subgraph "New: Feature-First"
        C1[features/weekly_system_v1/] --> C2[curriculum_lessons/]
        C2 --> C3[Index.vue SHARED]
        C2 --> C4[AdminCurriculumView.vue]
        C2 --> C5[TeacherCurriculumView.vue]
    end
    
    A1 -.->|Migrate to| C1
    B1 -.->|Migrate to| C1
```

---

## 3. Request Flow - Admin Viewing Curriculum

```mermaid
sequenceDiagram
    participant A as Admin Browser
    participant R as Router
    participant M as Middleware
    participant C as WeeklySystemController
    participant S as CurriculumService
    participant D as Database
    
    A->>R: GET /weekly-system-v1/curriculum-lessons
    R->>M: Check authentication
    M->>M: Verify admin role
    M->>C: curriculumLessonsIndex()
    
    C->>S: getSchoolCurricula(school_id)
    S->>D: SELECT * FROM curricula WHERE school_id = ?
    D-->>S: All school curricula
    S-->>C: Curriculum collection
    
    C->>C: Prepare Inertia props
    C->>A: Render AdminCurriculumView + data
    
    Note over A: Shows: Create button,<br/>Lock Dates, All curricula
```

---

## 4. Request Flow - Teacher Viewing Curriculum

```mermaid
sequenceDiagram
    participant T as Teacher Browser
    participant R as Router
    participant M as Middleware
    participant C as WeeklySystemController
    participant S as CurriculumService
    participant D as Database
    
    T->>R: GET /weekly-system-v1/curriculum-lessons
    R->>M: Check authentication
    M->>M: Verify teacher role
    M->>C: curriculumLessonsIndex()
    
    C->>S: getTeacherAssignedCurricula(teacher_id)
    S->>D: SELECT * FROM curricula<br/>WHERE EXISTS (SELECT 1 FROM cst<br/>WHERE cst.teacher_id = ? AND cst.curriculum_id = curricula.id)
    D-->>S: Assigned curricula only
    S-->>C: Filtered curriculum collection
    
    C->>C: Prepare Inertia props<br/>(canCreate=false, canDelete=false)
    C->>T: Render TeacherCurriculumView + data
    
    Note over T: Shows: Assigned only,<br/>No create/delete buttons
```

---

## 5. Component Hierarchy

```mermaid
graph TD
    subgraph "Dashboard Layer"
        AdminDash[AdminDashboard.vue]
        TeacherDash[TeacherDashboard.vue]
    end
    
    subgraph "Feature Views"
        AdminCV[AdminCurriculumView.vue]
        TeacherCV[TeacherCurriculumView.vue]
        AdminWP[AdminWeeklyPlansManager.vue]
        TeacherWP[TeacherWeeklyPlansEditor.vue]
    end
    
    subgraph "Shared Base Components"
        CLI[CurriculumLessons/Index.vue]
        WPM[WeeklyPlans/Manager.vue]
    end
    
    subgraph "UI Components"
        CF[CurriculumForm.vue]
        CL[CurriculumList.vue]
        WPG[WeeklyPlanGrid.vue]
        PS[PeriodSelector.vue]
        WS[WeekSelector.vue]
    end
    
    AdminDash --> AdminCV
    AdminDash --> AdminWP
    TeacherDash --> TeacherCV
    TeacherDash --> TeacherWP
    
    AdminCV --> CLI
    TeacherCV --> CLI
    AdminWP --> WPM
    TeacherWP --> WPM
    
    CLI --> CF
    CLI --> CL
    WPM --> WPG
    WPM --> PS
    WPM --> WS
```

---

## 6. Data Flow - Copy Weekly Plans

```mermaid
sequenceDiagram
    participant T as Teacher UI
    participant C as Controller
    participant PS as PreviewService
    participant CS as CommitService
    participant DB as Database
    
    T->>C: previewCopyPlans(from, to, week)
    C->>PS: Build preview
    
    PS->>DB: Load source plans
    PS->>DB: Load target schedules
    PS->>PS: Match by period_order
    PS-->>C: Preview data (no changes)
    C-->>T: Show preview dialog
    
    T->>C: commitCopyPlans(operations)
    C->>CS: Validate operations
    
    loop For each operation
        CS->>DB: UPDATE weekly_plans<br/>SET cw=?, hw=?, notes=?<br/>WHERE id=?
    end
    
    CS-->>C: Success count
    C-->>T: Show success message
```

---

## 7. Permission Matrix

```mermaid
graph TB
    subgraph "Admin Permissions"
        A1[Create Curriculum]
        A2[Delete Curriculum]
        A3[Set Lock Dates]
        A4[View All Plans]
        A5[Bulk Copy]
    end
    
    subgraph "Teacher Permissions"
        T1[View Assigned Only]
        T2[Edit Own Plans]
        T3[Copy Between My Classes]
        T4[Cannot Create Curriculum]
        T5[Cannot Delete]
    end
    
    Admin --> A1
    Admin --> A2
    Admin --> A3
    Admin --> A4
    Admin --> A5
    
    Teacher --> T1
    Teacher --> T2
    Teacher --> T3
    Teacher --> T4
    Teacher --> T5
```

---

## 8. Route Organization

```mermaid
graph LR
    subgraph "Main Routes"
        M1["/weekly-system-v1/"] --> M2[Dashboard Controller]
        M2 --> M3{Role?}
        M3 -->|Admin| M4[AdminDashboard]
        M3 -->|Teacher| M5[TeacherDashboard]
    end
    
    subgraph "Feature Routes"
        F1["/curriculum-lessons"] --> F2[Controller decides view]
        F2 --> F3{Role?}
        F3 -->|Admin| F4[AdminCurriculumView]
        F3 -->|Teacher| F5[TeacherCurriculumView]
        
        G1["/weekly-plans-manager"] --> G2{Role?}
        G2 -->|Admin| G3[AdminWeeklyPlansManager]
        G2 -->|Teacher| G4[TeacherWeeklyPlansEditor]
    end
    
    subgraph "API Routes"
        API1["/api/curricula"] --> API2[JSON Response]
        API3["/api/weekly-plans"] --> API4[JSON Response]
        API5["/api/schedules"] --> API6[JSON Response]
    end
```

---

## 9. Service Dependencies

```mermaid
graph TD
    WC[WeeklySystemController] --> CS[CurriculumService]
    WC --> WPS[WeeklyPlanService]
    WC --> TS[TimetableService]
    
    CS --> CM[Curriculum Model]
    CS --> CV[CurriculumVersion Model]
    
    WPS --> WP[WeeklyPlan Model]
    WPS --> S[Schedule Model]
    WPS --> CST[ClassroomSubjectTeacher]
    
    TS --> SC[ScheduleCopy Model]
    TS --> S
    
    CST --> T[Teacher Model]
    CST --> C[Classroom Model]
    CST --> SUB[Subject Model]
```

---

## 10. Migration Timeline

```mermaid
gantt
    title Weekly System V1 Migration Phases
    dateFormat  YYYY-MM-DD
    section Foundation
    Directory Setup           :done,    des1, 2026-03-15, 1d
    Documentation             :done,    des2, 2026-03-15, 1d
    
    section Backend
    Controller Setup          :active,  des3, 2026-03-16, 3d
    Service Layer             :         des4, after des3, 3d
    API Resources             :         des5, after des4, 2d
    
    section Frontend
    Shared Components         :         des6, after des5, 4d
    Curriculum Migration      :         des7, after des6, 3d
    Weekly Plans Migration    :         des8, after des7, 4d
    
    section Testing
    Unit Tests                :         des9, after des8, 3d
    Integration Tests         :         des10, after des9, 3d
    E2E Tests                 :         des11, after des10, 3d
    
    section Deployment
    Staging                   :         des12, after des11, 2d
    Pilot School              :         des13, after des12, 7d
    Full Rollout              :         des14, after des13, 14d
```

---

## 11. Before/After Code Comparison

### Before: Duplicate Logic

```mermaid
graph TB
    subgraph "Admin File (22.5KB)"
        A1[Fetch Curricula]
        A2[Form Validation]
        A3[API Calls]
        A4[Display Table]
        A5[Admin Buttons]
    end
    
    subgraph "Teacher File (17.2KB)"
        T1[Fetch Curricula]
        T2[Form Validation]
        T3[API Calls]
        T4[Display Table]
        T5[Teacher Buttons]
    end
    
    A1 -.->|DUPLICATE| T1
    A2 -.->|DUPLICATE| T2
    A3 -.->|DUPLICATE| T3
    A4 -.->|DUPLICATE| T4
```

### After: Shared + Wrappers

```mermaid
graph TB
    subgraph "Shared Index.vue (15KB)"
        S1[Fetch Curricula]
        S2[Form Validation]
        S3[API Calls]
        S4[Display Table]
    end
    
    subgraph "Wrappers"
        A[Admin Wrapper 5KB] --> S1
        T[Teacher Wrapper 5KB] --> S1
    end
    
    A --> A5[Admin Buttons Slot]
    T --> T5[Teacher Buttons Slot]
```

---

## 12. Security Boundaries

```mermaid
graph TB
    subgraph "Frontend Security"
        F1[Props-based Permissions]
        F2[v-if on Action Buttons]
        F3[Role-specific Slots]
    end
    
    subgraph "Backend Security"
        B1[Middleware Checks]
        B2[Policy Authorization]
        B3[Scope Filters on Queries]
        B4[Ownership Verification]
    end
    
    subgraph "Database Security"
        D1[School ID Scoping]
        D2[Soft Deletes]
        D3[Foreign Key Constraints]
    end
    
    F1 --> B1
    F2 --> B2
    F3 --> B3
    B1 --> D1
    B2 --> D2
    B3 --> D3
```

---

These diagrams illustrate the complete architecture of the Weekly System V1 migration from multiple perspectives.
