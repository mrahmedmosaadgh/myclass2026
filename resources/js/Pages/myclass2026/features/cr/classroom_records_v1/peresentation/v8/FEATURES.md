# Presentation Builder V8 - Features & Architecture

**Version:** 8.0  
**Build Date:** 2026-05-09  
**URL:** `/classroom-records/presentation/builder-v8`

---

## 🚀 Major Improvements from V7

### 1. **Clean Architecture Overhaul**
- **Pinia-based state management** - Replaced complex store logic with clean, composable Pinia stores
- **Composable pattern** - Extracted drag, resize, snap, and paste logic into reusable composables
- **Component separation** - Each component has single responsibility with clear props/events
- **Type-ready structure** - Prepared for TypeScript migration with consistent data structures

### 2. **Enhanced User Experience**
- **Improved drag & resize** - Smoother interactions with proper cursor feedback
- **Better snap-to-grid** - More predictable alignment with visual grid background
- **Enhanced context menu** - Richer visibility controls with opacity slider
- **Focus mode improvements** - Cleaner distraction-free editing experience

### 3. **Performance Optimizations**
- **Reduced re-renders** - Optimized computed properties and reactive updates
- **Efficient event handling** - Centralized keyboard shortcuts and paste handling
- **Memory management** - Proper cleanup of event listeners and temporary state

---

## 🏗️ Architecture Overview

### State Management (Pinia Stores)
```
stores/
├── presentationStore.js    # Core presentation data & operations
├── uiStore.js             # UI state, selections, modes
└── clipboardStore.js      # Copy/paste functionality
```

### Composables (Reusable Logic)
```
composables/
├── useDrag.js            # Element dragging with snap
├── useResize.js          # 8-direction resize handles
├── useSnap.js            # Grid snapping utilities
└── usePaste.js           # Clipboard paste handling
```

### Components (UI Layer)
```
components/
├── Index.vue             # Main orchestrator
├── EditorCanvas.vue       # Main editing canvas
├── ElementNode.vue       # Individual element renderer
├── Toolbar.vue           # Element creation tools
├── SlideNavigationBar.vue # Slide thumbnails
├── ElementContextMenu.vue # Right-click context menu
└── SlideCanvasReadonly.vue # Read-only slide view
```

---

## 🎯 Core Features

### Edit Mode Features

#### Element Creation
- **Text Elements** - Plain text with font size, color, alignment controls
- **Headings** - H1 (48px) and H2 (32px) with bold styling
- **Images** - File upload or clipboard paste with object-fit controls
- **Rectangles** - Shapes with background, border, radius controls
- **HTML Content** - Rich HTML content support

#### Element Manipulation
- **Drag & Drop** - Smooth element repositioning with grid snap
- **8-Point Resize** - Professional resize handles (NW, N, NE, E, SE, S, SW, W)
- **Copy/Paste** - Full clipboard support for text, images, and elements
- **Keyboard Shortcuts** - Comprehensive keyboard navigation and controls

#### Visibility System (Teaching Features)
- **Hidden & Clickable** - Start at 5% opacity, click to reveal
- **Shown & Clickable** - Start visible, click to hide to 10%
- **Moveable** - Draggable during presentation mode
- **No Interaction** - Always visible, no click behavior
- **Custom Hidden Opacity** - Adjustable 0-50% for hidden elements

#### Slide Management
- **Add/Delete Slides** - Dynamic slide creation with minimum 1 slide
- **Slide Navigation** - Thumbnail-based slide browser
- **Slide Reordering** - Drag to reorder slides
- **Pages View** - Overview of all slides for quick navigation

### Present Mode Features

#### Display Options
- **Continuous View** - Scroll through all slides vertically
- **Single Slide View** - One slide at a time with navigation
- **Zoom Controls** - 25-200% zoom with presets (75%, 100%, 125%, 150%)
- **Fullscreen Support** - Clean presentation mode with minimal UI

#### Element Behaviors
- **Visibility Toggles** - Click-based reveal/hide animations
- **Smooth Transitions** - 0.4s opacity, 0.3s position animations
- **Moveable Elements** - Drag to reposition during presentation
- **Visual Badges** - Clear indicators for element states

---

## 🎨 Design System

### Color Palette
```css
--primary: #6366f1;     /* Indigo */
--success: #10b981;     /* Emerald */
--warning: #f59e0b;     /* Amber */
--danger: #ef4444;       /* Red */
--gray-50: #f9fafb;
--gray-100: #f3f4f6;
--gray-200: #e5e7eb;
--gray-500: #6b7280;
--gray-900: #111827;
```

### Typography
- **System fonts** - ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont
- **Font sizes** - 12px (captions), 14px (body), 16px (controls), 18px (headings)
- **Font weights** - 400 (normal), 500 (medium), 600 (semibold), 700 (bold)

### Spacing & Layout
- **Grid system** - 10px grid with 6px snap threshold
- **Border radius** - 6px (small), 8px (medium), 12px (large)
- **Shadows** - Subtle box-shadows for depth and hierarchy

---

## ⌨️ Keyboard Shortcuts

### Element Operations
- `Delete` - Delete selected element
- `Ctrl+C` - Copy element
- `Ctrl+X` - Cut element  
- `Ctrl+V` - Paste element
- `Ctrl+D` - Duplicate element

### Navigation
- `Arrow Keys` - Nudge selected element (Shift for 10px)
- `Page Up/Down` - Previous/Next slide
- `Home/End` - First/Last slide

### View Controls
- `E` - Toggle Edit/Present mode
- `F` - Toggle Focus mode
- `S` - Toggle Slide navigation
- `T` - Toggle Toolbar
- `P` - Toggle Pages view
- `Ctrl+Plus` - Zoom in
- `Ctrl+Minus` - Zoom out
- `Ctrl+0` - Reset zoom

---

## 📊 Data Structure

### Element Model
```javascript
{
  id: "el-1234567890",
  type: "text" | "image" | "rectangle" | "html",
  content: "string",
  x: 150,
  y: 150,
  width: 200,
  height: 50,
  fontSize: 24,
  color: "#000000",
  fontFamily: "ui-sans-serif",
  fontWeight: "normal",
  textAlign: "left",
  lineHeight: "1.4",
  backgroundColor: "#6366f1",
  border: "none",
  borderRadius: "8px",
  src: "data:image/...",
  objectFit: "cover",
  zIndex: 1,
  visibilityOption: "shown-clickable" | "hidden-clickable" | "moveable" | "no-interaction",
  isVisible: true,
  hiddenOpacity: 0.05
}
```

### Slide Model
```javascript
{
  id: "slide-1234567890",
  elements: [ElementModel, ...]
}
```

### Presentation Model
```javascript
{
  slides: [SlideModel, ...],
  currentSlideIndex: 0,
  description: "string",
  showDescriptionInPresentMode: true,
  version: "v8",
  exportedAt: "2026-05-09T..."
}
```

---

## 🔧 Technical Implementation

### Vue 3 Composition API
- **Script setup** syntax throughout
- **Reactive refs** for local state
- **Computed properties** for derived state
- **Watchers** for side effects

### Pinia Store Pattern
- **Setup syntax** for clean store definition
- **Computed getters** for derived state
- **Action methods** for state mutations
- **Store composition** for complex operations

### Composable Pattern
- **Pure functions** with clear inputs/outputs
- **Reusable logic** across components
- **Event cleanup** in returned functions
- **Type-ready** parameter definitions

### Performance Considerations
- **Debounced resize** to prevent layout thrashing
- **Passive event listeners** where possible
- **Virtual scrolling** for large slide counts (planned)
- **Lazy loading** for heavy components (planned)

---

## 🚀 Migration from V7

### Breaking Changes
- **Store API** - New Pinia stores replace V7 stores
- **Component props** - Simplified prop interfaces
- **Event handling** - Centralized in composables

### Migration Steps
1. **Update imports** - Use new store paths
2. **Update component usage** - New prop interfaces
3. **Test functionality** - Verify all features work
4. **Update custom code** - Adapt to new patterns

### Backward Compatibility
- **Data format** - V7 presentations import successfully
- **URL structure** - Same route patterns
- **User preferences** - Preserved where possible

---

## 📋 Future Roadmap (V9 Planning)

### Planned Features
- **TypeScript migration** - Full type safety
- **Collaborative editing** - Real-time multi-user
- **Advanced animations** - Keyframe-based animations
- **Template system** - Reusable slide templates
- **Export formats** - PDF, PowerPoint, Google Slides
- **Cloud sync** - Auto-save to cloud storage

### Performance Improvements
- **Virtual scrolling** - For large presentations
- **Web Workers** - For heavy computations
- **Service Worker** - Enhanced offline support
- **Code splitting** - Reduced initial bundle size

### Accessibility
- **Screen reader support** - Full ARIA compliance
- **Keyboard navigation** - Complete keyboard control
- **High contrast mode** - Improved visibility
- **Reduced motion** - Respect user preferences

---

## 🐛 Troubleshooting

### Common Issues
1. **Elements not selectable** - Check z-index values
2. **Paste not working** - Verify clipboard permissions
3. **Zoom issues** - Reset zoom to 100%
4. **Performance lag** - Check element count

### Debug Tools
- **Console logging** - Detailed operation logs
- **State inspection** - Vue DevTools support
- **Performance monitoring** - Built-in performance metrics
- **Error boundaries** - Graceful error handling

---

## 📞 Support

### Documentation
- **Inline comments** - Comprehensive code documentation
- **Component docs** - Prop and event documentation
- **Architecture guide** - System design overview

### Getting Help
- **GitHub issues** - Bug reports and feature requests
- **Community forum** - User discussions and tips
- **Video tutorials** - Step-by-step guides (planned)

---

**Builder V8 represents a complete architectural overhaul while maintaining full feature parity with V7, providing a solid foundation for future enhancements.**
