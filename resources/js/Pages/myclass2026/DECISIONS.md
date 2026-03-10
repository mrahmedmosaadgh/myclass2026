# MyClass 2026 — Build Decisions & Open Questions

> Use this file to track architectural decisions, open questions, and choices made during development.

---

## ✅ Confirmed Decisions

| # | Decision | Choice Made | Reason |
|---|---|---|---|
| D1 | Existing files location | **Leave in place** | No risk of breaking routes/imports |
| D2 | Role list | **8 roles**: super-system, system-admin, school-admin, teacher, student, parent, hr, developer | Per discussion |
| D3 | Smart Scanner primary input | **QR Code cards (A/B/C/D)** | Free to print, works offline, "wow factor" |
| D4 | Scanner confirmation method | **Confirm/Cancel QR codes** | Silent, reliable, works in noisy classrooms |
| D5 | Face detection | **face-api.js (client-side)** | No server cost, instant feedback |
| D6 | Offline support | **IndexedDB (Dexie.js) + PWA** | Classrooms have spotty Wi-Fi |
| D7 | Real-time updates | **Laravel Reverb** | Already in stack, no extra cost |

---

## ⏳ Open Decisions (Need Your Input)

| # | Question | Options | Default if not decided |
|---|---|---|---|
| OD1 | Should Smart Scanner also capture student photo? | Yes (more secure) | **Decided: Yes (more secure)** |
| OD2 | Live Lesson — video conferencing or screen share? | External link (Zoom/Meet) | **Decided: External link (Zoom/Meet)** |
| OD3 | Parent portal — should parents see real-time grades? | Yes / Only after teacher publishes | Only after publish |
| OD4 | To-Do list — personal only or synced with class schedule? | Personal only / Auto-sync from weekly plan | Personal only first |
| OD5 | Student QR cards — printed per student or shown on teacher screen? | Printed (physical) / Digital display | Printed (as designed) |
| OD6 | HR module — integrate with external payroll system? | Standalone / API integration | Standalone first |

---

## 🏗️ Build Priority Order (Recommended)

Based on brainstorming ("Wow Factor" strategy first):

### Priority 1 — Smart Scanner MVP (High Impact, New Feature)
- [ ] `teacher/live/SmartScanner/ScanStation.vue`
- [ ] `teacher/live/SmartScanner/QrCardGenerator.vue`
- [ ] `teacher/live/SmartScanner/components/FaceGuard.vue`
- [ ] `teacher/live/SmartScanner/components/QrReader.vue`
- [ ] Laravel: `StudentAnswer` model + migration
- [ ] Laravel: `/api/scan/submit` endpoint

### Priority 2 — Connect Existing Features (Low effort, high value)
- [ ] Teacher Dashboard linking to all existing tools
- [ ] Student Dashboard linking to TakeExam, Grades, Schedule
- [ ] Unified navigation per role

### Priority 3 — Missing Core Features
- [ ] `teacher/records/TopicTracking/`
- [ ] `teacher/time-management/TodoList/`
- [ ] `teacher/live/LiveLesson/`
- [ ] `teacher/live/LiveQuiz/` (enhance existing)

### Priority 4 — Parent Portal
- [ ] Parent dashboard
- [ ] Child progress view
- [ ] Communication channel

### Priority 5 — Polish & Unify
- [ ] Migrate or alias old folders into new structure
- [ ] Shared layout components
- [ ] Role-based route guards

---

## 📅 Session Log

| Date | Decision Made |
|---|---|
| 2026-03-08 | Folder structure plan approved |
| 2026-03-08 | Roles confirmed: 8 total including HR + Developer |
| 2026-03-08 | Smart Scanner included as 🆕 priority feature |
| 2026-03-08 | Existing files stay in place (no migration yet) |
