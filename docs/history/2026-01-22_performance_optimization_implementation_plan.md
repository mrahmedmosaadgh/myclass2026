# Performance Optimization Implementation Plan

**Date:** 2026-01-22  
**Project:** MyClass LMS  
**Target:** Reduce `/reward_sys` page payload from 10MB+ to ~2-3MB

---

## 🎯 Objectives

1. **Immediate Impact**: Reduce initial page load by 70-80%
2. **User Experience**: Maintain all functionality while improving perceived performance
3. **Maintainability**: Use standard Vue/Vite patterns for long-term sustainability
4. **Scalability**: Create a blueprint for optimizing other heavy pages

---

## 📋 Implementation Phases

### **Phase 1: Component-Level Code Splitting** 
**Priority:** 🔴 Critical  
**Estimated Impact:** 📉 70% payload reduction  
**Effort:** ~2-3 hours

#### Files to Modify:
1. **`reward_sys.vue`** (Line ~1218)
   - Convert `TopLeaderboard` to async component
   - Convert `BehaviorIncidents` to async component
   - Convert `StudentGrouping` to async component

2. **`TopLeaderboard.vue`** (Line ~163)
   - Convert `CertificateGenerator` to async component

3. **`CertificateGenerator.vue`** (Lines ~244-245)
   - Move `jsPDF` and `html2canvas` imports inside `generatePDF()` function
   - Use dynamic `import()` statements

#### Implementation Details:

**Step 1.1: Update `reward_sys.vue`**
```javascript
// Replace static imports (lines ~1218-1223)
import { defineAsyncComponent } from 'vue'

const TopLeaderboard = defineAsyncComponent(() => 
  import('./reward_sys_comp/TopLeaderboard.vue')
)

const BehaviorIncidents = defineAsyncComponent(() => 
  import('./reward_sys_comp/BehaviorIncidents.vue')
)

const StudentGrouping = defineAsyncComponent(() => 
  import('./reward_sys_comp/StudentGrouping.vue')
)

// Keep these as static (they're lightweight and always visible):
// - PeriodSelectionRefactored
// - ClassroomSelection
// - StudentCard
```

**Step 1.2: Update `TopLeaderboard.vue`**
```javascript
// Replace line 163
import { defineAsyncComponent } from 'vue'

const CertificateGenerator = defineAsyncComponent(() => 
  import('./CertificateGenerator.vue')
)
```

**Step 1.3: Update `CertificateGenerator.vue`**
```javascript
// Remove lines 244-245:
// import html2canvas from 'html2canvas'
// import jsPDF from 'jspdf'

// Update generatePDF function (line ~419):
async function generatePDF() {
  generating.value = true
  isEditMode.value = false
  
  try {
    // Dynamic imports - only load when generating PDF
    const [{ default: html2canvas }, { default: jsPDF }] = await Promise.all([
      import('html2canvas'),
      import('jspdf')
    ])
    
    const element = certificateRef.value
    if (!element) throw new Error('Certificate element not found')

    const pdf = new jsPDF({
      orientation: 'landscape',
      unit: 'px',
      format: [1123, 794]
    })

    // ... rest of existing logic
  } catch (e) {
    console.error(e)
    $q.notify({ message: 'Failed to generate PDF', color: 'negative' })
  } finally {
    generating.value = false
    currentStudentIndex.value = 0
  }
}
```

**Step 1.4: Add Loading States**
```vue
<!-- In reward_sys.vue, update dialog sections -->
<q-dialog v-model="showLeaderboard" maximized>
  <q-card>
    <q-card-section class="p-0">
      <Suspense>
        <template #default>
          <TopLeaderboard 
            :students="students" 
            :student-behaviors="studentBehaviors"
            :period-code="periodCode"
            :date="selectedDate"
            :school-logo="schoolLogo"
          />
        </template>
        <template #fallback>
          <div class="flex items-center justify-center h-96">
            <q-spinner size="xl" color="primary" />
            <p class="ml-4 text-gray-600">Loading leaderboard...</p>
          </div>
        </template>
      </Suspense>
    </q-card-section>
  </q-card>
</q-dialog>
```

---

### **Phase 2: Utility Extraction & Dynamic Imports**
**Priority:** 🟠 High  
**Estimated Impact:** 📉 15% additional reduction  
**Effort:** ~1-2 hours

#### New Files to Create:

**`resources/js/utils/pdfGenerator.js`**
```javascript
/**
 * PDF Generation Utility
 * Dynamically loads heavy dependencies only when needed
 */

export async function generateCertificatePDF(element, options = {}) {
  const { default: html2canvas } = await import('html2canvas')
  const { default: jsPDF } = await import('jspdf')
  
  const canvas = await html2canvas(element, {
    scale: 2,
    useCORS: true,
    logging: false,
    allowTaint: true,
    ...options.canvasOptions
  })
  
  const imgData = canvas.toDataURL('image/png')
  const pdf = new jsPDF({
    orientation: 'landscape',
    unit: 'px',
    format: [1123, 794],
    ...options.pdfOptions
  })
  
  pdf.addImage(imgData, 'PNG', 0, 0, 1123, 794)
  return pdf
}

export async function captureElementAsImage(element, options = {}) {
  const { default: html2canvas } = await import('html2canvas')
  
  const canvas = await html2canvas(element, {
    scale: 2,
    useCORS: true,
    logging: false,
    ...options
  })
  
  return canvas.toDataURL('image/png')
}
```

**`resources/js/utils/audioManager.js`**
```javascript
/**
 * Audio Manager - Lazy load sound effects
 */

const audioCache = new Map()

export async function playSound(soundKey, soundFiles) {
  if (!audioCache.has(soundKey)) {
    const audio = new Audio(soundFiles[soundKey])
    await audio.load()
    audioCache.set(soundKey, audio)
  }
  
  const audio = audioCache.get(soundKey)
  audio.currentTime = 0
  return audio.play().catch(e => console.log('Sound play error:', e))
}

export function preloadSounds(soundFiles) {
  Object.entries(soundFiles).forEach(([key, src]) => {
    const audio = new Audio(src)
    audio.load()
    audioCache.set(key, audio)
  })
}
```

#### Update `reward_sys.vue` to use utilities:
```javascript
import { playSound, preloadSounds } from '@/utils/audioManager'

// In onMounted, replace audio preloading:
onMounted(() => {
  // Optional: preload on idle
  if ('requestIdleCallback' in window) {
    requestIdleCallback(() => preloadSounds(soundFiles))
  }
})

// Update playSound function:
const playSoundEffect = (type) => playSound(type, soundFiles)
```

---

### **Phase 3: Vite Configuration Enhancement**
**Priority:** 🟡 Medium  
**Estimated Impact:** ⚡ Better caching, faster rebuilds  
**Effort:** ~30 minutes

#### Update `vite.config.js`:
```javascript
// In rollupOptions.output.manualChunks:
manualChunks: {
  // Core framework (always needed)
  'framework-core': ['vue', '@inertiajs/vue3', 'pinia'],
  
  // UI libraries (frequently used)
  'ui-framework': ['quasar', 'vue3-toastify'],
  
  // Internationalization
  'i18n': ['vue-i18n', 'ziggy-js'],
  
  // Network utilities
  'network': ['axios', 'nprogress'],
  
  // Heavy on-demand libraries (separate chunks for better caching)
  'pdf-tools': ['jspdf', 'html2canvas', 'html2pdf.js'],
  'charts': ['echarts', 'vue-echarts'],
  'editors': ['@ckeditor/ckeditor5-build-classic', '@tiptap/starter-kit'],
  
  // Specialized features (loaded only when needed)
  'ocr': ['tesseract.js'],
  'barcode': ['@ericblade/quagga2', '@zxing/library'],
  'firebase': ['firebase'],
  'ai': ['openai', '@google/generative-ai'],
  
  // Document processing
  'documents': ['xlsx', 'mammoth', 'vue-pdf-embed'],
  
  // Utilities
  'utils': ['date-fns', 'lodash', 'dompurify'],
}
```

---

### **Phase 4: Route & Asset Optimization**
**Priority:** 🟢 Low  
**Estimated Impact:** ⚡ 5-10% reduction in HTML size  
**Effort:** ~1 hour

#### 4.1: Ziggy Route Filtering

**Create `resources/js/plugins/ziggyOptimizer.js`:**
```javascript
import { Ziggy } from '@/ziggy'

export function getFilteredRoutes(section = null) {
  if (!section) return Ziggy.routes
  
  const routePatterns = {
    admin: /^(admin\.|dashboard\.|reward-sys\.|qu-)/,
    student: /^(student\.|exam\.|lesson\.|my-)/,
    teacher: /^(teacher\.|classroom\.|reward-sys\.|qu-)/,
  }
  
  const pattern = routePatterns[section]
  if (!pattern) return Ziggy.routes
  
  return Object.keys(Ziggy.routes).reduce((acc, routeName) => {
    if (pattern.test(routeName) || ['home', 'login', 'logout'].includes(routeName)) {
      acc[routeName] = Ziggy.routes[routeName]
    }
    return acc
  }, {})
}
```

**Update `app.blade.php` (Laravel):**
```php
@php
    $userRole = auth()->user()?->role ?? 'guest';
    $routeSection = match($userRole) {
        'admin' => 'admin',
        'teacher' => 'teacher',
        'student' => 'student',
        default => null
    };
@endphp

@routes($routeSection)
```

#### 4.2: Image Optimization

**Add to `reward_sys.vue` for student avatars:**
```vue
<q-avatar size="80px" class="ring-4 ring-yellow-400/60">
  <img 
    :src="student.avatar || getPlaceholder(student.name)" 
    loading="lazy"
    decoding="async"
    class="object-cover" 
  />
</q-avatar>
```

---

## 📊 Expected Results

| Metric | Before | After Phase 1 | After All Phases |
|--------|--------|---------------|------------------|
| Initial Bundle Size | ~10MB | ~2.5MB | ~2MB |
| Time to Interactive | ~8s | ~2.5s | ~2s |
| Lighthouse Score | 45 | 75 | 85+ |
| First Contentful Paint | ~3s | ~1s | ~0.8s |

---

## ✅ Testing Checklist

After each phase:

- [ ] Run `npm run build` successfully
- [ ] Test reward system page loads without errors
- [ ] Verify certificate generation still works
- [ ] Check leaderboard displays correctly
- [ ] Test behavior recording functionality
- [ ] Verify audio playback works
- [ ] Run `npm run analyze` to confirm bundle sizes
- [ ] Test on slow 3G connection (Chrome DevTools)
- [ ] Check browser console for errors

---

## 🚀 Deployment Strategy

1. **Development Testing**: Implement and test locally
2. **Staging Deployment**: Deploy to staging environment
3. **Performance Monitoring**: Use Lighthouse CI or similar
4. **Gradual Rollout**: Deploy to production during low-traffic period
5. **Monitoring**: Watch for errors in production logs
6. **Rollback Plan**: Keep previous build artifacts for 48 hours

---

## 📝 Future Optimizations (Beyond This Plan)

1. **Service Worker**: Implement offline caching for static assets
2. **Image CDN**: Move uploaded images to CDN with automatic WebP conversion
3. **Database Indexing**: Optimize reward system queries
4. **Redis Caching**: Cache student behavior summaries
5. **Preloading**: Add `<link rel="prefetch">` for likely next pages

---

## 🔧 Maintenance Notes

- **Bundle Analysis**: Run `npm run analyze` monthly to catch regressions
- **Dependency Updates**: Check for lighter alternatives quarterly
- **Code Reviews**: Ensure new features use async components when appropriate
- **Documentation**: Update this plan as new optimizations are discovered

---

## 📞 Support & Questions

If issues arise during implementation:
1. Check browser console for specific error messages
2. Verify Vite dev server is running (`npm run dev`)
3. Clear browser cache and rebuild (`npm run build:clean`)
4. Review Vite documentation on code splitting
5. Consult Vue 3 async component documentation
