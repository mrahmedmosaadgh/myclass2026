# 🔧 PDF Navigation Fix - Single Page Display

## ❌ Problem

The PDF viewer was showing **all pages at once** as one long scrollable document instead of showing **one page at a time** with navigation.

### Symptoms:
- ✅ Drawing works on first page
- ❌ All pages visible simultaneously
- ❌ Navigation buttons don't change view
- ❌ Canvas only covers first page

---

## ✅ Root Causes & Fixes

### Issue 1: Wrong Prop Name
**Problem:**
```vue
<!-- ❌ Wrong -->
<VuePdfEmbed
  :page-number="currentPage"
  @num-pages="numPages = $event"
/>
```

**Solution:**
```vue
<!-- ✅ Correct -->
<VuePdfEmbed
  :page="currentPage"
  @loaded="onPDFLoaded"
/>
```

**Explanation:**
- `vue-pdf-embed` uses `:page` not `:page-number`
- Use `@loaded` event to get total pages, not `@num-pages`

---

### Issue 2: Missing Key Prop
**Problem:**
- Component doesn't re-render when page changes
- Canvas stays on first page

**Solution:**
```vue
<VuePdfEmbed
  :key="`page-${currentPage}`"
  :page="currentPage"
/>
<canvas :key="`canvas-${currentPage}`"></canvas>
```

**Explanation:**
- Adding `:key` forces Vue to re-render when page changes
- Both PDF and canvas need unique keys per page

---

### Issue 3: Missing onPDFLoaded Handler
**Problem:**
```javascript
// ❌ Old - event doesn't exist
@num-pages="numPages = $event"
```

**Solution:**
```javascript
// ✅ New - correct event
const onPDFLoaded = (pdf) => {
  numPages.value = pdf.numPages
  console.log('PDF loaded with', numPages.value, 'pages')
}
```

---

### Issue 4: CSS Not Constraining Display
**Problem:**
- No max-width on PDF container
- All pages render vertically

**Solution:**
```css
.page-container { 
  max-width: 100%;
  overflow: hidden;  /* Hide overflow */
}

.pdf-embed canvas {
  max-width: 100% !important;
  height: auto !important;
}
```

---

## 🎯 Complete Fix Applied

### Template Changes
```vue
<div class="page-container" ref="pageContainer">
  <VuePdfEmbed
    :key="`page-${currentPage}`"
    :source="pdfUrl"
    :page="currentPage"
    @loaded="onPDFLoaded"
    @rendered="onPageRendered"
    class="pdf-embed"
  />
  <canvas 
    ref="drawCanvas" 
    class="draw-canvas" 
    :key="`canvas-${currentPage}`"
  ></canvas>
</div>
```

### Script Changes
```javascript
// Add onPDFLoaded handler
const onPDFLoaded = (pdf) => {
  numPages.value = pdf.numPages
  console.log('PDF loaded with', numPages.value, 'pages')
}

// Enhanced onPageRendered
const onPageRendered = async () => {
  await nextTick()
  console.log('Page', currentPage.value, 'rendered')
  setupCanvas()
  restoreDrawings()
}
```

### CSS Changes
```css
.page-container { 
  max-width: 100%;
  overflow: hidden;
  margin: 20px auto;
}

.pdf-embed canvas {
  max-width: 100% !important;
  height: auto !important;
}
```

---

## ✅ Expected Behavior After Fix

### Before Fix:
```
┌─────────────────┐
│ Page 1          │
│                 │
├─────────────────┤
│ Page 2          │
│                 │
├─────────────────┤
│ Page 3          │
│                 │
└─────────────────┘
All pages visible
Drawing only on Page 1
```

### After Fix:
```
Navigation: [Previous] Page 1 of 3 [Next]

┌─────────────────┐
│ Page 1          │
│                 │
│ (Drawing works) │
│                 │
└─────────────────┘
Only current page visible

Click Next →

┌─────────────────┐
│ Page 2          │
│                 │
│ (Drawing works) │
│                 │
└─────────────────┘
Only current page visible
```

---

## 🧪 Testing Checklist

### Test 1: Page Display
- [ ] Upload a multi-page PDF
- [ ] Verify only page 1 is visible
- [ ] Verify no scrolling to other pages

### Test 2: Navigation
- [ ] Click "Next" button
- [ ] Verify page 2 displays (page 1 hidden)
- [ ] Click "Previous" button
- [ ] Verify page 1 displays again

### Test 3: Drawing
- [ ] Draw on page 1
- [ ] Navigate to page 2
- [ ] Draw on page 2
- [ ] Navigate back to page 1
- [ ] Verify page 1 drawings are restored

### Test 4: Canvas Alignment
- [ ] Draw on any page
- [ ] Verify drawing aligns with PDF content
- [ ] Verify canvas covers entire page

### Test 5: Page Counter
- [ ] Verify "Page X of Y" shows correct numbers
- [ ] Verify Previous disabled on page 1
- [ ] Verify Next disabled on last page

---

## 🔍 Debugging Tips

### Check Console Logs
```javascript
// Should see these logs:
"PDF loaded with 5 pages"
"Page 1 rendered"
"Page 2 rendered"  // When navigating
```

### Inspect DOM
```html
<!-- Should see only ONE canvas element -->
<div class="page-container">
  <div class="pdf-embed">
    <canvas></canvas>  <!-- Only one PDF canvas -->
  </div>
  <canvas class="draw-canvas"></canvas>  <!-- Only one draw canvas -->
</div>
```

### Check Props
```javascript
// In Vue DevTools:
currentPage: 1
numPages: 5
pdfUrl: "blob:http://..."
```

---

## 📊 vue-pdf-embed API Reference

### Correct Props
```vue
<VuePdfEmbed
  :source="pdfUrl"           <!-- PDF source (required) -->
  :page="currentPage"        <!-- Page number (1-based) -->
  :width="800"               <!-- Optional: fixed width -->
  :height="1000"             <!-- Optional: fixed height -->
  @loaded="onLoaded"         <!-- Fires when PDF loads -->
  @rendered="onRendered"     <!-- Fires when page renders -->
/>
```

### Events
```javascript
// @loaded event
onLoaded(pdf) {
  console.log(pdf.numPages)  // Total pages
}

// @rendered event
onRendered() {
  // Page finished rendering
  // Good time to setup canvas
}
```

---

## 🎯 Key Takeaways

### Do's ✅
- Use `:page` prop (not `:page-number`)
- Use `@loaded` event (not `@num-pages`)
- Add `:key` to force re-render
- Setup canvas in `@rendered` event
- Store drawings per page

### Don'ts ❌
- Don't use `:page-number` prop
- Don't forget `:key` on components
- Don't setup canvas before render
- Don't assume all pages render at once

---

## 🔮 Future Enhancements

### Possible Improvements:
1. **Page Thumbnails** - Show all pages as thumbnails
2. **Zoom Controls** - Zoom in/out on current page
3. **Rotation** - Rotate pages 90°
4. **Search** - Search text in PDF
5. **Bookmarks** - Jump to specific pages
6. **Print** - Print current page or all pages

---

## 📝 Complete Working Example

```vue
<template>
  <div class="pdf-annotator">
    <div v-if="!pdfUrl" class="upload">
      <input type="file" @change="loadPdf" accept=".pdf" />
    </div>
    
    <div v-else>
      <!-- Navigation -->
      <div class="nav">
        <button @click="prevPage" :disabled="currentPage <= 1">
          Previous
        </button>
        <span>Page {{ currentPage }} of {{ numPages }}</span>
        <button @click="nextPage" :disabled="currentPage >= numPages">
          Next
        </button>
      </div>
      
      <!-- PDF + Canvas -->
      <div class="container" ref="container">
        <VuePdfEmbed
          :key="`page-${currentPage}`"
          :source="pdfUrl"
          :page="currentPage"
          @loaded="pdf => numPages = pdf.numPages"
          @rendered="setupCanvas"
        />
        <canvas 
          ref="canvas" 
          :key="`canvas-${currentPage}`"
        ></canvas>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, nextTick } from 'vue'
import VuePdfEmbed from 'vue-pdf-embed'

const pdfUrl = ref('')
const currentPage = ref(1)
const numPages = ref(1)
const container = ref(null)
const canvas = ref(null)

const loadPdf = (e) => {
  const file = e.target.files[0]
  pdfUrl.value = URL.createObjectURL(file)
}

const setupCanvas = async () => {
  await nextTick()
  // Setup canvas to match PDF size
  const pdfCanvas = container.value?.querySelector('canvas')
  if (pdfCanvas && canvas.value) {
    const rect = pdfCanvas.getBoundingClientRect()
    canvas.value.width = rect.width
    canvas.value.height = rect.height
  }
}

const prevPage = () => {
  if (currentPage.value > 1) currentPage.value--
}

const nextPage = () => {
  if (currentPage.value < numPages.value) currentPage.value++
}
</script>
```

---

**Status:** ✅ Fixed
**Last Updated:** 2025-11-17
**Version:** 2.0
