# 2026-03-13 00:50 | Curriculum Architecture Improvements & Editions Pattern

## What was done:

1. **Phase A: Editions Pattern (Versioning Core)**
   - Replaced individual lesson versioning with a full `curriculum_versions` table.
   - Removed the `active` column from the `curricula` table.
   - Updated `curriculum_topics` to link to `curriculum_version_id`.
   - Updated `curriculum_lessons` to link to `curriculum_version_id` and made `topic_id` nullable to support both flat and nested curriculum structures cleanly.
   - Updated `curriculum_maps` to point directly to the version.

2. **Phase B: Granular Skill Tagging**
   - Created `skills` and polymorphic `skillables` tables for gap tracking.
   - Removed legacy `skill` and `objective` free-text columns from `curriculum_lessons` and migrated them to use the pivot table.
   - Updated `Skill` Model to support MorphToMany relations.

3. **Phase C: Gamification Hooks**
   - Added a `gamification_hooks` JSON column to `curriculum_lesson_plans` so teachers can automate behavioral triggers (points/badges) inside daily execution plans.

4. **Phase D: Database Cleanup**
   - Verified as obsolete and completely safely deleted the old flat `lessons` table (`2025_05_20_000000_create_lessons_table.php`) and the corresponding `App\Models\Lesson.php`.
   - Cleaned up dangling relations in `Subject.php`.

5. **Phase E: JSON Hydration Safety**
   - Created and applied `WeeklyPlanCast` and `LessonPlanCast` to their respective models to enforce JSON typing and avoid frontend parsing errors on `null` strings.

## What still needs to be done:
- Run `php artisan migrate` (or `migrate:fresh --seed` depending on environment needs) since migrations were directly edited.
- Update frontend Vue components (specifically maps and lessons tables) to adjust to the removed `skill`, `objective`, and `active` columns, and to handle the new `curriculum_versions` relation instead of a direct `curriculum` relation. 
