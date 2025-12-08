# Weekly Plan System - Implementation Todo

## Phase 1: Database Structure
- [ ] Create migration for weekly_plan_headers table
- [ ] Create migration for weekly_plan_sessions table
- [ ] Create model for WeeklyPlanHeader
- [ ] Create model for WeeklyPlanSession
- [ ] Set up model relationships (hasMany, belongsTo)

## Phase 2: API Structure
- [ ] Create WeeklyPlanHeaderController
- [ ] Create WeeklyPlanSessionController
- [ ] Create API routes for CRUD operations
- [ ] Create form requests for validation
- [ ] Add authorization policies

## Phase 3: Frontend Structure
- [ ] Create Vue page for weekly plan listing
- [ ] Create Vue component for session cards
- [ ] Implement drag-and-drop for reordering
- [ ] Add modal for session editing
- [ ] Create week navigation component

## Phase 4: Business Logic
- [ ] Create service to generate initial plan from lessons
- [ ] Implement session index calculation based on classes_per_week
- [ ] Add schedule change handling (period_code updates)
- [ ] Create helper for period_code generation

## Phase 5: Testing & Polish
- [ ] Create factories for testing
- [ ] Write feature tests
- [ ] Add frontend validation
- [ ] Create documentation
- [ ] Add error handling

## Phase 6: Integration
- [ ] Connect with existing lesson system
- [ ] Add permissions for teachers
- [ ] Implement real-time updates
- [ ] Add export functionality