# Enterprise Quiz System - Styling Documentation

## Overview

This directory contains the comprehensive styling system for the Enterprise Quiz System. The styles are organized into modular CSS files that follow a design token approach for consistency and maintainability.

## File Structure

```
styles/
├── index.css              # Master file - imports all styles
├── quiz-base.css          # Design tokens and CSS variables
├── quiz-components.css    # Component-specific styles
├── quiz-animations.css    # Animations and transitions
├── quiz-responsive.css    # Responsive design and breakpoints
└── README.md             # This file
```

## Usage

### In Vue Components

```vue
<style>
@import './styles/index.css';
</style>
```

### In Main Application

```javascript
import './quiz/styles/index.css';
```

## Design System

### Color Palette

All colors are WCAG 2.1 AA compliant with proper contrast ratios:

#### Primary Colors (Blue)
- Used for: Interactive elements, focus states, selected options
- Contrast ratio: 4.5:1 minimum on white background
- Variables: `--quiz-primary-50` through `--quiz-primary-900`

#### Success Colors (Green)
- Used for: Correct answers, progress indicators
- Contrast ratio: 4.5:1 minimum on white background
- Variables: `--quiz-success-50` through `--quiz-success-900`

#### Error Colors (Red)
- Used for: Incorrect answers, validation errors
- Contrast ratio: 4.5:1 minimum on white background
- Variables: `--quiz-error-50` through `--quiz-error-900`

#### Warning Colors (Yellow/Amber)
- Used for: Time warnings, hints
- Contrast ratio: 4.5:1 minimum on white background
- Variables: `--quiz-warning-50` through `--quiz-warning-900`

#### Neutral Colors (Gray)
- Used for: Text, borders, backgrounds
- Contrast ratio: 4.5:1 minimum for text
- Variables: `--quiz-neutral-50` through `--quiz-neutral-900`

### Typography Scale

The system uses a modular typography scale based on 16px base:

```css
--quiz-text-xs: 0.75rem;      /* 12px */
--quiz-text-sm: 0.875rem;     /* 14px */
--quiz-text-base: 1rem;       /* 16px */
--quiz-text-lg: 1.125rem;     /* 18px */
--quiz-text-xl: 1.25rem;      /* 20px */
--quiz-text-2xl: 1.5rem;      /* 24px */
--quiz-text-3xl: 1.875rem;    /* 30px */
--quiz-text-4xl: 2.25rem;     /* 36px */
```

### Spacing System

Based on 8px grid system:

```css
--quiz-space-1: 0.25rem;   /* 4px */
--quiz-space-2: 0.5rem;    /* 8px */
--quiz-space-3: 0.75rem;   /* 12px */
--quiz-space-4: 1rem;      /* 16px */
--quiz-space-5: 1.25rem;   /* 20px */
--quiz-space-6: 1.5rem;    /* 24px */
--quiz-space-8: 2rem;      /* 32px */
--quiz-space-10: 2.5rem;   /* 40px */
--quiz-space-12: 3rem;     /* 48px */
```

### Breakpoints

Mobile-first responsive design:

```css
--quiz-breakpoint-sm: 640px;    /* Small tablets */
--quiz-breakpoint-md: 768px;    /* Tablets */
--quiz-breakpoint-lg: 1024px;   /* Desktops */
--quiz-breakpoint-xl: 1280px;   /* Large desktops */
--quiz-breakpoint-2xl: 1536px;  /* Extra large */
```

## Component Styles

### QuizEngine Container

The main container with responsive max-width:
- Mobile: Full width with padding
- Tablet: 720px max-width
- Desktop: 800px max-width

### Progress Indicator

Visual progress tracking with:
- Animated progress bar fill
- Question counter
- Percentage display
- ARIA live regions for accessibility

### Question Content

Styled question display with:
- Highlighted question text
- HTML content support
- Code formatting
- Emphasis and strong text

### Option Items

Interactive answer options with states:
- Default: Gray border
- Hover: Blue border with slide animation
- Selected: Blue background
- Correct: Green background with glow
- Incorrect: Red background with shake
- Disabled: Reduced opacity

### Navigation Controls

Accessible navigation buttons:
- Previous: Gray background
- Next: Blue background
- Finish: Green background
- Disabled state with reduced opacity
- Touch-friendly 44x44px minimum

## Animations

### Progress Bar
- Smooth width transition (400ms)
- Optional shimmer effect
- Respects reduced motion preference

### Feedback
- Fade-in animation (200ms)
- Scale-in for icons (200ms)
- Bounce for correct answers
- Shake for incorrect answers
- Glow effect for correct options

### Option Selection
- Smooth border/background transitions
- Slide animation on hover
- Pulse animation for selected label

### Page Transitions
- Fade transition between questions
- Slide transition (optional)
- Scale transition (optional)

## Responsive Design

### Mobile (< 640px)
- Single column layout
- Stacked navigation buttons
- Reduced font sizes
- Compact spacing
- Touch-optimized targets (44x44px)
- No hover effects

### Tablet (640px - 1024px)
- Moderate spacing
- Side-by-side navigation
- Medium font sizes
- Subtle hover effects

### Desktop (> 1024px)
- Maximum comfortable width (800px)
- Generous spacing
- Large font sizes
- Enhanced hover effects
- Smooth animations

### Touch Devices
- Minimum 44x44px touch targets
- Increased spacing between elements
- Active state feedback instead of hover
- Disabled transform animations

## Accessibility Features

### WCAG 2.1 AA Compliance
- Color contrast ratios ≥ 4.5:1 for text
- Color contrast ratios ≥ 3:1 for UI components
- Focus indicators on all interactive elements
- ARIA labels and roles
- Keyboard navigation support

### High Contrast Mode
- Increased border widths
- Darker color variants
- Enhanced text contrast

### Reduced Motion
- All animations disabled
- Instant transitions
- No transform effects
- Essential feedback preserved

### Screen Readers
- ARIA live regions for updates
- Descriptive labels
- State announcements
- Progress updates

## Customization

### Changing Colors

Override CSS variables in your component:

```css
.quiz-engine {
  --quiz-primary-500: #your-color;
  --quiz-success-600: #your-color;
}
```

### Changing Spacing

```css
.quiz-engine {
  --quiz-space-4: 1.5rem; /* Increase base spacing */
}
```

### Changing Typography

```css
.quiz-engine {
  --quiz-font-sans: 'Your Font', sans-serif;
  --quiz-text-base: 1.125rem; /* Larger base size */
}
```

### Dark Mode

The system includes dark mode support via `prefers-color-scheme`:

```css
@media (prefers-color-scheme: dark) {
  /* Dark mode colors automatically applied */
}
```

## Browser Support

- Chrome/Edge: Latest 2 versions
- Firefox: Latest 2 versions
- Safari: Latest 2 versions
- iOS Safari: iOS 13+
- Chrome Android: Latest version

### CSS Features Used
- CSS Custom Properties (CSS Variables)
- CSS Grid
- Flexbox
- CSS Animations
- Media Queries
- Container Queries (progressive enhancement)

## Performance

### Optimizations
- GPU-accelerated animations (transform, opacity)
- Will-change hints for animated elements
- Efficient selectors
- Minimal repaints/reflows
- Lazy loading support

### File Sizes
- quiz-base.css: ~8KB
- quiz-components.css: ~12KB
- quiz-animations.css: ~10KB
- quiz-responsive.css: ~10KB
- Total: ~40KB (uncompressed)
- Gzipped: ~8KB

## Testing

### Visual Regression Testing
Test across breakpoints:
- 375px (Mobile)
- 768px (Tablet)
- 1024px (Desktop)
- 1440px (Large Desktop)

### Accessibility Testing
- Keyboard navigation
- Screen reader compatibility
- Color contrast validation
- Focus indicator visibility
- Touch target sizes

### Browser Testing
- Cross-browser compatibility
- High contrast mode
- Reduced motion preference
- Dark mode
- Print styles

## Maintenance

### Adding New Components
1. Add styles to `quiz-components.css`
2. Use existing design tokens
3. Follow naming conventions
4. Add responsive styles to `quiz-responsive.css`
5. Add animations to `quiz-animations.css` if needed

### Updating Colors
1. Modify color variables in `quiz-base.css`
2. Ensure WCAG compliance
3. Test in high contrast mode
4. Test in dark mode

### Adding Animations
1. Add keyframes to `quiz-animations.css`
2. Include reduced motion alternative
3. Use GPU-accelerated properties
4. Keep duration under 400ms

## Resources

- [WCAG 2.1 Guidelines](https://www.w3.org/WAI/WCAG21/quickref/)
- [CSS Custom Properties](https://developer.mozilla.org/en-US/docs/Web/CSS/--*)
- [Responsive Design](https://web.dev/responsive-web-design-basics/)
- [CSS Animations](https://developer.mozilla.org/en-US/docs/Web/CSS/CSS_Animations)

## Support

For issues or questions about the styling system:
1. Check this documentation
2. Review the CSS comments in each file
3. Test with browser DevTools
4. Validate accessibility with axe DevTools

## Version History

### v1.0.0 (Current)
- Initial comprehensive styling system
- WCAG 2.1 AA compliant colors
- Full responsive design
- Complete animation system
- Accessibility features
- Dark mode support
- Print styles
