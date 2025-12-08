# Conversation Memory with Gemini Code Assist

This file contains a summary of key points from our previous conversations to provide context for future sessions.

---

## How to Use and Maintain This Memory File

**For the User:**
1. At the start of each new chat session, upload or reference this file so the assistant has full project context.
2. After any important discussion, ask the assistant to update this file, or update it yourself with:
   - Key decisions and changes
   - New features or bug fixes
   - File paths and function/class names
   - Open questions or next steps
   - Dates for major updates
3. Keep entries concise and organized by topic.
4. Use checklists for tasks and unresolved issues.
5. Add new session highlights under the "Timeline / Updates" section.

**For the Assistant:**
- When asked to update the memory, summarize all main points, decisions, and file references from the current chat.
- Add new features, changes, and open questions to the appropriate sections.
- Update the "Timeline / Updates" section with the current date and a brief summary.
- Ensure instructions remain clear and up to date for both user and assistant.
- Never remove previous context unless explicitly told to do so.

---

## Project Overview: "my_class"

- **Purpose:** Web application for managing school schedules.
- **Core Feature:** "Schedule Copies" module for creating, editing, and managing schedule versions.
- **Tech Stack:**
  - Backend: Laravel
  - Frontend: Vue.js (with Inertia.js)

---

## Key Features & Decisions

### Documentation Portal ([docs/documentation-portal.md](../../../../docs/documentation-portal.md))
- Hybrid: Markdown files + database docs
- Quasar UI, role-based access, search
- Smart `.md` file discovery

### Users & Roles Management ([docs/users-management.md](../../../../docs/users-management.md))
- Manage Teachers, Students, Parents
- Advanced search, school filter, role management

### CSRF Token Fix ([docs/CSRF_Token_Fix_Documentation.md](../../../../docs/CSRF_Token_Fix_Documentation.md))
- Solves 419 CSRF errors after login
- Uses `resources/js/utils/csrf.js` + Axios interceptors

### Internal Project Tracker (Laravel + Vue)
- Created a full-featured project tracker with tasks stored in the database and Markdown backup (`project_tasks.md`).
- Each task includes: title, path, description, priority, type, status, notes, updated_at.
- Backend: Migration, model, controller, and API routes for `project_tasks`.
- Frontend: `ProjectTracker.vue` page with add/edit/delete, filters, and responsive UI.
- Route `/project-manager` registered in `web.php` for Inertia page.
- JSON import/export for tasks, including import to form and validation.
- Responsive UI: table on desktop, cards on mobile.

### Resume System (CV Theme System)
- **Main Entry:** `/developer/resume-system` (Inertia route)
- **Main Page:** `resources/js/Pages/modules/resumes/Index.vue` (Quasar tabs)
- **Features:**
  - Resume Question Bank (add/edit questions)
  - Resume Answers (view all answers)
  - Resume Comments (threaded, media-rich)
- **Backend:**
  - Migrations: `resume_questions`, `resume_answers`, `resume_question_comments`
  - Models: `ResumeQuestion`, `ResumeAnswer`, `ResumeQuestionComment`
  - Controllers: `ResumeQuestionController`, `ResumeAnswerController`, `ResumeQuestionCommentController`
  - API routes for all CRUD and media upload
- **Frontend:**
  - All logic modularized under `modules/resumes/question_bank/`
  - Quasar UI for forms, lists, dialogs, and tabs
  - Media support for text, audio, video, file uploads
  - Comments support threading and media
- **Status:** Core scaffolding, CRUD, and UI complete. Next: add detail pages, permissions, and advanced features as needed.

---

## Security

- [help_ok/help.txt](../../../../help_ok/help.txt) contains hardcoded SSH credentials (**critical!**)
- Change password & remove from history

---

## Open Questions / Next Steps

- [ ] Re-enable and test "Parents" management features
- [ ] Add UI toggle for offline/online mode
- [ ] Review documentation portal search UX
- [ ] Add Markdown export Artisan command for project tasks
- [ ] Add Markdown import Artisan command for project tasks
- [ ] Further UI/UX improvements for mobile
- [ ] Add detail pages and permissions for Resume System

---

## Timeline / Updates

- **2025-07-03:** Memory file structure improved for clarity and quick reference
- **2025-07-06:** Added project tracker, JSON import/export, responsive UI, and workflow improvements
- **2025-07-06:** CV Theme System (Resume) scaffolding complete: backend model, migration, controller, API routes, Vue CRUD, Inertia routes, developer menu, robust role-based sidebar, Quasar tabs for Resume main page.
- **2025-07-06:** Resume System expanded: Question Bank, Answers, Comments, all modularized under main page with Quasar tabs. Media support and threaded comments implemented.

---

## About Gemini Code Assist's Memory

- Memory is limited to the **current chat session**.
- To continue context, upload or reference this file at the start of each new chat.
- Summarize only key points, decisions, and file references for best results.
- Follow the instructions above for all future updates.