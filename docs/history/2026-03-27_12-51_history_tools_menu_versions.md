# History: Tools Menu Versioned Selector Created

**Date:** 2026-03-27 12:51  
**Type:** Documentation / Tooling

## What Was Done

- Created a versioned archive structure for the deployment menu scripts.
- Saved the current `tools_menu.sh` behavior as **Version 1** in:
  - `tools_menu_versions/toolsmenuv1/tools_menu.sh`
- Created an improved **Version 2** in:
  - `tools_menu_versions/toolsmenuv2/tools_menu.sh`
- Added a final selector launcher in:
  - `tools_menu_versions/toolsmenu_selector.sh`

## Version 1

- Preserves the original menu behavior exactly as it existed before versioning.
- Keeps the existing deployment, log, and cache options unchanged.

## Version 2

- Uses the repository root automatically so it can be launched from the selector.
- Adds stale-lock cleanup for the deployment lock file.
- Uses stronger confirmation prompts for risky actions.
- Improves remote cache-clear and route verification flow.
- Keeps the original menu structure familiar while making it safer.
- **Route Verification**: Checks that critical Laravel routes exist on production:
  - `classroom-records/presentation/remote/teacher` - Teacher remote control
  - `classroom-records/presentation/remote/student` - Student remote view  
  - `classroom-records/presentation/remote/test` - Diagnostics page
  - `submit-answer` - Quiz answer submission
- **Cache Clear Process**: 
  - Runs `php artisan optimize:clear` to remove all caches including routes
  - Runs `php artisan optimize` to rebuild a fresh route cache
  - Immediately verifies routes exist in the new cache
  - This fixes 404 issues when new routes are added but not registered

## Final Selector

- Presents all available versions in one menu.
- Defaults to the recommended final choice.
- The final choice runs **Version 2**.

## Notes

- The new versioned scripts were syntax-checked successfully.
- The main root `tools_menu.sh` was left unchanged so the original entry point still works.
- The new selector is intended for choosing between archived versions without losing the original script.

## When to Use Route Verification

- **After deployment**: Ensures new routes are registered (especially for the diagnostics page)
- **Cache issues**: Fixes 404 errors when Laravel's route cache is stale
- **Debugging**: Quickly identify missing routes before users report issues
- **Option 4**: Deploy + Cache Clear (runs verification after deployment)
- **Option 7**: Server-only Cache Clear + Route Verify (standalone check)

## Next Steps If Needed

- Add a README inside `tools_menu_versions/` if you want a short usage guide.
- If desired later, the root `tools_menu.sh` can be replaced with a shortcut that launches the selector.
