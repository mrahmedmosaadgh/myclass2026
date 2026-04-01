# Presentation Builder V4 - Features Specification

Consolidated features from V1, V2, and V3. Excludes drawing tools and split screen.

---

## Core Architecture

**Entry Point:** `PresentationBuilderV4.vue`  
**Modes:** Edit | Present (no separate visibility mode - integrated into Edit)  
**Components:** TopBar, SlidePanel, EditorCanvas, ElementNode, PresenterV4

---

## Edit Mode Features

### Slide Management
| Feature | Description |
|---------|-------------|
| Add Slide | Creates new blank slide |
| Delete Slide | Removes current slide (min 1 slide enforced) |
| Slide Navigation | Numbered thumbnail strip, active = blue border |
| Slide Reordering | Drag to reorder slides (optional) |
| Height Selector | A4 1123px / Normal 500px / Medium 800px / Large 1200px / Custom input |

### Element Creation
| Feature | Description |
|---------|-------------|
| Add Text | Plain text element, 24px, black |
| Add Heading H1 | 48px bold text |
| Add Subheading H2 | 32px, dark grey |
| Add Image | File upload OR clipboard paste |
| Add Rectangle | Filled rect with border, color, radius, opacity |
| Paste Button | Paste images/text/HTML from clipboard |

### Element Manipulation
| Feature | Description |
|---------|-------------|
| Drag Elements | Click and drag to reposition on canvas |
| 8-Point Resize | NW/N/NE/E/SE/S/SW/W handles for rectangles; bottom-right for images |
| Inline Text Edit | `contenteditable` when text element is selected |
| Duplicate Element | Create copy with offset position |
| Delete Element | Remove from slide |
| Layer Ordering | Bring to Front (z=9999), Send to Back (z=1) |

### Context Menu (⋮)
Located on each element when selected:

**VISIBILITY Section:**
- Start Hidden, Click to Show (eye-slash icon) - 5% opacity initially
- Start Visible, Click to Hide (eye icon) - 100% opacity, can hide
- Moveable during Presentation (arrows icon) - draggable in present mode
- No Interaction (lock icon) - always visible, no click behavior
- Hidden Opacity Slider (0-50%)

**LAYERS Section:**
- Bring to Front
- Send to Back

**ELEMENT Section:**
- Duplicate
- Delete
- Reset Visibility Settings

### Visual Indicators
| State | Border | Badge |
|-------|--------|-------|
| Hidden & Clickable | Orange dashed | "start HIDDEN" |
| Shown & Clickable | Green solid | "start SHOWN" |
| Moveable | Blue solid | "MOVEABLE" |
| No Interaction | Grey solid | None |

### Data Import/Export
| Feature | Description |
|---------|-------------|
| Export JSON | Downloads `presentation-v4-{timestamp}.json` |
| Import JSON | Validates array + id + elements structure |
| Auto-save | LocalStorage backup (optional) |

### Technical Features
| Feature | Description |
|---------|-------------|
| Service Worker | Offline support via `/sw.js`, polls updates every 60s |
| Sound Manager | Initialize on mount, play click on element toggle |
| Clipboard Support | `navigator.clipboard.read()` for images/text/HTML |
| Keyboard Shortcuts | Ctrl+V paste, Delete key removes selected element |

---

## Present Mode Features

### Display
| Feature | Description |
|---------|-------------|
| Fullscreen Black BG | Fixed overlay, z-index 1000 |
| White Slide Canvas | Centered, shadow, scrollable vertically |
| Slide Counter | Shows "2 / 5" format |

### Element Behaviors
| Type | Initial | Click Action |
|------|---------|--------------|
| Hidden & Clickable | 5% opacity | Toggle 100% ↔ 5% |
| Shown & Clickable | 100% opacity | Toggle 100% ↔ 10% |
| Moveable | 100% opacity | Click & drag to reposition |
| No Interaction | 100% opacity | None |

### Navigation
| Feature | Description |
|---------|-------------|
| Floating Pill Nav | Bottom-center glassmorphic bar |
| Previous Slide | ◀ button (disabled at first slide) |
| Next Slide | ▶ button (disabled at last slide) |
| Exit Present | ✕ button or ESC key |
| Keyboard Nav | ← → arrows, ESC to exit |

### State Management
- Toggle states persist during presentation session
- State resets when changing slides
- Smooth transitions: 0.4s for opacity, 0.3s for position

---

## Design Language

### Color Palette
| Purpose | Color |
|---------|-------|
| Primary | Indigo-500 (#6366f1) |
| Visible/Shown | Emerald (#10b981) |
| Hidden/Amber | Amber (#f59e0b) |
| Destructive | Rose (#f43f5e) |
| Surface | Neutral-600 (#4b5563) |
| Background | Dark #111827 |
| Canvas | White #ffffff |

### Icons
- Heroicons inline SVG (no external dependency)
- Text: T icon
- Heading: H1 icon
- Image: Photo icon
- Rectangle: Square icon
- Paste: Clipboard icon
- Edit: Pencil icon
- Present: Play icon
- Context menu: ⋮ (3 dots)

### Layout
- Dark neutral background (#111827)
- White A4 canvas (794×1123px default)
- Glassmorphic floating panels
- Left slide panel: 200px width
- Floating element toolbar above canvas

---

## Data Structure

```javascript
{
  slides: [
    {
      id: "slide-123",
      elements: [
        {
          id: "element-456",
          type: "text" | "image" | "rectangle" | "html",
          content: "string",
          x: 150,
          y: 150,
          width: 400,
          height: "auto" | 200,
          // Text properties
          fontSize: 24,
          color: "#000000",
          // Image properties
          src: "data:image/png;base64,...",
          // Rectangle properties
          borderColor: "#2c5aa0",
          borderWidth: 2,
          borderRadius: 0,
          opacity: 1,
          // Visibility configuration
          visibilityOption: "hidden-clickable" | "shown-clickable" | "moveable" | null,
          startHidden: true | false,
          clickable: true | false,
          moveable: true | false,
          hiddenOpacity: 0.1 | 0.05 | 0.0,
          // Animation
          animation: "fadeIn" | "fadeOut" | "bounceIn" | null,
          animationOrder: number | null,
          // Layering
          zIndex: number
        }
      ]
    }
  ],
  currentSlideIndex: 0
}
```

---

## Excluded Features (Not in V4)

The following features from previous versions are **excluded**:

- ❌ Drawing tools (pen, brush, shapes)
- ❌ Split screen / dual pane view
- ❌ Animation sequence ordering (numbered steps)
- ❌ Arrow indicators (replaced by visual badges)
- ❌ Separate "Visibility Settings" mode (now in context menu)

---

## File Structure

```
resources/js/Pages/myclass2026/features/cr/classroom_records_v1/peresentation/v4/
├── FEATURES.md                          ← This file
├── PresentationBuilderV4.vue            ← Root orchestrator
├── components/
│   ├── TopBar.vue                       ← Mode switcher + actions
│   ├── SlidePanel.vue                   ← Left slide thumbnail strip
│   ├── EditorCanvas.vue                 ← White canvas + toolbar
│   ├── ElementNode.vue                  ← Drag/resize/select + context menu
│   └── PresenterV4.vue                  ← Fullscreen presenter
```

---

## Migration Notes

From V2 to V4:
- Visibility settings moved from separate mode to element context menu
- Arrow indicators replaced with color-coded badges
- Simplified: 2 modes instead of 3

From V3 to V4:
- Consolidated features into unified system
- Maintained glassmorphic design language
- Kept inline SVG icon approach

---

## Best Practices

1. Use **Hidden & Clickable** for answers, hints, reveals
2. Use **Shown & Clickable** for optional content that can be hidden
3. Use **Moveable** for drag-to-match interactions
4. Use **No Interaction** for always-visible content
5. Preview in Present mode before finalizing
6. Export JSON as backup before major changes
