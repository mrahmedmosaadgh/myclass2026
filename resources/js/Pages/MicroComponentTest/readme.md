# Micro Component Test

This page serves as a testing ground for various Vue components used in the myclass2026 application. It provides a centralized interface to test, debug, and demonstrate micro-components in isolation.

## 🚀 How to Access

### URL Routes
```
# Main public route
http://localhost:8000/micro-component-test
http://127.0.0.1:8000/micro-component-test

# Alternative short route
http://localhost:8000/ct
http://127.0.0.1:8000/ct

# Science namespace route
http://localhost:8000/science/micro-component-test
http://127.0.0.1:8000/science/micro-component-test
```

### Navigation
1. Start your Laravel development server: `php artisan serve`
2. Open your browser and go to any of the URLs above
3. You'll see the Micro Component Test page with a component switcher dropdown

**Note**: All routes lead to the same page, use whichever URL you prefer. The `/ct` route is a short alias for quick access.

## 📋 Available Components

### Core Components
- **Audio Player** (`AudioPlayer`) - Interactive audio components with replay controls
- **Secure Numpad** (`SecureNumpad`) - POS/Kiosk style numeric input with sound
- **Micro Dropdown** (`MicroDropdown`) - Legacy dropdown component test
- **Question Display** (`QuestionDisplay`) - Teacher/Admin view for live polls
- **Question Input** (`QuestionInput`) - Student view for submitting answers

### Chart Components
- **EChart Component** (`EChartComponent`) - Basic chart implementation
- **EChart Component V2** (`EChartComponent_v2`) - Enhanced chart with more features
- **Test Chart V2** (`TestChartV2`) - Chart testing component
- **Dynamic Tree Editor** (`DynamicTreeEditor`) - Interactive tree structure editor
- **Drag & Drop** (`DragDrop`) - Drag and drop functionality

### Multiplication Components
- **Multiplication Practice** (`mulitp`) - Basic multiplication table practice
- **Multiplication Drag & Drop** (`multip2`) - Drag and match multiplication
- **Multiple Choice Quiz** (`MultipleChoiceQuiz`) - Quiz component with progress tracking
- **Tables Diploma Quiz** (`InputQuiz`) - Time-based multiplication challenge

### Educational Components
- **IXL Line Plot** (`IXLLinePlotExample`) - Educational data visualization
- **Presentation Editor** (`PresentationEditor`) - Slide presentation editor
- **MyTableSchedule** (`MyTableSchedule`) - Schedule timeline component
- **Tasks Pro** (`TaskList`) - Task management component
- **Audio Player Demo** (`AudioPlayerDemo`) - Advanced audio player demonstration

## 🛠️ Adding New Components

### Step 1: Create Component File
1. **Choose a descriptive subfolder** in `/comptest/` based on component type:
   - `/comptest/media/` - Audio/Video players
   - `/comptest/input/` - Form inputs, numpads, dropdowns
   - `/comptest/charts/` - Data visualization components
   - `/comptest/quiz/` - Educational quiz components
   - `/comptest/presentation/` - Slides and presentation tools
   - `/comptest/utility/` - Helper/utility components

2. **Create your component** with a descriptive name:
   ```bash
   # Example: Create a video player component
   touch /resources/js/Pages/MicroComponentTest/comptest/media/VideoPlayer.vue
   ```

### Step 2: Add to components.js
1. Open `/resources/js/Pages/MicroComponentTest/components.js`
2. Import your new component:
   ```javascript
   import VideoPlayer from './comptest/media/VideoPlayer.vue';
   ```
3. Add it to the export list:
   ```javascript
   export {
       // ... existing exports
       VideoPlayer,
   };
   ```

### Step 3: Add to Index.vue Configuration
1. Open `/resources/js/Pages/MicroComponentTest/Index.vue`
2. Add your component to the `componentViews` object:
   ```javascript
   const componentViews = {
       // ... existing components
       videoPlayer: {
           title: 'Video Player',
           component: Components.VideoPlayer,
           props: { /* your component props */ }
       }
   };
   ```

### Step 4: Add to Dropdown Menu
1. Find the dropdown menu section in the template
2. Add a new `q-item` for your component:
   ```vue
   <q-item clickable v-close-popup @click="currentView = 'videoPlayer'">
       <q-item-section avatar>
           <span class="text-xl">�</span>
       </q-item-section>
       <q-item-section>
           <q-item-label>Video Player</q-item-label>
           <q-item-label caption>Interactive video playback component</q-item-label>
       </q-item-section>
   </q-item>
   ```

### Step 5: Update currentViewLabel (Optional)
Add your component to the computed `currentViewLabel` function for proper display in the dropdown button.

## 📁 Recommended Folder Structure

```
comptest/
├── media/                    # Audio/Video components
│   ├── AudioPlayer.vue
│   ├── VideoPlayer.vue
│   └── AudioPlayerDemo.vue
├── input/                    # Input components
│   ├── SecureNumpad/
│   ├── MicroDropdown.vue
│   └── SmartInput.vue
├── realtime/                 # Real-time components
│   ├── QuestionDisplay.vue
│   └── QuestionInput.vue
├── charts/                   # Data visualization
│   ├── EChartComponent.vue
│   ├── DynamicTreeEditor.vue
│   └── DragDrop.vue
├── quiz/                     # Educational quizzes
│   ├── multiplication/
│   └── MultipleChoiceQuiz.vue
├── presentation/             # Presentation tools
│   └── ppt/
├── tasks/                    # Task management
│   └── taskspro/
└── utility/                  # Helper components
    ├── LoadingSpinner.vue
    └── ErrorBoundary.vue
```

## 🏗️ Architecture

### Centralized Import System
- **File**: `components.js`
- **Purpose**: Centralizes all component imports and exports
- **Benefit**: New components are automatically available without modifying Index.vue imports

### Dynamic Component Rendering
- **Configuration Object**: `componentViews` in Index.vue
- **Purpose**: Defines component metadata (title, component reference, props)
- **Benefit**: Single dynamic component renders all views instead of individual template sections

### Component Access Pattern
- **Import**: `import * as Components from './components.js'`
- **Usage**: `<component :is="componentViews[currentView].component" />`
- **Benefit**: Clean, organized component access with automatic prop binding

### Component Organization
Components are organized by category in the `componentViews` configuration:
- Core components (essential UI elements)
- Chart components (data visualization)
- Multiplication components (educational math tools)
- Educational components (learning tools)

## 🎨 Features

### Dynamic Component Rendering
- **Single component renderer** handles all component views
- **Configuration-driven** component display
- **Automatic prop binding** from configuration object
- **90% code reduction** compared to individual view sections

### Real-time Testing
- Live component preview
- Interactive testing environment
- Immediate feedback on component behavior

### Component Switcher
- Dropdown menu for easy navigation
- Visual indicators for each component type
- Smooth transitions between components

### Responsive Design
- Mobile-friendly layout
- Adaptive grid systems
- Touch-compatible interactions

## 🔧 Development Notes

### File Structure
```
MicroComponentTest/
├── Index.vue                 # Main test page
├── components.js            # Centralized component exports
├── readme.md               # This documentation
├── MicroDropdown.vue       # Legacy dropdown component
└── comptest/               # Component test directory
    ├── AudioPlayer.vue
    ├── SecureNumpad/
    ├── realtimetest/
    ├── test1/
    │   ├── charts/
    │   ├── multiplication/
    │   ├── smartscore/
    │   ├── ppt/
    │   ├── taskspro/
    │   └── files_audio_player/
    └── ...
```

### Best Practices
1. **Testing**: Always test components in isolation before integration
2. **Documentation**: Document component props and events
3. **Styling**: Use consistent Tailwind CSS classes
4. **Performance**: Monitor component load times and memory usage

### Troubleshooting
- **Component not showing**: Check if it's exported in `components.js`
- **Import errors**: Verify file paths and component names
- **Styling issues**: Ensure Tailwind CSS classes are properly applied

## 📱 Browser Support

- Chrome/Chromium (Recommended)
- Firefox
- Safari
- Edge

## 🔄 Updates

This page is actively updated as new components are developed. Check the component switcher for the latest additions.

---

**Last Updated**: March 22, 2026  
**Version**: 1.0.0