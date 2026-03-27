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

## Final Selector

- Presents all available versions in one menu.
- Defaults to the recommended final choice.
- The final choice runs **Version 2**.

## Notes

- The new versioned scripts were syntax-checked successfully.
- The main root `tools_menu.sh` was left unchanged so the original entry point still works.
- The new selector is intended for choosing between archived versions without losing the original script.

## Next Steps If Needed

- Add a README inside `tools_menu_versions/` if you want a short usage guide.
- If desired later, the root `tools_menu.sh` can be replaced with a shortcut that launches the selector.
