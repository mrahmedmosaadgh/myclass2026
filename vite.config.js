import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import { quasar } from '@quasar/vite-plugin';

const isProduction = process.env.NODE_ENV === 'production';

export default defineConfig({
    plugins: [
        laravel({
            input: 'resources/js/app.js',
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        quasar({
            sassVariables: false,
            // Production-optimized Quasar config
            importStrategy: 'kebab',
            components: isProduction ? 'auto' : 'all', // Auto-import only used components in production
            directives: isProduction ? 'auto' : 'all',
            plugins: [
                'Notify',
                'Loading', 
                'Dialog',
                'Dark'
            ]
        }),
    ],
    resolve: {
        alias: {
            '@': '/resources/js',
        },
    },
    optimizeDeps: {
        include: [
            'vue',
            '@inertiajs/vue3',
            'pinia',
            'vue-i18n',
            'quasar',
            'vue3-toastify',
            'nprogress'
        ],
        exclude: ['@quasar/extras']
    },
    build: {
        // Explicit build configuration for production safety
        outDir: 'public/build',
        assetsDir: 'assets',
        manifest: true,
        emptyOutDir: true,
        
        // Production-optimized settings
        target: 'es2022', // Changed from es2020 to support top-level await
        minify: isProduction ? 'terser' : false,
        sourcemap: !isProduction,
        
        // Chunk size warnings - aligned with spec requirements
        chunkSizeWarningLimit: 1000, // 1MB warning threshold
        
        terserOptions: isProduction ? {
            compress: {
                drop_console: true,
                drop_debugger: true,
                pure_funcs: ['console.log', 'console.info', 'console.debug']
            },
            mangle: {
                safari10: true
            }
        } : {},
        
        rollupOptions: {
            output: {
                // Deterministic chunk naming for better caching
                chunkFileNames: 'assets/js/[name]-[hash].js',
                entryFileNames: 'assets/js/[name]-[hash].js',
                
                // Fixed assetFileNames to remove deprecated 'name' property
                assetFileNames: (assetInfo) => {
                    const fileName = assetInfo.names?.[0] || 'asset';
                    const info = fileName.split('.');
                    const ext = info[info.length - 1];
                    
                    if (/\.(css)$/.test(fileName)) {
                        return `assets/css/[name]-[hash].${ext}`;
                    }
                    if (/\.(png|jpe?g|svg|gif|tiff|bmp|ico)$/i.test(fileName)) {
                        return `assets/images/[name]-[hash].${ext}`;
                    }
                    if (/\.(woff2?|eot|ttf|otf)$/i.test(fileName)) {
                        return `assets/fonts/[name]-[hash].${ext}`;
                    }
                    return `assets/[name]-[hash].${ext}`;
                },
                
                // Production-safe manual chunks strategy
                manualChunks: (id) => {
                    // Always handle node_modules first for consistent vendor splitting
                    if (id.includes('node_modules')) {
                        // Core Vue ecosystem - most stable
                        if (id.includes('vue') && !id.includes('vue-')) return 'vendor-vue-core';
                        if (id.includes('@vue/')) return 'vendor-vue-core';
                        if (id.includes('@inertiajs')) return 'vendor-inertia';
                        
                        // Quasar framework - stable but large
                        if (id.includes('quasar')) return 'vendor-quasar';
                        
                        // State management and routing
                        if (id.includes('pinia') || id.includes('vue-router')) return 'vendor-state';
                        
                        // Heavy feature libraries - separate for better caching
                        if (id.includes('echarts') || id.includes('chart')) return 'vendor-charts';
                        if (id.includes('pdf') || id.includes('jspdf') || id.includes('html2canvas')) return 'vendor-pdf';
                        if (id.includes('cropperjs') || id.includes('canvas-confetti')) return 'vendor-media';
                        if (id.includes('katex') || id.includes('mathjax')) return 'vendor-math';
                        if (id.includes('dexie') || id.includes('localforage')) return 'vendor-storage';
                        
                        // Utilities and smaller libraries
                        if (id.includes('axios') || id.includes('lodash') || id.includes('date-fns')) return 'vendor-utils';
                        if (id.includes('lucide') || id.includes('@lucide') || id.includes('heroicons')) return 'vendor-icons';
                        if (id.includes('vue-i18n') || id.includes('vue3-toastify') || id.includes('nprogress')) return 'vendor-ui-utils';
                        
                        // Catch-all for remaining vendor code
                        return 'vendor-misc';
                    }
                    
                    // Application code splitting - only major features to prevent chunk explosion
                    if (isProduction) {
                        // Route-level chunks for major sections
                        if (id.includes('/Pages/QuizManagement/')) return 'route-quiz';
                        if (id.includes('/Pages/my_table_mnger/reward_sys/')) return 'route-rewards';
                        if (id.includes('/Pages/CourseManagement/')) return 'route-courses';
                        if (id.includes('/Pages/Auth/')) return 'route-auth';
                        if (id.includes('/Pages/Dashboard/')) return 'route-dashboard';
                        
                        // Heavy components that should be lazy-loaded
                        if (id.includes('CameraCapture') || id.includes('/camera/')) return 'component-camera';
                        if (id.includes('PDFViewer') || id.includes('/pdf/')) return 'component-pdf';
                        if (id.includes('ChartComponent') || id.includes('/charts/')) return 'component-charts';
                        
                        // Don't split other app code in production for simplicity
                        return undefined;
                    } else {
                        // Development: More granular splitting for debugging
                        if (id.includes('/Pages/QuizManagement/')) return 'dev-quiz';
                        if (id.includes('/Pages/my_table_mnger/reward_sys/')) return 'dev-rewards';
                        if (id.includes('/Pages/CourseManagement/')) return 'dev-courses';
                        if (id.includes('/Pages/Auth/')) return 'dev-auth';
                        if (id.includes('/Components/')) return 'dev-components';
                        if (id.includes('/Composables/')) return 'dev-composables';
                        
                        return undefined;
                    }
                }
            }
        }
    },
    server: {
        host: 'localhost',
        port: 5173,
        fs: {
            allow: ['..', 'node_modules/@quasar/extras']
        }
    }
});


