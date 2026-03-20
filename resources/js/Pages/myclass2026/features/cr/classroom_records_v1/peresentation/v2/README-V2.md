# Presentation Builder V2 - Arrow Indicators & Opacity Toggle

A unique presentation system where you configure element visibility with visual arrow indicators and toggle between 10% and 100% opacity during presentations.

## 🎯 Unique Features of V2

### **Arrow Indicators**
- **Every element** on the slide has a **blue arrow (→)** pointing to it
- Arrows pulse and animate to help you locate elements
- Click arrows **or** elements to select them for configuration

### **Two Opacity States**
- **100% opacity** = Fully visible
- **10% opacity** = Barely visible (ghost/hint mode)
- Elements toggle between these two states on click

### **Simple Configuration**
No animation sequences or complex ordering - just two decisions per element:
1. **Initial State**: Should it start visible (100%) or hidden (10%)?
2. **Clickable**: Can users toggle it during presentation?

## 📊 How It Works

### Visibility Settings Mode

1. **Click "👁 Visibility Settings"** button
2. **Select an element** by clicking:
   - The element itself
   - The blue arrow (→) indicator pointing to it
3. **Configure the element**:

   **Initial State:**
   - ✅ **Start Visible** (100% opacity) 
   - 👻 **Start Hidden** (10% opacity - barely visible)

   **Click Behavior:**
   - 👆 **Clickable** - Users can toggle opacity during presentation
   - 🔒 **Not Clickable** - Stays at initial state forever

### Presentation Mode

- **Clickable elements**: Click to toggle between 10% ↔ 100% opacity
- **Non-clickable elements**: Stay at their initial opacity
- **Smooth transitions**: 0.4s fade between states
- **No sequence**: All elements are independently clickable

## 🎨 Visual Feedback

### In Visibility Settings Mode:
- **Selected element**: Blue border with glow
- **Will start hidden**: Orange diagonal stripes + 30% opacity
- **Will start visible**: Green badge + 100% opacity
- **Arrow indicators**: Pulsing blue circles with →

### In Presentation Mode:
- **10% opacity**: Element is barely visible (ghost/hint)
- **100% opacity**: Element is fully visible
- **Smooth transition**: 0.4s fade when toggling

## 💾 Data Structure

```json
{
  "id": "element-123",
  "type": "text",
  "content": "Hello World",
  "x": 100,
  "y": 100,
  "width": 400,
  "fontSize": 24,
  "color": "#ffffff",
  "startHidden": false,    // true = 10%, false = 100%
  "clickable": true        // Can user toggle it?
}
```

## 🔄 Comparison with Other Versions

| Feature | V2 (This Version) | PowerPoint-like | Simple Show/Hide |
|---------|-------------------|-----------------|------------------|
| Visibility states | 10% ↔ 100% opacity | 0% → 100% fade | display: none/block |
| Selection method | Arrow indicators | Click elements | Click elements |
| Animation sequence | None (all independent) | Numbered order | Numbered order |
| Click behavior | Toggle on/off | Sequential advance | Sequential advance |
| Visual complexity | Medium | High | Low |
| Configuration | 2 choices per element | 3 choices + ordering | 3 choices + ordering |

## 🎯 Best Use Cases

**Perfect for:**
- **Interactive tutorials** - Let users reveal hints (10% → 100%)
- **Answer reveals** - Questions visible, answers at 10% until clicked
- **Progressive diagrams** - Show components as barely visible, reveal on demand
- **Focus control** - Hide distractions (100% → 10%), bring back later
- **Exploration presentations** - Non-linear, user drives the reveal order

**Not ideal for:**
- Linear storytelling (use PowerPoint-like instead)
- Fully hidden elements (use Simple Show/Hide instead)
- Simple on/off toggles with no preview (use Simple version)

## 📝 Example Scenarios

### Scenario 1: Quiz Slide
```
Question (100%, not clickable) - Always visible
Answer A (10%, clickable) - Click to reveal, barely visible hint
Answer B (10%, clickable) - Click to reveal, barely visible hint  
Answer C (10%, clickable) - Click to reveal, barely visible hint
Explanation (10%, clickable) - Click after guessing
```

### Scenario 2: Diagram Breakdown
```
Full diagram (100%, not clickable) - Always visible
Label 1 (10%, clickable) - Ghost until clicked
Label 2 (10%, clickable) - Ghost until clicked
Detail box (10%, clickable) - Ghost until clicked
```

### Scenario 3: Focus Mode
```
Main content (100%, not clickable) - Always visible
Sidebar info (100%, clickable) - Can hide to reduce distraction
Footer notes (100%, clickable) - Can hide to reduce distraction
```

## 🎨 Why 10% Instead of 0%?

The 10% opacity for "hidden" elements serves as a **ghost/hint mode**:
- Users know something is there
- Provides spatial awareness
- Encourages exploration
- Avoids sudden "pop-in" surprise
- Better for learning and discovery

If you want true invisibility, use the **Simple Show/Hide** version instead.

## ⚡ Quick Start

1. **Edit Mode**: Add images and text to your slide
2. **Visibility Settings Mode**: 
   - Click arrow indicators to select elements
   - Set initial state (100% or 10%)
   - Set clickable (yes or no)
3. **Present Mode**: Click elements to toggle opacity

## 🔧 Customization

### Change Opacity Levels
Edit `SlideElementV2.vue`:
```javascript
// Current: 10% and 100%
style.opacity = this.element.startHidden ? 0.1 : 1;

// Alternative: 20% and 100%
style.opacity = this.element.startHidden ? 0.2 : 1;

// Alternative: 0% and 100% (fully hidden)
style.opacity = this.element.startHidden ? 0 : 1;
```

### Change Transition Speed
Edit `SlideElementV2.vue`:
```javascript
// Current: 0.4s
transition: 'opacity 0.4s ease'

// Slower: 0.8s
transition: 'opacity 0.8s ease'

// Instant: 0s
transition: 'opacity 0s'
```

### Arrow Indicator Position
Edit `AnimationEditorV2.vue`:
```javascript
// Current: 30px to the left
left: (element.x - 30) + 'px'

// Further left: 50px
left: (element.x - 50) + 'px'

// To the right instead
left: (element.x + element.width + 10) + 'px'
```

## 📊 Statistics Panel

The Visibility Settings mode includes a summary showing:
- **Total Elements**: Count of all elements on slide
- **Start Visible**: How many at 100% opacity initially
- **Start Hidden**: How many at 10% opacity initially  
- **Clickable**: How many can be toggled during presentation

## 🐛 Known Behaviors

- Elements at 10% opacity are still selectable and visible
- Arrow indicators only appear in Visibility Settings mode
- Clicking background does nothing (only elements respond)
- Non-clickable elements stay at initial opacity forever
- Each slide maintains independent toggle states

## 💡 Tips for Best Results

1. **Use 10% for hints** - Let users know content exists
2. **Make important elements 100% + not clickable** - Ensures visibility
3. **Group related content** - Set similar clickable behavior
4. **Use arrows for quick selection** - Faster than clicking small elements
5. **Preview in Present mode** - Test your opacity settings

## 🎓 Teaching Applications

This mode is excellent for education:
- **Vocabulary**: Word visible (100%), definition hidden (10%)
- **Math**: Problem visible (100%), steps hidden (10%)
- **History**: Event visible (100%), context hidden (10%)
- **Science**: Question visible (100%), answer hidden (10%)

The 10% preview helps students know what to expect without giving away answers!
