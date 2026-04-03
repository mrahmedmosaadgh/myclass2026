# Timing Stage Files Refactor
This plan replaces the current single timing config with a central stage index plus separate per-stage timing files, where each stage starts with the same default timing for all days and can be customized later.

## Goal
Move timing data to a structure that is easier to understand and edit:
- A central file listing stages, defaulting to `prim`, `middle`, `sec`
- One JSON file per stage
- Each stage file starts with one default timing set shared by all days
- Day-specific customization remains possible later without forcing it now

## Proposed Data Shape
- **Central index file**
  - Holds the stage list, e.g. `default: ["prim", "middle", "sec"]`
  - Optionally holds labels if needed for UI later
- **Per-stage files**
  - Example: `prim.json`, `middle.json`, `sec.json`
  - Suggested shape:
    - `default`: array of timing slots used for the stage by default
    - `days`: object with `d1`..`d6`, initially all `null`
  - `null` day value means: use that stage's `default`

## Files Likely To Change
- `resources/js/Pages/MicroComponentTest/mytable/MyTableSchedule/v5/data/stage_day_timings.json`
  - Replace or split into central stage index + stage files
- `resources/js/Pages/MicroComponentTest/mytable/MyTableSchedule/v5/composables/useTimingResolver.js`
  - Remove the recent auto-apply concept
  - Resolve from per-stage file structure instead
- `resources/js/Pages/MicroComponentTest/mytable/MyTableSchedule/v5/composables/useAppStore.js`
  - Load the new timing structure on init
  - Persist updated structure without auto-generating overrides
- `resources/js/Pages/MicroComponentTest/mytable/MyTableSchedule/v5/components/menu/MenuTimingConfig.vue`
  - Keep stage/day editing UI, but point it to the new structure
  - Ensure reset/copy behavior still works cleanly

## Implementation Steps
1. Define the new canonical timing schema:
   - stage index file
   - per-stage timing files
2. Remove the current `autoApplyToAll` / `appliedStages` / `appliedDays` approach.
3. Update store initialization to load the new schema as the default saved config.
4. Update timing resolution so it resolves:
   - day override for current stage
   - otherwise stage default
   - otherwise fallback
5. Update timing editor behavior so users can:
   - edit the stage default once
   - leave days untouched to inherit the stage default
   - later customize a specific day only when needed
6. Verify import/export and cloud snapshot compatibility for the new timing config shape.

## Notes / Risks
- The current code has partial timing changes already applied; these should be cleaned up as part of the refactor.
- `useAppStore.js` currently references `autoApplyDefaults`; that needs to be removed during implementation.
- `useTimingResolver.js` currently defines `autoApplyDefaults` inside `useTimingResolver`, so it is not exported correctly for store usage.
- Existing saved IndexedDB timing data may use the old structure; we may need a small migration or fallback reader.

## Expected Result
You will have a simpler timing system:
- stages are defined in one place
- each stage has its own timing file
- all days use the same stage timing by default
- later customization per day remains easy
- reset can return a stage back to its shared default behavior
