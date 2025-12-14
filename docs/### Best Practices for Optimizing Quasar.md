### Best Practices for Optimizing Quasar (Vite) Bundle Size – Especially with Large PDF Viewer Chunks

Your build warning shows three large chunks (>1500 kB minified), with **pdf-canvas-B2rlCR6j.js (~3 MB)** being the biggest culprit. This is almost certainly **PDF.js** (Mozilla's PDF rendering library) or a wrapper around it – it's notoriously heavy because it bundles full PDF parsing logic in JS.

While gzip reduces download size significantly (~961 kB for the PDF chunk), large initial chunks still hurt **First Contentful Paint (FCP)** and **Time to Interactive (TTI)**, especially on mobile or slow networks. Users may wait longer for the app to become usable.

Here are my **top performance-focused recommendations**, prioritized by impact:

#### 1. **Highest Impact: Lazy-Load the PDF Viewer (Code Splitting)**
   Don't import the PDF component globally or on initial routes. Load it **dynamically only when needed** (e.g., on a "View Lesson PDF" page).

   - In routes (if using Quasar routing): Use dynamic imports for PDF-heavy pages.
   - In components:
     ```vue
     <template>
       <div v-if="pdfUrl">
         <component :is="PdfViewer" :src="pdfUrl" />
       </div>
     </template>

     <script setup>
     import { ref } from 'vue'
     const PdfViewer = ref(null)
     const pdfUrl = ref(null)  // Set when user clicks to view PDF

     const openPdf = async (url) => {
       pdfUrl.value = url
       PdfViewer.value = (await import('@/components/YourPdfViewer.vue')).default
     }
     </script>
     ```

   **Result**: The ~3 MB PDF chunk loads **on-demand** (separate network request). Initial bundle drops dramatically – users without PDFs download almost nothing extra.

#### 2. **Analyze Your Bundle First (Essential Step)**
   Before tweaking, visualize what's inside each chunk.

   Install temporarily:
   ```
   npm i -D rollup-plugin-visualizer
   ```

   In `quasar.config.js` (or `quasar.conf.js`):
   ```js
   const { visualizer } = require('rollup-plugin-visualizer')

   module.exports = function (ctx) {
     return {
       build: {
         vitePlugins: [
           [visualizer, { open: true, filename: 'dist/stats.html' }]
         ]
       }
     }
   }
   ```

   Run `quasar build` – it opens an interactive treemap like these examples:












   This pinpoints exactly what's bloating `pdf-canvas` (likely `pdfjs-dist`).

#### 3. **Manual Chunking for Better Caching**
   Split heavy vendors into stable chunks (long-term caching).

   In `quasar.config.js`:
   ```js
   build: {
     extendViteConf (viteConf) {
       viteConf.build.rollupOptions = {
         output: {
           manualChunks(id) {
             if (id.includes('node_modules/pdfjs-dist') || id.includes('pdf.js')) {
               return 'pdf-viewer'
             }
             if (id.includes('node_modules/quasar')) {
               return 'quasar'
             }
             if (id.includes('node_modules')) {
               return 'vendor'
             }
           }
         }
       }
     },
     chunkSizeWarningLimit: 3500  // Suppress warning for now
   }
   ```

   Combines well with lazy-loading – PDF chunk caches separately.

#### 4. **Consider Lighter PDF Alternatives**
   If you don't need advanced features (annotations, text selection in complex PDFs):

   - **Best for Performance**: Use browser-native viewer with `<iframe>` or `<embed>`:
     ```vue
     <iframe :src="pdfUrl + '#toolbar=1&navpanes=0&scrollbar=1'" width="100%" height="800px" />
     ```
     **Bundle impact: 0 kB added**. Fastest rendering, uses device's built-in PDF engine.

   - Lightweight wrappers: `vue-pdf-embed` (~100-200 kB) or similar minimal PDF.js forks.

   - Avoid full PDF.js unless required (e.g., for offline/custom rendering).

#### 5. **Other Quick Wins**
   - Route-level lazy loading (Quasar supports dynamic imports natively).
   - Tree-shake unused Quasar components/icons.
   - Enable Brotli compression on Hostinger (better than gzip).
   - Test real performance with Lighthouse/Web Vitals.

Prioritize **#1 (lazy-loading)** + **#2 (visualizer)** – you'll likely cut initial load by 70-80% for non-PDF users.

If you share your PDF component code or `package.json` deps (e.g., is it `@quasar/qpdfviewer`, `pdfjs-dist`, or `vue3-pdf`?), I can give even more targeted config!