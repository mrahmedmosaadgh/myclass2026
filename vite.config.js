import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import { splitVendorChunkPlugin } from 'vite';
import { quasar, transformAssetUrls } from '@quasar/vite-plugin';

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
            sassVariables: false
        }),
        // Remove splitVendorChunkPlugin()
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
        chunkSizeWarningLimit: 1500, // Lower limit to encourage better splitting
        target: 'es2022', // Support top-level await
        minify: 'terser', // Better compression
        terserOptions: {
            compress: {
                drop_console: true, // Remove console.log in production
                drop_debugger: true,
            },
        },
        rollupOptions: {
            output: {
                manualChunks: (id) => {
                    // Vendor chunks - more granular splitting
                    if (id.includes('node_modules')) {
                        // Vue ecosystem - split into smaller chunks
                        if (id.includes('vue/dist') || id.includes('vue/runtime')) return 'vue-core';
                        if (id.includes('@vue/') || id.includes('vue-router')) return 'vue-utils';
                        if (id.includes('@inertiajs')) return 'inertia';
                        if (id.includes('pinia')) return 'pinia';
                        
                        // Quasar - split by components
                        if (id.includes('quasar/src/components')) return 'quasar-components';
                        if (id.includes('quasar/src/directives') || id.includes('quasar/src/plugins')) return 'quasar-plugins';
                        if (id.includes('quasar')) return 'quasar-core';
                        
                        // UI and styling libraries
                        if (id.includes('vue-i18n')) return 'i18n';
                        if (id.includes('vue3-toastify') || id.includes('nprogress')) return 'ui-utils';
                        
                        // Icon libraries
                        if (id.includes('lucide') || id.includes('@lucide')) return 'icons';
                        
                        // Chart libraries
                        if (id.includes('chart') || id.includes('echarts') || id.includes('d3')) return 'charts';
                        
                        // Document processing
                        if (id.includes('pdf') || id.includes('canvas') || id.includes('html2canvas')) return 'pdf-canvas';
                        if (id.includes('xlsx') || id.includes('excel') || id.includes('sheetjs')) return 'excel';
                        
                        // Media processing
                        if (id.includes('katex') || id.includes('mathjax')) return 'math';
                        if (id.includes('qrcode') || id.includes('barcode')) return 'qr-barcode';
                        
                        // Utility libraries
                        if (id.includes('axios') || id.includes('lodash') || id.includes('moment')) return 'utils';
                        if (id.includes('dexie') || id.includes('localforage')) return 'storage';
                        
                        // Other vendor dependencies
                        return 'vendor-misc';
                    }
                    
                    // App chunks by feature - more granular
                    if (id.includes('/QuizManagement/')) {
                        if (id.includes('QuizBuilder') || id.includes('QuestionEditor')) return 'quiz-builder';
                        if (id.includes('QuizEngine') || id.includes('QuizPlayer')) return 'quiz-engine';
                        return 'quiz-management';
                    }
                    
                    if (id.includes('/reward_sys/')) {
                        if (id.includes('BehaviorManager') || id.includes('BehaviorIncidents')) return 'rewards-behavior';
                        if (id.includes('Leaderboard') || id.includes('TopLeaderboard')) return 'rewards-leaderboard';
                        return 'rewards-core';
                    }
                    
                    if (id.includes('/my_table_mnger/')) {
                        if (id.includes('attendance') || id.includes('Attendance')) return 'table-attendance';
                        if (id.includes('schedule') || id.includes('Schedule')) return 'table-schedule';
                        return 'table-manager-core';
                    }
                    
                    if (id.includes('/LessonManagement/')) return 'lessons';
                    if (id.includes('/UserManagement/')) return 'users';
                    if (id.includes('/Dashboard/')) return 'dashboard';
                    if (id.includes('/Auth/')) return 'auth';
                    
                    // Components by type
                    if (id.includes('/Components/')) {
                        if (id.includes('Chart') || id.includes('Graph')) return 'components-charts';
                        if (id.includes('Form') || id.includes('Input')) return 'components-forms';
                        if (id.includes('Table') || id.includes('DataTable')) return 'components-tables';
                        return 'components-common';
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


