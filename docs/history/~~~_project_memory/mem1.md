# Project Memory - Presentation Builder V3 Development

**Date:** 2026-03-22  
**AI Model:** Cascade (SWE-1.5)  
**IDE:** Cursor IDE  
**Device:** macOS Development Environment  
**Session Focus:** Presentation Builder V3 Interface Refactoring  

---

## 🎯 Project Overview

**MyClass2026** is a comprehensive educational management system with a focus on classroom records, presentations, and interactive learning tools. The presentation builder is a key component allowing teachers to create and deliver engaging slide presentations.

---

## 🏗️ Architecture Understanding

### **Technology Stack:**
- **Backend:** Laravel 12.x with Inertia.js
- **Frontend:** Vue 3 Composition API with TailwindCSS
- **Build System:** Vite
- **Database:** MySQL with Eloquent ORM
- **Real-time:** Firebase integration for live features

### **Key Directories:**
```
/resources/js/Pages/myclass2026/features/cr/classroom_records_v1/peresentation/v3/
├── PresentationBuilderV3.vue (Main editor interface)
├── components/
│   ├── TopBar.vue (Fixed toolbar with tools)
│   ├── PresenterV3.vue (Presentation mode)
│   ├── SlidePanel.vue (Slide management sidebar)
│   ├── EditorCanvas.vue (Slide editing area)
│   └── ElementNode.vue (Individual slide elements)
/routes/myclass2026/cr/web.php (Presentation routes)
/docs/history/ (Development documentation)
```

---

## 🔧 Key Technical Insights

### **Vue 3 Composition API Patterns:**
- Use `ref()` for reactive primitives
- Use `computed()` for derived state
- Component communication via props and emits
- Lifecycle hooks: `onMounted()`, `onUnmounted()`

### **State Management:**
- Local component state for simple features
- No Vuex/Pinia needed for current scope
- Reactive refs for UI state management

### **Styling Approach:**
- TailwindCSS utility classes
- Dynamic styles with `:style` bindings
- Computed properties for complex styling logic
- Dark theme consistency throughout

---

## 🎨 UI/UX Design Principles

### **Layout Patterns:**
- **Fixed TopBar:** Always accessible tools (64px height)
- **Flexible Main Area:** `pt-16` to account for fixed header
- **Responsive Design:** Mobile and desktop considerations
- **Dark Theme:** Consistent gray-800/gray-900 palette

### **Component Design:**
- **Dropdowns:** Click outside to close, proper z-index (z-50)
- **Tooltips:** Custom styled, hover-based, positioned correctly
- **Transitions:** Smooth color and opacity changes
- **Icons:** Heroicons SVG for consistency

### **Accessibility:**
- Semantic HTML structure
- Proper ARIA labels where needed
- Keyboard navigation support
- High contrast dark theme

---

## 🐛 Common Issues & Solutions

### **Drawing Layer Blocking Elements:**
**Problem:** SVG drawing overlay intercepts clicks on underlying elements  
**Solution:** Conditionally render drawing overlay only when `isDrawingMode` is active

### **Split-Screen State Management:**
**Problem:** Multiple screens sharing state causing conflicts  
**Solution:** Separate state variables for each screen (`screen1SlideIndex`, `screen2SlideIndex`, etc.)

### **Computed Property Performance:**
**Problem:** Complex inline styles causing performance issues  
**Solution:** Move styling logic to computed properties (`containerStyle`)

### **Click Outside Dropdowns:**
**Problem:** Dropdowns staying open when clicking elsewhere  
**Solution:** Add global click listener in `onMounted` and clean up in `onUnmounted`

---

## 📝 Code Patterns to Follow

### **Component Structure:**
```vue
<template>
  <!-- Semantic HTML with proper accessibility -->
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'

// State
const someState = ref('default')

// Computed properties
const computedValue = computed(() => {
  return someState.value.toUpperCase()
})

// Methods
const someMethod = () => {
  // Logic here
}

// Lifecycle
onMounted(() => {
  // Setup
})

onUnmounted(() => {
  // Cleanup
})
</script>
```

### **Event Handling:**
```vue
<!-- Parent -->
<ChildComponent @custom-event="handleEvent" />

<!-- Child -->
<script setup>
const emit = defineEmits(['custom-event'])
const handleClick = () => {
  emit('custom-event', data)
}
</script>
```

### **Dynamic Styling:**
```vue
<template>
  <div :style="computedStyle">
    <!-- Content -->
  </div>
</template>

<script setup>
const computedStyle = computed(() => {
  return {
    width: isLarge.value ? '100%' : '50%',
    backgroundColor: isActive.value ? 'blue' : 'gray'
  }
})
</script>
```

---

## 🚀 Development Workflow

### **File Organization:**
- Keep components focused and single-purpose
- Use descriptive component names
- Group related files in feature directories
- Maintain consistent naming conventions

### **Git Workflow:**
- Use `git add .` to capture all related changes
- Write descriptive commit messages with scope
- Include documentation in commits
- Use force-push carefully when amending

### **Testing Approach:**
- Test both edit and presentation modes
- Verify split-screen functionality
- Check element interactions (clicks, drawing)
- Ensure responsive behavior

---

## ⚠️ Gotchas & Pitfalls

### **Vue 3 Specific:**
- Always import `ref`, `computed` from 'vue'
- Use `.value` to access ref values
- Template refs need `ref()` declarations
- Event emits need proper declaration

### **Laravel/Inertia:**
- Routes return Inertia responses with props
- Use `Head` component for page titles
- Share data via middleware when needed
- Handle CSRF tokens automatically

### **Styling Issues:**
- Tailwind classes need proper configuration
- Dynamic styles require `:style` bindings
- Z-index management for overlays
- Responsive breakpoints in utilities

### **Performance:**
- Computed properties cache results
- Avoid excessive re-renders
- Use `v-show` vs `v-if` appropriately
- Clean up event listeners

---

## 🔮 Future Development Guidelines

### **For New Features:**
1. **Plan the component structure first**
2. **Consider state management needs**
3. **Design for accessibility from start**
4. **Write comprehensive documentation**
5. **Test edge cases thoroughly**

### **Code Quality:**
- Use TypeScript for complex logic (consider migration)
- Implement proper error boundaries
- Add loading states for async operations
- Consider internationalization needs

### **Architecture Decisions:**
- Keep components loosely coupled
- Use prop validation
- Implement proper error handling
- Consider mobile-first design

---

## 📚 Key Resources

### **Documentation:**
- `/docs/history/` - Development history and decisions
- Component files contain inline comments
- Route files show data flow
- History files track feature evolution

### **Learning Resources:**
- Vue 3 Composition API documentation
- Inertia.js Laravel integration guide
- TailwindCSS utility reference
- Laravel routing and middleware docs

---

## 🎯 Session-Specific Learnings

### **Presentation Builder V3 Refactor:**
- **Split-screen independence** requires separate state management
- **Drawing interactions** need careful layering and event handling
- **UI consolidation** improves user experience significantly
- **Computed properties** are essential for complex styling logic
- **TopBar integration** provides better accessibility

### **Best Practices Discovered:**
- Always clean up event listeners to prevent memory leaks
- Use semantic HTML for better accessibility
- Implement click-outside handlers for dropdowns
- Maintain consistent design patterns across components
- Document architectural decisions for future reference

---

## 💭 Final Notes

This project demonstrates modern web development with Vue 3, Laravel, and Inertia.js. The presentation builder showcases complex state management, real-time interactions, and responsive design principles. Future developers should focus on maintaining the established patterns while building new features.

**Key Success Factors:**
- Consistent code organization
- Comprehensive documentation
- Thoughtful component design
- Regular refactoring and cleanup
- User-focused design decisions

**Remember:** The goal is creating an intuitive, powerful tool for educators to create engaging presentations while maintaining code quality and developer experience.
