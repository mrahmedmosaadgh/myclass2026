
## Objective
1. Write two documentation files inside `v3/` to capture all v2 features and the v3 redesign spec.
2. Add a `builder-v3` route.
3. Implement V3 with only **Edit** and **Present** modes — clean, modern, icon-driven UI.

---

## Phase 1 — Create Documentation Files

### `v3/v2-features.md` — Full V2 Feature Audit

**Architecture**
- Entry: `PresentationBuilderV2.vue` orchestrates 3 modes (`edit` / `visibility` / `present`)
- Sub-components: `SlideEditor`, `VisibilityEditorFinal`, `SlidePresenterFinal`, `SlideElement`
- Route: `GET /classroom-records/presentation/builder-v2` (closure route in `routes/myclass2026/cr/web.php`)

**Edit Mode**
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

**Visibility Mode** *(separate mode in v2 — will be merged into Edit in v3)*
- All elements shown on white canvas with coloured borders: orange-dashed = hidden-clickable, green = shown-clickable, blue = moveable
- ⋮ dropdown per element → 3 options: Hidden & Clickable / Shown & Clickable / Moveable
- Animated badge labels above each element

**Present Mode**
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

---

### `v3/v3-plan.md` — V3 Design Spec

**Design Philosophy**
- Only 2 modes: **Edit** and **Present** (visibility settings live inside the element context menu, same as v2's SlideElement ⋮ menu)
- Design language: dark neutral background (`#111827`), white slide canvas, glassmorphic floating panels
- Icon library: inline SVG Heroicons (no external dependency beyond what already exists)
- Colour palette: indigo-500 for primary actions, emerald for "visible", amber for "hidden", rose for destructive, neutral-600 for surface

**V3 Layout — Edit Mode**
```
┌──────────────────────────────────────────────────────┐
│  LEFT PANEL (200px)     │  CANVAS AREA               │
│  ┌──────────────────┐   │  ┌────────────────────┐    │
│  │  + Add Slide     │   │  │   ELEMENT TOOLBAR  │    │
│  │  ──────────────  │   │  │  (floating strip)  │    │
│  │  [Slide 1]  ▣    │   │  ├────────────────────┤    │
│  │  [Slide 2]  ▣    │   │  │                    │    │
│  │  [Slide 3]  ▣    │   │  │   White A4 Canvas  │    │
│  └──────────────────┘   │  │                    │    │
│                         │  └────────────────────┘    │
│                         │                            │
└──────────────────────────────────────────────────────┘
│  TOP BAR: [Edit ✏] [Present ▶]  |  Export  Import   │
└──────────────────────────────────────────────────────┘
```

**V3 Layout — Present Mode**
```
┌──────────────────────────────────────────────────────┐
│                   Black background                   │
│        ┌──────────────────────────────────┐          │
│        │         White Slide Canvas       │          │
│        │   (elements with their states)   │          │
│        └──────────────────────────────────┘          │
│           [◀]  [2 / 5]  [▶]  [✕]  ← floating pill  │
└──────────────────────────────────────────────────────┘
```

---

## Phase 2 — File Structure

```
resources/js/Pages/myclass2026/features/cr/classroom_records_v1/peresentation/v3/
├── v2-features.md                  ← Phase 1 documentation
├── v3-plan.md                      ← Phase 1 design spec
├── PresentationBuilderV3.vue       ← Root orchestrator (mode state, slides state)
├── components/
│   ├── TopBar.vue                  ← Mode switcher + Export/Import/Delete/Add
│   ├── SlidePanel.vue              ← Left slide thumbnail strip
│   ├── EditorCanvas.vue            ← White canvas + element toolbar + paste zone
│   ├── ElementNode.vue             ← Drag/resize/select + Heroicon ⋮ context menu
│   └── PresenterV3.vue             ← Fullscreen presenter + floating nav pill
```

**Route addition** in `routes/myclass2026/cr/web.php`:
```php
Route::get('/builder-v3', function () {
    return Inertia::render(
        'myclass2026/features/cr/classroom_records_v1/peresentation/v3/PresentationBuilderV3'
    );
})->name('builder-v3');
```

---

## Phase 3 — Component Responsibilities

### `PresentationBuilderV3.vue`
- State: `slides[]`, `currentSlideIndex`, `mode ('edit'|'present')`, `slideHeight`
- Methods: `addSlide`, `deleteSlide`, `updateSlide`, `exportJSON`, `importJSON`
- Renders `TopBar` + `SlidePanel` + `EditorCanvas` (edit) or `PresenterV3` (present)

### `TopBar.vue`
- Left: logo/title
- Center: **Edit** button (pencil icon) | **Present** button (play icon) — pill toggle group, active = indigo
- Right: Export (download icon), Import (upload icon), Delete slide (trash icon, disabled if 1 slide)
- Height selector dropdown (A4 / custom)

### `SlidePanel.vue` (Edit only)
- Vertical strip on left, 200px wide
- Each slide = mini preview card (aspect ratio A4, grey bg, slide number badge)
- Active = indigo border
- Add slide button at bottom (+)
- Drag to reorder (optional future)

### `EditorCanvas.vue`
- White A4 canvas (794×1123px, shadow)
- Floating element toolbar above canvas:
  - Text (T icon), Heading (H1), Subheading (H2), Image (photo icon), Rectangle (square icon), Paste (clipboard icon)
- `@paste` handler on canvas div
- Renders `ElementNode` for each element in current slide

### `ElementNode.vue`
- Absolute positioned, draggable (mousedown → mousemove)
- Types: `image`, `text` (contenteditable when selected), `rectangle` (8 resize handles)
- When selected: indigo outline + floating context menu button (⋮ icon, top-right)
- Context menu (custom dropdown, no Quasar dependency):
  - **VISIBILITY** section:
    - "Start Hidden, Click to Show" (eye-slash icon) — sets `startHidden:true, clickable:true`
    - "Start Visible, Click to Hide" (eye icon) — sets `startHidden:false, clickable:true`
    - "Moveable during Presentation" (arrows icon) — sets `moveable:true`
    - "No Interaction" (lock icon) — clears all
    - Hidden Opacity slider (0–50%)
  - **LAYERS** section: Bring to Front, Send to Back
  - **ELEMENT** section: Duplicate, Delete
- Visual indicator badge on element showing current visibility state (colour-coded dot)

### `PresenterV3.vue`
- `position:fixed` fullscreen, `background:#000`
- White canvas centred, scrollable if tall
- Element rendering: applies visibility state (hidden-clickable starts 5%, shown-clickable starts 100%, moveable draggable)
- Click on clickable elements → toggle opacity + SoundManager.playClick
- Drag moveable elements
- State reset on slide change
- Floating pill nav (bottom-center, glassmorphic):
  - ← (previous, disabled at index 0)
  - `2 / 5` counter
  - → (next, disabled at last)
  - ✕ (exit → mode = 'edit')
- Keyboard: ←→ navigate, ESC exit

---

## Verification / DoD

| Step | Targets | Check |
|---|---|---|
| MD files created | `v3/v2-features.md`, `v3/v3-plan.md` | Files exist, readable |
| Route added | `routes/myclass2026/cr/web.php` | `GET /classroom-records/presentation/builder-v3` returns 200 |
| Edit mode | Add all 5 element types, drag, resize, ⋮ menu | Elements appear and persist |
| Visibility settings | Set hidden/clickable per element in edit | Badge shows correct state |
| Present mode | Elements show/hide on click, moveable drag | Toggle and drag work |
| Export/Import | Export then re-import JSON | Same slides restored |
| Keyboard nav | ← → ESC in present | Navigation works |
| No Visibility tab | UI has only Edit + Present buttons | Confirmed |
