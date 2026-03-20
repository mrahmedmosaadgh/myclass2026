# Vue Presentation Builder

A fully-featured presentation builder built with Vue 3, supporting clipboard paste, drag-and-drop, animations, and JSON import/export.

## Features

### 📝 Edit Mode
- **Clipboard Paste**: Paste images and text directly from clipboard (Ctrl+V / Cmd+V)
- **Drag to Position**: Click and drag elements anywhere on the slide
- **Resize Elements**: Drag the resize handle to adjust size
- **Inline Text Editing**: Click text to edit content directly
- **Delete Elements**: Remove unwanted elements with the delete button

### ✨ Animation Mode
- **Set Initial State**: Choose if elements start visible or hidden
- **Click Animations**: Configure fade in/out effects on click
- **Animation Preview**: Visual badges show animation settings
- **Per-Element Control**: Configure each element independently

### ▶️ Present Mode
- **Fullscreen Presentation**: Clean, distraction-free view
- **Navigation Controls**: Previous/Next buttons and keyboard shortcuts
- **Click Animations**: Click elements to trigger fade effects
- **Keyboard Shortcuts**:
  - Arrow keys (←/→) to navigate slides
  - ESC to exit presentation

### 💾 Data Management
- **Export to JSON**: Save presentations for later use
- **Import from JSON**: Load previously saved presentations
- **Multi-Slide Support**: Create and manage multiple slides
- **Slide Navigation**: Quick thumbnail navigation between slides

## Component Structure

### Main Components

1. **PresentationBuilder.vue** - Main container component
   - Manages slides array and current slide
   - Handles mode switching (Edit/Animation/Present)
   - Controls import/export functionality
   
2. **SlideEditor.vue** - Edit mode interface
   - Handles clipboard paste events
   - Manages element creation from clipboard
   
3. **AnimationEditor.vue** - Animation configuration
   - Sets initial visibility state
   - Configures click animations
   
4. **SlidePresenter.vue** - Presentation mode
   - Fullscreen presentation view
   - Handles navigation and animations
   
5. **SlideElement.vue** - Reusable element component
   - Supports both image and text types
   - Handles drag, resize, and edit operations
   - Manages animation states

## Usage

### Quick Start (Standalone HTML)

Open `demo.html` in a web browser - no build step required!

### Using as Vue Components

```javascript
import PresentationBuilder from './PresentationBuilder.vue';

export default {
  components: {
    PresentationBuilder
  }
}
```

### Data Structure

Each presentation is an array of slides:

```json
[
  {
    "id": "unique-id",
    "elements": [
      {
        "id": "element-id",
        "type": "text",
        "content": "Hello World",
        "x": 150,
        "y": 150,
        "width": 400,
        "fontSize": 24,
        "color": "#ffffff",
        "initialState": "visible",
        "animation": "fadeIn"
      },
      {
        "id": "element-id-2",
        "type": "image",
        "src": "data:image/png;base64,...",
        "x": 100,
        "y": 100,
        "width": 300,
        "height": 200,
        "initialState": "hidden",
        "animation": "fadeOut"
      }
    ]
  }
]
```

### Element Properties

**Common Properties:**
- `id` - Unique identifier
- `type` - "text" or "image"
- `x`, `y` - Position coordinates
- `initialState` - "visible" or "hidden"
- `animation` - null, "fadeIn", or "fadeOut"

**Text-specific:**
- `content` - Text content
- `width` - Max width
- `fontSize` - Font size in pixels
- `color` - Text color (hex)

**Image-specific:**
- `src` - Image source (data URL or external URL)
- `width`, `height` - Image dimensions

## Animation Behavior

### Fade In Animation
- Best with `initialState: "hidden"`
- Element starts invisible
- Clicking makes it fade in
- Clicking again toggles it back

### Fade Out Animation
- Best with `initialState: "visible"`
- Element starts visible
- Clicking makes it fade out
- Clicking again toggles it back

### No Animation
- Respects `initialState` only
- No click interaction in present mode

## Keyboard Shortcuts

**Edit Mode:**
- `Ctrl/Cmd + V` - Paste from clipboard

**Present Mode:**
- `→` - Next slide
- `←` - Previous slide
- `ESC` - Exit presentation

## Browser Support

- Chrome/Edge (recommended)
- Firefox
- Safari

**Note:** Clipboard paste works best in Chrome/Edge due to browser API support.

## Customization

### Styling
All styles are scoped to components. Modify the `<style scoped>` sections to customize appearance.

### Default Values
Edit the component data/props to change:
- Default slide size
- Default element positions
- Default font sizes and colors
- Animation durations

### Adding New Element Types
To add new element types (e.g., shapes, videos):
1. Add type to SlideElement.vue template
2. Add creation method to SlideEditor.vue
3. Update data structure documentation

## Tips

1. **Image Quality**: Pasted images are stored as base64 - large images increase file size
2. **Text Editing**: Click text in edit mode to edit, click outside to finish
3. **Animation Setup**: Use Animation Mode to configure, then test in Present Mode
4. **File Management**: Export regularly to avoid losing work
5. **Presentation Flow**: Set up slides in Edit Mode, configure animations in Animation Mode, then Present

## License

Free to use and modify for any purpose.
