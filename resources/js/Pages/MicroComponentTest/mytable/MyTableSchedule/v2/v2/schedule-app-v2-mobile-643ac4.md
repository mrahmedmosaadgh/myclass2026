# Schedule App V2 - Mobile-Optimized with Multiple View Modes

Create a mobile-first version 2 of the schedule app with three switchable layout modes (Card, Table, List) as reusable components.

## Overview

Copy all files from the current MyTableSchedule folder to a new v2 directory and redesign with mobile-first approach, featuring three distinct view modes that users can switch between.

## Implementation Steps

### 1. Directory Structure Setup
- Create `/v2` folder inside MyTableSchedule directory
- Copy all existing files:
  - `MyTableSchedule.vue` → `MyTableScheduleV2.vue`
  - `StandaloneScheduleApp.vue` → `StandaloneScheduleAppV2.vue`
  - All component files from `/components` folder
  - JSON data files (`schedule_data.json`, `schedule_timing.json`)
  - Audio file (`notification1.mp3`)
  - Icon file (`icon.png`)

### 2. Create Three View Mode Components

#### A. Card View Component (`CardView.vue`)
**Mobile-optimized features:**
- Each day as a swipeable card
- Vertical period layout with large touch targets
- Current period highlighted with progress bar
- Smooth swipe gestures between days
- Compact header showing day name and date
- Visual indicators for breaks/activities
- Touch-friendly spacing (min 44px tap targets)

#### B. Table View Component (`TableView.vue`)
**Mobile-optimized features:**
- Responsive table with horizontal scroll
- Sticky day column on left
- Pinch-to-zoom support
- Larger font sizes for readability
- Color-coded periods with clear borders
- Optimized for landscape orientation
- Touch-friendly cell padding

#### C. List View Component (`ListView.vue`)
**Mobile-optimized features:**
- Vertical scrolling list of periods
- Expandable period cards showing details
- Current period at top with countdown
- Large, readable text (min 16px)
- Collapsible sections for each day
- Quick jump to current period button
- Bottom sheet for period details

### 3. View Switcher Component
Create `ViewModeSwitcher.vue`:
- Segmented control with icons for each view
- Smooth transitions between views
- Persist user preference in localStorage
- Mobile-friendly toggle buttons
- Visual feedback on selection

### 4. Update Main V2 Component
Modify `MyTableScheduleV2.vue`:
- Add view mode state management
- Integrate ViewModeSwitcher
- Dynamic component loading based on selected view
- Share data/props across all view modes
- Maintain notification and timing features

### 5. Mobile-First Styling Improvements
- Use CSS Grid and Flexbox for responsive layouts
- Implement touch-friendly spacing (8px minimum)
- Add haptic feedback for interactions
- Optimize for various screen sizes (320px - 768px)
- Dark mode support
- Smooth animations and transitions
- Bottom navigation for better thumb reach
- Floating action button for quick actions

### 6. Enhanced Mobile Features
- Pull-to-refresh functionality
- Swipe gestures for navigation
- Bottom sheet modals instead of popups
- Toast notifications for feedback
- Skeleton loading states
- Offline indicator
- Battery-efficient animations

### 7. Update Routes
Add new route for v2:
- `/my-schedule-app/v2` pointing to StandaloneScheduleAppV2
- Update manifest for v2 with new icons
- Update service worker cache for v2 files

### 8. Standalone App V2 Updates
Modify `StandaloneScheduleAppV2.vue`:
- Mobile-optimized header (collapsible)
- Bottom navigation bar
- Floating install button
- Improved status indicators
- Touch-friendly controls

## Key Mobile Design Principles

1. **Touch Targets**: Minimum 44x44px for all interactive elements
2. **Typography**: Base font size 16px, headings 20-24px
3. **Spacing**: Generous padding (16-24px) for better readability
4. **Navigation**: Bottom-aligned for thumb reach
5. **Feedback**: Visual and haptic feedback for all interactions
6. **Performance**: Lazy loading, optimized animations
7. **Accessibility**: High contrast, readable fonts, ARIA labels

## File Structure After Implementation

```
MyTableSchedule/
├── v2/
│   ├── MyTableScheduleV2.vue (main component)
│   ├── StandaloneScheduleAppV2.vue (standalone wrapper)
│   ├── components/
│   │   ├── CardView.vue (NEW - swipeable cards)
│   │   ├── TableView.vue (enhanced responsive table)
│   │   ├── ListView.vue (NEW - vertical list)
│   │   ├── ViewModeSwitcher.vue (NEW - view toggle)
│   │   ├── ScheduleTimingManager.vue (mobile-optimized)
│   │   └── MobileBottomSheet.vue (NEW - modal component)
│   ├── composables/
│   │   ├── useSwipeGesture.js (NEW - swipe detection)
│   │   ├── useViewMode.js (NEW - view state management)
│   │   └── useMobileOptimizations.js (NEW - mobile utilities)
│   ├── schedule_data.json
│   ├── schedule_timing.json
│   ├── notification1.mp3
│   └── icon.png
```

## Testing Checklist

- [ ] Test on mobile devices (iOS Safari, Chrome Android)
- [ ] Verify swipe gestures work smoothly
- [ ] Check touch target sizes (min 44px)
- [ ] Test in portrait and landscape modes
- [ ] Verify offline functionality
- [ ] Test view mode switching
- [ ] Check notifications on mobile
- [ ] Verify PWA installation on mobile
- [ ] Test with various screen sizes
- [ ] Validate accessibility features

## Benefits

- **Better UX**: Three distinct views for different use cases
- **Mobile-First**: Optimized for touch and small screens
- **Flexible**: Users choose their preferred layout
- **Reusable**: Components can be used independently
- **Modern**: Follows current mobile design patterns
- **Accessible**: Larger touch targets and readable text
