# Visibility Editor - 3 Options System

Complete visibility control system with dropdown menus and side panel, exactly as shown in your screenshot.

## 🎯 Three Visibility Options

### **Option 1: Hidden & Clickable** 👻
- **Initial State**: Starts at **0.1 opacity** (10% - barely visible)
- **Click Action**: Shows at **1.0 opacity** (100% - fully visible)
- **Click Again**: Toggles back to 0.1 opacity
- **Visual Indicator**: Orange dashed border + "start HIDDEN (10% opacity)" badge
- **Use Case**: Answers, hints, reveals, progressive disclosure

### **Option 2: Shown & Clickable** 👁
- **Initial State**: Starts at **1.0 opacity** (100% - fully visible)
- **Click Action**: Hides at **0.1 opacity** (10% - barely visible)
- **Click Again**: Toggles back to 1.0 opacity
- **Visual Indicator**: Green solid border + "start SHOWN (100% opacity)" badge
- **Use Case**: Optional content, sidebars, distractions to hide

### **Option 3: Moveable Element** 🔄
- **Initial State**: Always at **1.0 opacity** (100% - fully visible)
- **Click & Drag**: Can be repositioned anywhere on the slide
- **No Toggle**: Stays visible, position changes instead
- **Visual Indicator**: Blue solid border + "MOVEABLE element" badge
- **Use Case**: Interactive diagrams, rearrangeable components

## 🎨 User Interface

### **Dropdown Menu on Each Element**
- **⋮ Button**: Blue circular button at top-right corner
- **3 Options**: Numbered 1, 2, 3 with descriptions
- **Active Indicator**: Checkmark (✓) shows selected option
- **Auto-close**: Menu closes after selection

### **Right Side Panel**
- **Selected Element Info**: Shows element type (Image/Text)
- **Visual Cards**: Three large cards for easy selection
- **Current Behavior Box**: Explains what will happen
- **Dark Theme**: Matches professional presentation tools

## 📊 Data Structure

```javascript
{
  id: "element-123",
  type: "text",
  content: "Sample text",
  x: 100,
  y: 100,
  width: 400,
  fontSize: 24,
  color: "#1f2937",
  
  // Visibility Configuration
  visibilityOption: "hidden-clickable",  // or "shown-clickable" or "moveable"
  startHidden: true,                     // For option 1
  clickable: true,                       // For options 1 & 2
  moveable: false                        // For option 3
}
```

## 🎬 How It Works

### **In Visibility Settings Mode:**

1. **Click an element** on the slide
2. Element gets blue glow outline
3. Right panel shows element details
4. Click **⋮ button** to open dropdown OR click cards in side panel
5. Choose one of 3 options:
   - **1**: Hidden & Clickable
   - **2**: Shown & Clickable  
   - **3**: Moveable Element
6. Badge and border update immediately
7. See preview of behavior in info box

### **In Presentation Mode:**

**Option 1 Elements (Hidden & Clickable):**
```
Initial: 0.1 opacity (barely visible ghost)
↓ Click
Show: 1.0 opacity (fully visible)
↓ Click again
Hide: 0.1 opacity (back to ghost)
```

**Option 2 Elements (Shown & Clickable):**
```
Initial: 1.0 opacity (fully visible)
↓ Click
Hide: 0.1 opacity (barely visible)
↓ Click again
Show: 1.0 opacity (back to visible)
```

**Option 3 Elements (Moveable):**
```
Always: 1.0 opacity (fully visible)
↓ Click and drag
Move: Repositions to new location
↓ Release
Stay: Remains at new position
```

## 🎯 Use Cases

### **Educational Quiz**
```
Question text → Option 2 (always visible)
Answer A → Option 1 (hidden, click to reveal)
Answer B → Option 1 (hidden, click to reveal)
Answer C → Option 1 (hidden, click to reveal)
Explanation → Option 1 (hidden, click to reveal)
```

### **Interactive Diagram**
```
Base diagram → Option 2 (always visible)
Label 1 → Option 3 (moveable to correct position)
Label 2 → Option 3 (moveable to correct position)
Label 3 → Option 3 (moveable to correct position)
```

### **Focus Presentation**
```
Main content → Option 2 (always visible)
Side notes → Option 2 (visible, can hide for focus)
References → Option 2 (visible, can hide for focus)
Extra details → Option 1 (hidden, reveal if needed)
```

## 🎨 Visual Indicators

### **In Editor:**

| Option | Border | Opacity | Badge Color | Badge Text |
|--------|--------|---------|-------------|------------|
| Option 1 | Orange dashed | 30% | Orange | start HIDDEN (10% opacity) |
| Option 2 | Green solid | 100% | Green | start SHOWN (100% opacity) |
| Option 3 | Blue solid | 100% | Blue | MOVEABLE element |

### **In Presentation:**

| Option | Cursor | Hover Effect | Interaction |
|--------|--------|--------------|-------------|
| Option 1 | Pointer | Brightness +5% | Click to toggle |
| Option 2 | Pointer | Brightness +5% | Click to toggle |
| Option 3 | Move | Shadow + indicator | Drag to reposition |

## ⚙️ Technical Details

### **Opacity Transitions**
- **Smooth fade**: 0.4s ease transition
- **Range**: 0.1 (barely visible) to 1.0 (fully visible)
- **Why 0.1 not 0**: Provides spatial awareness, shows "something is there"

### **Drag & Drop (Option 3)**
- **Smooth movement**: Position transitions with 0.3s ease
- **No transition while dragging**: Instant follow for responsiveness
- **Shadow feedback**: Increases during drag
- **Constrained**: Stays within slide boundaries

### **State Management**
```javascript
elementStates: {
  "element-123": {
    visible: true,    // For options 1 & 2
    x: 250,          // For option 3 (moved position)
    y: 180           // For option 3 (moved position)
  }
}
```

## 🔧 Customization

### Change Opacity Levels

Edit `SlidePresenterFinal.vue`:
```javascript
// Current: 0.1 and 1.0
style.opacity = isVisible ? 1 : 0.1;

// Alternative: 0.2 and 1.0
style.opacity = isVisible ? 1 : 0.2;

// Alternative: 0 and 1.0 (fully hidden)
style.opacity = isVisible ? 1 : 0;
```

### Add 4th Option

Edit `VisibilityEditorFinal.vue` dropdown menu:
```html
<button 
  class="dropdown-option"
  :class="{ 'selected': element.visibilityOption === 'option-4' }"
  @click="setVisibilityOption(element.id, 'option-4')"
>
  <div class="option-content">
    <div class="option-header">
      <span class="option-number">4</span>
      <span class="option-name">Your Custom Option</span>
    </div>
    <div class="option-description">
      Description of what this option does
    </div>
  </div>
  <span v-if="element.visibilityOption === 'option-4'" class="checkmark">✓</span>
</button>
```

### Change Colors

```css
/* Option 1 - Hidden (Orange) */
.visibility-badge.hidden-clickable {
  background: #fb923c;
  color: #7c2d12;
}

/* Option 2 - Shown (Green) */
.visibility-badge.shown-clickable {
  background: #4ade80;
  color: #14532d;
}

/* Option 3 - Moveable (Blue) */
.visibility-badge.moveable {
  background: #3b82f6;
  color: #1e3a8a;
}
```

## 📱 Responsive Behavior

- **Dropdown menu**: Min-width 380px, adapts to content
- **Side panel**: Fixed 400px width (can be made responsive)
- **Touch support**: All interactions work with touch
- **Mobile**: Recommended minimum screen width 1024px

## ⌨️ Keyboard Shortcuts

**In Presentation Mode:**
- `←` Left arrow: Previous slide
- `→` Right arrow: Next slide
- `ESC`: Exit presentation
- Click elements: Toggle visibility (options 1 & 2)
- Click & drag: Move elements (option 3)

## 🐛 Known Behaviors

- **Option 1 elements**: Start barely visible (0.1 opacity preview)
- **Option 2 elements**: Start fully visible (1.0 opacity)
- **Option 3 elements**: Always visible, position changes
- **Dropdown closes**: After selection or clicking outside
- **State resets**: When changing slides
- **Smooth transitions**: 0.4s for opacity, 0.3s for position

## 💡 Best Practices

1. **Use Option 1** for content you want to reveal (answers, details)
2. **Use Option 2** for content you might want to hide (notes, sidebars)
3. **Use Option 3** for interactive elements (drag-to-match, positioning)
4. **Consistent patterns**: Use same option for similar elements
5. **Test before presenting**: Preview all interactions
6. **Clear badges**: Help you remember what each element does

## 🎓 Educational Applications

### **Language Learning**
- Word (Option 2) + Definition (Option 1)
- Sentence (Option 2) + Translation (Option 1)

### **Math Problems**
- Problem (Option 2) + Steps (Option 1) + Answer (Option 1)

### **Science Diagrams**
- Diagram (Option 2) + Labels (Option 3 - drag to match)

### **History Timeline**
- Events (Option 2) + Details (Option 1)

## 📦 Integration

Works seamlessly with:
- ✅ SlideEditor (for creating content)
- ✅ SlidePresenterFinal (for presenting)
- ✅ PresentationBuilder (main app)
- ✅ JSON export/import (preserves all settings)

## 🚀 Performance

- **Lightweight**: No heavy libraries
- **Smooth**: GPU-accelerated CSS transitions
- **Reactive**: Vue 3 reactivity system
- **Efficient**: Only selected elements render dropdown

## 📄 Files

- **VisibilityEditorFinal.vue** - Main editor component with dropdown & panel
- **SlidePresenterFinal.vue** - Presentation mode with all 3 behaviors
- **README-FINAL.md** - This comprehensive documentation

Perfect for creating interactive, educational, and professional presentations!
