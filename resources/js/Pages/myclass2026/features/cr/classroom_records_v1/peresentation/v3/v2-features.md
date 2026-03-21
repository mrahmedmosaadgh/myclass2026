# V2 Feature Audit

## Architecture
- Entry: `PresentationBuilderV2.vue` orchestrates 3 modes (`edit` / `visibility` / `present`)
- Sub-components: `SlideEditor`, `VisibilityEditorFinal`, `SlidePresenterFinal`, `SlideElement`
- Route: `GET /classroom-records/presentation/builder-v2` (closure route in `routes/myclass2026/cr/web.php`)

## Edit Mode

| Feature | Detail |
|---|---|
| Add Text | Plain text element, 24px, black |
| Add Heading H1 | 48px bold |
| Add Subheading H2 | 32px, dark grey |
| Add Image | File upload OR clipboard paste (Ctrl+V or button) |
| Add Rectangle | Filled rect with border, color, radius, opacity |
| Clipboard paste | `navigator.clipboard.read()` → image or text |
| Drag elements | `mousedown` → `mousemove` delta on canvas |
| 8-point resize handles | NW/N/NE/E/SE/S/SW/W for rectangles; bottom-right for images |
| Inline text editing | `contenteditable` on text elements when selected |
| ⋮ context menu (Quasar q-menu) | Visibility settings, layer ordering, duplicate, delete, reset |
| Visibility in menu | "Start Hidden/Click to Show", "Start Visible/Click to Hide", custom opacity slider (0–1) |
| Layer ordering | "Move to Top" (z=9999), "Move to Background" (z=1) |
| Height selector | Dropdown: A4 1123px / Normal 500px / Medium 800px / Large 1200px / Custom number input |
| Slide thumbnails | Numbered buttons strip; active = blue |
| Add / Delete slide | Adds new empty slide; deletes current; min 1 |
| Export JSON | Downloads `presentation-v2-{ts}.json` |
| Import JSON | File input, validates array + id + elements |
| Service Worker | Registers `/sw.js` for offline, polls updates every 60s |
| Sound | `SoundManager.initialize()` on mount; `playClick(0.5)` on element toggle |

## Visibility Mode
*(separate mode in v2 — will be merged into Edit in v3)*
- All elements shown on white canvas with coloured borders: orange-dashed = hidden-clickable, green = shown-clickable, blue = moveable
- ⋮ dropdown per element → 3 options: Hidden & Clickable / Shown & Clickable / Moveable
- Animated badge labels above each element

## Present Mode

| Feature | Detail |
|---|---|
| Fullscreen black bg | Fixed overlay, z-index 1000 |
| White slide canvas | Centred, shadow, scrollable vertically |
| hidden-clickable | Starts at 5% opacity, click → 100% with sound |
| shown-clickable | Starts at 100%, click → 10% with sound |
| moveable | Drag to reposition during presentation |
| State reset | Toggle state cleared on slide change |
| Floating pill nav bar | Fixed bottom-center: ← counter → X (all circle/pill buttons, glassmorphic bg) |
| Keyboard nav | ← → arrows, ESC to exit |
