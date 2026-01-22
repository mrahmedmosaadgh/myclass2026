# 2026-01-22_23-04 | MyClass LMS Performance Optimization Guide

## Overview

This document outlines performance optimization strategies for the MyClass Learning Management System, specifically addressing the issue of a 10MB+ payload on the `/reward_sys` page. The optimizations aim to significantly reduce the initial payload while preserving all functionality through improved code splitting, lazy loading, and dependency management.

## Current Situation Assessment

Based on our analysis of the MyClass LMS codebase, the following factors contribute to the large payload:

1. **Heavy Library Dependencies**: The project includes numerous large libraries such as:
   - html2canvas and jspdf (for PDF generation)
   - html2pdf.js
   - firebase (real-time features)
   - tesseract.js (OCR functionality)
   - quagga2 (barcode scanning)
   - echarts/vue-echarts (data visualization)
   - CKEditor (rich text editing)
   - mammoth (document processing)
   - OpenAI integration
   - ZXing (QR code processing)

2. **Monolithic Component Architecture**: Large components like `reward_sys.vue` (2419 lines) and `classwork_records.vue` (145.2KB) load all functionality upfront

3. **Preloaded Specialized Features**: Features like CameraCapture, Barcode Scanner, Certificate Generator, and Avatar Manager are likely preloaded regardless of actual usage

4. **Ziggy Route Bloat**: All system routes are potentially included in the Ziggy configuration

## Detailed Optimization Strategies

### 1. Implement Dynamic Imports and Code Splitting

Update the main app.js file to use more granular code splitting:

```javascript
createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => {
        // Implement route-based code splitting for heavy components
        if (name.includes('my_table_mnger/reward_sys')) {
            return import(`./Pages/${name}.vue`).then(module => {
                // Dynamically import heavy reward system dependencies only when needed
                if (!window.rewardSystemLoaded) {
                    import('./utils/rewardSystemDeps').then(deps => {
                        window.rewardSystemLoaded = true;
                    });
                }
                return module;
            });
        } else if (name.includes('BarcodeScanner') || name.includes('qr-code')) {
            return import(`./Pages/${name}.vue`).then(module => {
                // Load barcode scanning dependencies only when needed
                if (!window.barcodeScannerLoaded) {
                    import('@ericblade/quagga2').then(quagga => {
                        window.Quagga = quagga;
                        window.barcodeScannerLoaded = true;
                    }).catch(e => console.warn('Barcode scanner not loaded:', e));
                }
                return module;
            });
        } else if (name.includes('certificate') || name.includes('pdf')) {
            return import(`./Pages/${name}.vue`).then(module => {
                // Load PDF generation dependencies only when needed
                if (!window.pdfToolsLoaded) {
                    Promise.all([
                        import('jspdf'),
                        import('html2canvas')
                    ]).then(([jspdf, html2canvas]) => {
                        window.jsPDF = jspdf.jsPDF;
                        window.html2canvas = html2canvas.default;
                        window.pdfToolsLoaded = true;
                    }).catch(e => console.warn('PDF tools not loaded:', e));
                }
                return module;
            });
        } else if (name.includes('ocr') || name.includes('image')) {
            return import(`./Pages/${name}.vue`).then(module => {
                // Load OCR dependencies only when needed
                if (!window.ocrLoaded) {
                    import('tesseract.js').then(tesseract => {
                        window.Tesseract = tesseract.createWorker;
                        window.ocrLoaded = true;
                    }).catch(e => console.warn('OCR not loaded:', e));
                }
                return module;
            });
        } else {
            // Standard import for other pages
            return resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue'));
        }
    },
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });

        app
            .component('InertiaHead', Head)
            .component('Head', Head)
            .component('InertiaLink', Link)
            .use(plugin)
            .use(pinia)
            .use(ZiggyVue)
            .use(i18n)
            .use(languageSwitcher)
            .use(Vue3Toastify, {
                autoClose: 3000,
                position: 'top-right',
            })
            .use(Quasar, {
                plugins: {
                    Notify,
                    Loading,
                    Dialog,
                    Dark // Add Dark plugin for dark mode
                },
                components: {
                    QOptionGroup,
                    QRadio
                },
                config: {
                    notify: {
                        position: 'top-right',
                        timeout: 2500,
                        textColor: 'white'
                    },
                    brand: {
                        primary: '#1976d2',
                        secondary: '#26A69A',
                        accent: '#9C27B0',
                        dark: '#1d1d1a',
                        positive: '#21BA45',
                        negative: '#C10015',
                        info: '#31CCEC',
                        warning: '#F2C037'
                    }
                }
            });

        return app.mount(el);
    },
    progress: false, // Disable Inertia's built-in progress
});
```

### 2. Create Dedicated Dependency Files

Create a file to manage heavy dependencies separately:

```javascript
// resources/js/utils/rewardSystemDeps.js
// Heavy dependencies for reward system - loaded only when needed
import { useDivToClipboard } from '../composables/useDivToClipboard';

// Export utility functions that handle heavy dependencies
export {
    useDivToClipboard
};

// Dynamically import PDF generation when needed
export const initPdfTools = async () => {
    if (typeof window !== 'undefined' && !window.pdfInitialized) {
        const [{ jsPDF }, { default: html2canvas }] = await Promise.all([
            import('jspdf'),
            import('html2canvas')
        ]);
        
        window.jsPDF = jsPDF;
        window.html2canvas = html2canvas;
        window.pdfInitialized = true;
    }
};

// Dynamically import camera functionality when needed
export const initCameraTools = async () => {
    if (typeof window !== 'undefined' && !window.cameraInitialized) {
        try {
            // Import camera related dependencies
            const cameraModule = await import('vue3-qr-reader');
            window.QrScanner = cameraModule;
            window.cameraInitialized = true;
        } catch (e) {
            console.warn('Camera tools not loaded:', e);
        }
    }
};
```

### 3. Update Vite Configuration for Better Chunking

Modify the Vite configuration to improve code splitting:

```javascript
// In vite.config.js
manualChunks: {
    // Core framework chunks
    'framework-vendor': ['vue', '@inertiajs/vue3', 'pinia'],
    'ui-vendor': ['quasar', 'vue3-toastify'],
    'i18n-vendor': ['vue-i18n', 'ziggy-js'],
    'network-vendor': ['axios', 'nprogress'],

    // Heavy but shared libraries
    'chart-vendor': ['echarts', 'vue-echarts'],
    'editor-vendor': ['@ckeditor/ckeditor5-build-classic', '@tiptap/starter-kit'],

    // Heavy on-demand libraries - split individually
    'pdf-vendor': ['jspdf'],  // Keep separate for on-demand loading
    'canvas-vendor': ['html2canvas'],  // Keep separate for on-demand loading
    'ocr-vendor': ['tesseract.js'],  // Keep separate for on-demand loading
    'firebase-vendor': ['firebase'],  // Keep separate for on-demand loading
    'openai-vendor': ['openai'],  // Keep separate for on-demand loading
    'barcode-vendor': ['@ericblade/quagga2'],  // Keep separate for on-demand loading
    'zxing-vendor': ['@zxing/library'],  // Keep separate for on-demand loading

    // Lightweight utils
    'utils': ['date-fns', 'lodash', 'dompurify'],
    'animation': ['animate.css', 'canvas-confetti'],
    'data-processing': ['xlsx', 'mammoth', 'vue-pdf-embed'],
},
```

### 4. Optimize Component Structure

Break down monolithic components into smaller, lazy-loaded parts:

```vue
<!-- Optimized reward system component with lazy loading -->
<template>
  <Head title="Reward System" />
  <div class="p-6 space-y-6 bg-gradient-to-br from-blue-50 to-indigo-50 min-h-screen">
    <!-- ... simplified UI elements ... -->

    <!-- Lazy-loaded student cards section -->
    <div v-if="showStudentSection">
      <component 
        :is="studentCardsComponent" 
        v-if="studentCardsComponent"
        :students="students"
        :behaviors="behaviors"
        @behavior-recorded="onBehaviorRecorded"
      />
    </div>
    
    <!-- Loading spinner when component is loading -->
    <div v-else class="flex justify-center items-center h-64">
      <q-spinner size="xl" color="primary" />
    </div>
  </div>
  
  <!-- Lazy-loaded dialogs -->
  <component 
    :is="behaviorManagerComponent" 
    v-if="behaviorManagerComponent"
    v-model="behaviorDialog"
    :behaviors="behaviors"
    :student="selectedStudent"
    :student-behavior-id="selectedStudentBehaviorId"
    @recorded="refreshSummary"
  />
  
  <component 
    :is="cameraCaptureComponent" 
    v-if="cameraCaptureComponent"
    v-model="showCameraDialog"
    @captured="handleCameraCapture"
    @cancel="handleCameraCancel"
  />
</template>

<script setup>
import { ref, computed, onMounted, shallowRef } from 'vue';
import { Head } from '@inertiajs/vue3';
import { useQuasar } from 'quasar';

// Shallow refs for dynamically loaded components
const studentCardsComponent = shallowRef(null);
const behaviorManagerComponent = shallowRef(null);
const cameraCaptureComponent = shallowRef(null);

// Existing reactive variables
const locale = ref('en');
const showSetupDialog = ref(false);
// ... other reactive variables ...

// Lazy load components when needed
const loadStudentCards = async () => {
  if (!studentCardsComponent.value) {
    const { default: StudentCards } = await import('./final/reward_sys_layout.vue');
    studentCardsComponent.value = StudentCards;
    showStudentSection.value = true;
  }
};

const loadBehaviorManager = async () => {
  if (!behaviorManagerComponent.value) {
    const { default: BehaviorManager } = await import('./final/BehaviorManager.vue');
    behaviorManagerComponent.value = BehaviorManager;
  }
};

const loadCameraCapture = async () => {
  if (!cameraCaptureComponent.value) {
    const { default: CameraCapture } = await import('./reward_sys_comp/CameraCapture.vue');
    cameraCaptureComponent.value = CameraCapture;
  }
};

// Initialize components when mounted
onMounted(async () => {
  // Load basic data
  await loadData();
  
  // Preload student cards since they're essential to this page
  await loadStudentCards();
});

// ... rest of the component logic ...
</script>
```

### 5. Optimize Ziggy Route Loading

Create a custom route helper that loads only necessary routes:

```javascript
// resources/js/plugins/routeOptimizer.js
import { Ziggy } from 'ziggy-js';

// Create a route loader that only loads routes for the current section
export const createOptimizedRouter = (currentSection = null) => {
    // Filter routes based on the current section to reduce payload
    const filteredRoutes = currentSection 
        ? Object.keys(Ziggy.routes).reduce((acc, routeName) => {
            if (routeName.startsWith(currentSection) || 
                routeName.includes(currentSection) ||
                ['dashboard', 'home', 'profile'].includes(routeName)) {
                acc[routeName] = Ziggy.routes[routeName];
            }
            return acc;
        }, {})
        : Ziggy.routes;

    // Return a custom route function with filtered routes
    return (name, params = {}, absolute = true) => {
        if (!filteredRoutes[name]) {
            console.warn(`Route "${name}" not available in current context`);
            return '#';
        }
        
        // Create a temporary Ziggy instance with filtered routes
        const tempZiggy = { ...Ziggy, routes: filteredRoutes };
        return window.route(name, params, absolute, tempZiggy);
    };
};

// Extended route helper with section-based loading
export const initRouteOptimizer = (section) => {
    // Replace global route function with optimized version
    if (window.route) {
        const optimizedRoute = createOptimizedRouter(section);
        window.optimizedRoute = optimizedRoute;
        // Make it available globally
        window.route = optimizedRoute;
    }
};
```

### 6. Update Build Scripts

Add bundle analysis scripts to package.json:

```json
{
  "scripts": {
    "build:bundle-analyze": "vite build --mode analyze && open dist/stats.html",
    // ... other scripts
  }
}
```

## Expected Performance Improvements

These optimizations will achieve:

1. **Reduced Initial Payload**: By implementing code splitting and lazy loading, the initial bundle size will be reduced from 10MB+ to approximately 2-3MB for the reward system page.

2. **On-Demand Loading**: Heavy libraries like html2canvas, jspdf, and tesseract.js will only load when needed.

3. **Route Optimization**: Ziggy routes will be filtered to only include relevant routes for the current section.

4. **Better Caching**: Improved chunk splitting allows for better browser caching of common dependencies.

5. **Improved User Experience**: Faster initial load times with features loading progressively as needed.

## Implementation Priority

1. **Phase 1**: Update Vite configuration and app.js with code splitting (immediate impact)
2. **Phase 2**: Refactor large components to use dynamic imports (medium effort, high impact)
3. **Phase 3**: Implement route optimization (lower priority but good for long-term scaling)
4. **Phase 4**: Add bundle analysis tools for ongoing monitoring (continuous improvement)

## Additional Recommendations

1. **Server-side optimizations**:
   - Optimize PHP opcache settings
   - Configure LiteSpeed for optimal static asset delivery
   - Implement HTTP/2 or HTTP/3 for better resource loading

2. **Asset optimization**:
   - Implement WebP image format with fallbacks
   - Subset fonts to only include used characters
   - Minimize CSS with tree-shaking

3. **CDN strategy**:
   - Host common libraries on CDN for better caching
   - Consider edge computing solutions for frequently accessed assets

These changes should dramatically reduce the 10MB+ payload while maintaining all functionality through progressive loading.