# Implement Reward System Audio Features

## Context
The goal was to enhance the Reward System UI/UX by adding audio feedback for interactions and optional background music to create a more engaging atmosphere.

## Changes Created
- Modified `resources/js/Pages/my_table_mnger/reward_sys/reward_sys.vue` to:
    - Include an `<audio>` element for background music.
    - Add logic to preload and play sound effects (`select`, `reward`, `penalty`) using `Audio` objects to optimize network usage.
    - Add a "Music On/Off" toggle in the Settings tab, persisting the preference to `localStorage`.
    - Integrate `playSound` calls into:
        - Student behavior application (Reward/Penalty).
        - Local student selection logic.
        - Group "Select All" logic.
- Modified `resources/js/Pages/my_table_mnger/reward_sys/reward_sys_comp/StudentCard.vue`:
    - Removed local audio playback (reverted) to centralize audio control in the parent component and specific interactions. (Actually, `StudentCard` logic was reverted to just emit events, so the parent handles general selection sounds or the `toggleSelected` function in the parent handles it. Wait, in `StudentCard.vue` I removed the sound play, but I added `playSound('select')` to `toggleSelected` in the parent. Let me verify. In the parent `reward_sys.vue`, `playSound('select')` is called in `toggleSelected` and `selectGroupStudents`. `StudentCard` emits `select` which calls `handleCardClick` -> emits `select` -> parent listens `@select="toggleAttendance(student.id)"`. `toggleAttendance` also calls `playSound('select')`. So yes, centralized in parent functions.)
- Updated `resources/js/lang/en.json` and `resources/js/lang/ar.json` with new translation keys:
    - `rewardSys.musicOn`
    - `rewardSys.musicOff`

## Technical Details
- **Audio Preloading**: Used `new Audio(src).load()` in `onMounted` to ensure sound effects are ready and do not trigger network requests on every click.
- **Persistence**: Background music state is saved in `reward-system-bg-music` local storage key.
- **Volume**: Background music volume set to 0.1 (10%) to be non-intrusive.

## Next Steps
- None. Feature is complete and verified.
