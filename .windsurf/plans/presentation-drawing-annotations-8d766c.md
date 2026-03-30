# Presentation Drawing & Annotation System

Build a reusable Vue drawing component for presentation annotations with full-slide overlay, per-slide persistence, collapsible toolbar, and comprehensive drawing tools.

## Architecture Overview

**Component Structure:**
- `DrawingCanvas.vue` - Main canvas overlay component with HTML5 Canvas API
- `DrawingToolbar.vue` - Collapsible side panel with all drawing tools
- `DrawingControls.vue` - Individual tool controls (color picker, brush size, etc.)
- `useDrawing.js` - Core drawing composable (pen, shapes, text, eraser)
- `useDrawingHistory.js` - Undo/redo stack management
- `useDrawingStorage.js` - IndexedDB persistence for drawings
- `drawingStore.js` - Pinia store for drawing state

**Integration Points:**
- Overlay layer in `Index.vue` (above slides, below navigation)
- Auto-save integration with existing `presentationStore.js`
- Slide navigation awareness for per-slide drawing switching

## Recommended Libraries

1. **Canvas Rendering:** Native HTML5 Canvas (no library needed for basic drawing)
2. **Smooth Lines:** Perfect Freehand (`perfect-freehand`) - pressure-sensitive smooth strokes
3. **Shape Drawing:** Custom implementation with Canvas API
4. **Text Rendering:** Canvas text API + contenteditable overlay for editing
5. **Touch Support:** Pointer Events API (unified mouse/touch/stylus)
6. **Color Picker:** `@vueuse/core` useColorPicker or custom implementation

## Implementation Plan

### Phase 1: Core Drawing Infrastructure
- [ ] Create `drawingStore.js` with state management (active tool, color, brush size, opacity)
- [ ] Create `useDrawing.js` composable with canvas setup and basic pen tool
- [ ] Implement smooth line drawing using perfect-freehand library
- [ ] Create `DrawingCanvas.vue` as transparent overlay component
- [ ] Add pointer events handling (mouse + touch + stylus support)

### Phase 2: Drawing Tools
- [ ] **Pen Tool:** Freehand drawing with pressure sensitivity
- [ ] **Highlighter:** Semi-transparent strokes (30-40% opacity)
- [ ] **Shapes:** Rectangle, circle, line, arrow (with preview while dragging)
- [ ] **Eraser:** Object-based eraser (remove entire strokes/shapes)
- [ ] **Text Tool:** Click to add editable text annotations
- [ ] **Laser Pointer:** Temporary red dot that follows cursor (no persistence)

### Phase 3: Drawing Toolbar UI
- [ ] Create `DrawingToolbar.vue` as collapsible side panel (right side)
- [ ] Tool selection buttons with icons (Lucide icons)
- [ ] Color picker with preset palette + custom color
- [ ] Brush size slider (1-20px range)
- [ ] Opacity slider (10-100% range)
- [ ] Clear current slide button
- [ ] Undo/redo buttons with keyboard shortcuts

### Phase 4: History & State Management
- [ ] Create `useDrawingHistory.js` with undo/redo stack
- [ ] Implement command pattern for all drawing actions
- [ ] Keyboard shortcuts: Ctrl+Z (undo), Ctrl+Y (redo), Ctrl+Shift+Z (redo)
- [ ] Tool switching shortcuts: P (pen), H (highlighter), E (eraser), T (text), L (laser)
- [ ] Clear history when switching slides

### Phase 5: Persistence & Storage
- [ ] Create `useDrawingStorage.js` for IndexedDB operations
- [ ] Add `drawings` field to slide data structure in presentationStore
- [ ] Auto-save drawings on every stroke/shape completion (debounced 500ms)
- [ ] Load drawings when switching slides
- [ ] Export drawings as JSON with slide data
- [ ] Support standalone drawing sessions (separate from presentations)

### Phase 6: Multi-Slide Support
- [ ] Watch `currentSlideIndex` in presentationStore
- [ ] Save current slide drawings before switching
- [ ] Load new slide drawings after switching
- [ ] Clear canvas and redraw from saved data
- [ ] Handle slide deletion (remove associated drawings)

### Phase 7: Advanced Features
- [ ] Snap-to-grid toggle (optional 10px grid)
- [ ] Alignment guides for shapes
- [ ] Export current slide as PNG (with drawings baked in)
- [ ] Import/export all drawings as JSON
- [ ] Drawing layer visibility toggle (show/hide all annotations)

### Phase 8: Presentation Mode Integration
- [ ] Add drawing toolbar FAB button in presentation HUD (bottom-left, near leaderboard)
- [ ] Collapsible panel slides in from right side
- [ ] Toolbar auto-hides after 5 seconds of inactivity (optional)
- [ ] Laser pointer mode for presentation (no save, just visual)
- [ ] Drawing mode indicator in navbar

### Phase 9: Events & Plugin Architecture
- [ ] Emit `onDraw` event on every stroke/shape
- [ ] Emit `onChange` event when drawings modified
- [ ] Emit `onSave` event when auto-save completes
- [ ] Emit `onSlideChange` event with drawing data
- [ ] Plugin system for custom tools (extensible architecture)

### Phase 10: Responsive & Accessibility
- [ ] Responsive toolbar for tablet/mobile
- [ ] Touch gesture support (pinch to zoom canvas)
- [ ] Keyboard navigation for toolbar
- [ ] ARIA labels for accessibility
- [ ] Performance optimization (canvas layer caching)

## File Structure

```
resources/js/Pages/myclass2026/features/cr/classroom_records_v1/peresentation/v5/
├── components/
│   ├── drawing/
│   │   ├── DrawingCanvas.vue          # Main canvas overlay
│   │   ├── DrawingToolbar.vue         # Collapsible side panel
│   │   ├── DrawingControls.vue        # Color picker, sliders
│   │   ├── ToolButton.vue             # Reusable tool button
│   │   └── LaserPointer.vue           # Laser pointer component
├── composables/
│   ├── drawing/
│   │   ├── useDrawing.js              # Core drawing logic
│   │   ├── useDrawingHistory.js       # Undo/redo
│   │   ├── useDrawingStorage.js       # IndexedDB persistence
│   │   ├── tools/
│   │   │   ├── usePenTool.js          # Pen implementation
│   │   │   ├── useShapeTool.js        # Shapes implementation
│   │   │   ├── useTextTool.js         # Text implementation
│   │   │   ├── useEraserTool.js       # Eraser implementation
│   │   │   └── useLaserPointer.js     # Laser pointer
├── stores/
│   └── drawingStore.js                # Drawing state management
```

## Data Structure

### Drawing Store State
```javascript
{
  isDrawingMode: false,           // Drawing overlay active
  activeTool: 'pen',              // 'pen' | 'highlighter' | 'eraser' | 'text' | 'laser' | 'rectangle' | 'circle' | 'line' | 'arrow'
  color: '#000000',               // Current stroke color
  brushSize: 3,                   // 1-20px
  opacity: 100,                   // 10-100%
  isToolbarOpen: true,            // Toolbar visibility
  showGrid: false,                // Snap-to-grid
  currentDrawing: null,           // Active stroke being drawn
  history: [],                    // Undo stack
  historyIndex: -1                // Current position in history
}
```

### Slide Drawing Data
```javascript
{
  slideId: 'slide-123',
  drawings: [
    {
      id: 'draw-1',
      type: 'stroke',              // 'stroke' | 'shape' | 'text'
      tool: 'pen',                 // Tool used
      points: [[x, y], ...],       // Path points
      color: '#000000',
      brushSize: 3,
      opacity: 100,
      timestamp: '2026-03-30T20:13:00Z'
    },
    {
      id: 'draw-2',
      type: 'shape',
      tool: 'rectangle',
      x: 100, y: 100,
      width: 200, height: 150,
      color: '#ff0000',
      opacity: 50,
      timestamp: '2026-03-30T20:14:00Z'
    },
    {
      id: 'draw-3',
      type: 'text',
      tool: 'text',
      x: 300, y: 200,
      content: 'Important!',
      fontSize: 24,
      color: '#0000ff',
      timestamp: '2026-03-30T20:15:00Z'
    }
  ],
  lastModified: '2026-03-30T20:15:00Z'
}
```

## UI/UX Design

### Collapsible Toolbar Layout (Right Side Panel)
```
┌─────────────────────────────────────────────┐
│  [Presentation Content]          │ ╔═══════╗│
│                                   │ ║ Tools ║│
│                                   │ ╠═══════╣│
│                                   │ ║  🖊️   ║│ Pen
│                                   │ ║  🖍️   ║│ Highlighter
│                                   │ ║  ⬜   ║│ Rectangle
│                                   │ ║  ⭕   ║│ Circle
│                                   │ ║  ➖   ║│ Line
│                                   │ ║  ➡️   ║│ Arrow
│                                   │ ║  T    ║│ Text
│                                   │ ║  🧹   ║│ Eraser
│                                   │ ║  🔴   ║│ Laser
│                                   │ ╠═══════╣│
│                                   │ ║ Color ║│
│                                   │ ║ [🎨]  ║│
│                                   │ ╠═══════╣│
│                                   │ ║ Size  ║│
│                                   │ ║ [═══] ║│ Slider
│                                   │ ╠═══════╣│
│                                   │ ║ ↶ ↷   ║│ Undo/Redo
│                                   │ ║ 🗑️    ║│ Clear
│                                   │ ╚═══════╝│
└─────────────────────────────────────────────┘
```

### Toolbar States
- **Collapsed:** Small FAB button (pencil icon) on right edge
- **Expanded:** Full panel slides in from right (280px width)
- **Auto-hide:** Collapses after 5 seconds of inactivity (optional setting)

## Integration Steps

1. Add `DrawingCanvas.vue` to `Index.vue` as overlay layer
2. Add drawing FAB button to presentation HUD (near leaderboard)
3. Update `presentationStore.js` to include `drawings` in slide schema
4. Watch slide changes and sync drawing data
5. Auto-save drawings with existing presentation auto-save
6. Add keyboard shortcuts to main `Index.vue` keydown handler

## Testing Checklist

- [ ] Draw smooth lines with mouse
- [ ] Draw smooth lines with touch
- [ ] Draw smooth lines with stylus (pressure sensitivity)
- [ ] All shape tools work correctly
- [ ] Text tool allows editing and positioning
- [ ] Eraser removes strokes/shapes
- [ ] Laser pointer doesn't persist
- [ ] Undo/redo works correctly
- [ ] Drawings save automatically
- [ ] Drawings load when switching slides
- [ ] Drawings export with presentation JSON
- [ ] Toolbar is responsive on tablet
- [ ] Keyboard shortcuts work
- [ ] Performance is smooth (60fps drawing)

## Notes

- Use `requestAnimationFrame` for smooth drawing
- Debounce auto-save to avoid excessive writes
- Cache canvas layers for performance
- Consider WebGL for very large canvases (future optimization)
- Support both raster (canvas) and vector (SVG) export options
- Implement drawing permissions for multi-user scenarios (future)
