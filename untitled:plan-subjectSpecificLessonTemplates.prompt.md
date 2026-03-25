## Plan: Subject-specific Lesson Templates

TL;DR — Reuse the existing `LessonPlanTemplate` model and `Subject.lesson_plan_templates` JSON if needed, but add DB associations and a lesson-level template reference. Backend changes add relations, a migration, and an apply-template workflow; frontend changes add a template selector in the lesson editor and an "apply template" button to generate slides per section/slide-count.

### Steps
1. Schema: Add columns and links
   - Add migration to `lesson_presentations` table: `lesson_plan_template_id` (nullable FK to `lesson_plan_templates.id`), `template_snapshot` (json, nullable) and `is_template_applied` (boolean default false).
   - Add migration to `lesson_plan_templates` table: add `subject_id` (nullable FK to `subjects.id`) so templates can be scoped to a subject (if null = global).
   - Files to change: `database/migrations/*`, `app/Models/free/LessonPresentation.php`, `app/Models/CourseManagement/LessonPlanTemplate.php`.

2. Backend API & controllers
   - Update `LessonPresentationController@store` and `update` to accept `lesson_plan_template_id` and `is_template_applied` and `template_snapshot`.
   - Add helper to apply template: when `lesson_plan_template_id` present in store, create slides per template `structure` mapping (section id => slides count and default `slide_type` and `slide_content`), and store `template_snapshot` as the current template JSON (safe version).
   - Return the created slides and persisted `lesson_presentation` with template fields.
   - Update `LessonPresentationController@show` to return `lesson_plan_template_id`, `template_snapshot` and `templates` available for the lesson's subject.
   - Update `LessonPlanTemplateController` to accept `subject_id` on create/edit and scope listing to specific subject + global ones (subject_id null) when queried by subject.
   - Files to change: `app/Http/Controllers/LessonPresentationController.php`, `app/Http/Controllers/CourseManagement/LessonPlanTemplateController.php`, `routes/web_lesson_presentation.php`, `routes/api.php`.

3. Subject-level templates (existing JSON vs DB)
   - Use `lesson_plan_templates` DB table for global and subject-scoped templates.
   - Keep `Subject.lesson_plan_templates` JSON as backward-compatible storage; but prefer DB queries for selectable templates.
   - Modify `SubjectController@getLessonPlanTemplates` to additionally query the DB and combine (DB templates for the subject + global templates) and return combined list.

4. Frontend changes
   - In `resources/js/Pages/my_table_mnger/lesson_presentation/lesson_presentation.vue`:
     - Add a dropdown/select listing templates (combined global + subject templates) — load via API `GET /admin/subject/{subject}/lesson-plan-templates` or `GET /api/lesson-plan-templates?subject_id=...`.
     - When a template is selected, show its structure preview and “Apply Template” button.
     - On Click “Apply Template":
       - If presentation is new: include `lesson_plan_template_id` in the create payload and let the backend create slides from template.
       - If presentation exists: call a new API endpoint (or re-use slides endpoints) to populate slides based on selected template (confirm overwrite) and update `lesson_presentation` template fields.
     - Keep the ability to edit and save slides afterward.
   - Create UI components: `TemplateSelect.vue` (reusable) and small preview component showing sections & slide counts.
   - Files to change: `resources/js/Pages/my_table_mnger/lesson_presentation/lesson_presentation.vue`, `resources/js/Pages/my_class/admin/Subjects/LessonPlanTemplates.vue`, add `resources/js/components/templates/TemplateSelect.vue`.

5. Template structure format & logic
   - Standardize template `structure` JSON to be section-oriented, e.g.:
     {
       "sections": [
         { "id": "objectives", "title":"Objectives", "slides": 1, "default_slide_type": "text", "defaults": { "title": "", "content": "" } },
         { "id": "warmup", "title":"Warm-Up", "slides": 0 },
         ...
       ],
       "notes": "Optional note"
     }
   - Mapping rules:
     - For each `section` entry, create that number of slides, set `section` field on `LessonPresentationSlide`, set `slide_type` to `default_slide_type` or `text` and `slide_content` to `defaults` if present.
     - Store the template snapshot in `lesson_presentations.template_snapshot` to preserve the applied template at the time of generation.

6. UX: Per-lesson customization & manage templates for subject
   - Teacher workflow: select a template from list filtered to subject (and global). Option to preview.
   - “Apply Template” generates slides, but teacher can edit or remove slides.
   - Teacher or admin can manage templates under `my_class/admin/Subjects/LessonPlanTemplates.vue`.
   - If teacher needs a lesson-specific variation, allow saving current presentation configuration as a template (optional UI) and set subject_id on new template creation or global.

### Further Considerations
1. Data migration options and audit: Decide whether to migrate `Subject.lesson_plan_templates` JSON into the `lesson_plan_templates` table (one-time script) or keep them as JSON; recommend migrating into DB for consistency.
2. Snapshot: Keep `template_snapshot` to preserve slide creation deterministically when the original template changes later.
3. Reapplying templates: Provide a non-destructive apply (confirm for overwrite), optionally add `undo` or keep revision history for slides.
4. API Performance: When fetching templates, combine DB templates with JSON field if still used; prefer DB first.
5. Tests & Validation: Add tests around template application, slide counts creation, and `lesson_plan_template_id` relationship. Consider unit tests for `LessonPresentationController@store`, `update`, and `addSlide` behavior.

Would you like me to draft the specific migrations and the controller update pseudo-code next?
