# V3 Design Specification

## Design Philosophy
- Only 2 modes: **Edit** and **Present** (visibility settings live inside the element context menu, same as v2's SlideElement ⋮ menu)
- Design language: dark neutral background (`#111827`), white slide canvas, glassmorphic floating panels
- Icon library: inline SVG Heroicons (no external dependency beyond what already exists)
- Colour palette: indigo-500 for primary actions, emerald for "visible", amber for "hidden", rose for destructive, neutral-600 for surface

## V3 Layout — Edit Mode
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

## V3 Layout — Present Mode
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

## File Structure
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

## Component Responsibilities

### PresentationBuilderV3.vue
- State: `slides[]`, `currentSlideIndex`, `mode ('edit'|'present')`, `slideHeight`
- Methods: `addSlide`, `deleteSlide`, `updateSlide`, `exportJSON`, `importJSON`
- Renders `TopBar` + `SlidePanel` + `EditorCanvas` (edit) or `PresenterV3` (present)

### TopBar.vue
- Left: logo/title
- Center: **Edit** button (pencil icon) | **Present** button (play icon) — pill toggle group, active = indigo
- Right: Export (download icon), Import (upload icon), Delete slide (trash icon, disabled if 1 slide)
- Height selector dropdown (A4 / custom)

### SlidePanel.vue (Edit only)
- Vertical strip on left, 200px wide
- Each slide = mini preview card (aspect ratio A4, grey bg, slide number badge)
- Active = indigo border
- Add slide button at bottom (+)
- Drag to reorder (optional future)

### EditorCanvas.vue
- White A4 canvas (794×1123px, shadow)
- Floating element toolbar above canvas:
  - Text (T icon), Heading (H1), Subheading (H2), Image (photo icon), Rectangle (square icon), Paste (clipboard icon)
- `@paste` handler on canvas div
- Renders `ElementNode` for each element in current slide

### ElementNode.vue
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

### PresenterV3.vue
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
