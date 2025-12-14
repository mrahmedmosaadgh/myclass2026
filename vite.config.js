import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import { splitVendorChunkPlugin } from 'vite';
import { quasar, transformAssetUrls } from '@quasar/vite-plugin';
import { generateViteBuildConfig, getTreeShakingConfig } from './resources/js/config/quasar-build-config.js';

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
            ...getTreeShakingConfig().quasar
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
        ...generateViteBuildConfig({ production: process.env.NODE_ENV === 'production' }),
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
                        
                        // Quasar - split by components for better tree shaking
                        if (id.includes('quasar/src/components')) return 'quasar-components';
                        if (id.includes('quasar/src/directives') || id.includes('quasar/src/plugins')) return 'quasar-plugins';
                        if (id.includes('quasar')) return 'quasar-core';
                        
                        // UI and styling libraries
                        if (id.includes('vue-i18n')) return 'i18n';
                        if (id.includes('vue3-toastify') || id.includes('nprogress')) return 'ui-utils';
                        
                        // Icon libraries
                        if (id.includes('lucide') || id.includes('@lucide')) return 'icons';
                        
                        // Chart libraries - separate chunk for heavy components
                        if (id.includes('chart') || id.includes('echarts') || id.includes('d3')) return 'charts';
                        
                        // Document processing - PDF viewer gets its own chunk
                        if (id.includes('vue-pdf-embed')) return 'pdf-viewer';
                        if (id.includes('pdf') || id.includes('canvas') || id.includes('html2canvas')) return 'pdf-canvas';
                        if (id.includes('xlsx') || id.includes('excel') || id.includes('sheetjs')) return 'excel';
                        
                        // Media processing - camera and image libraries
                        if (id.includes('cropperjs') || id.includes('html2canvas') || id.includes('canvas-confetti')) return 'media-processing';
                        if (id.includes('katex') || id.includes('mathjax')) return 'math';
                        if (id.includes('qrcode') || id.includes('barcode') || id.includes('@zxing') || id.includes('@ericblade/quagga2')) return 'qr-barcode';
                        
                        // Utility libraries
                        if (id.includes('axios') || id.includes('lodash') || id.includes('date-fns')) return 'utils';
                        if (id.includes('dexie') || id.includes('localforage')) return 'storage';
                        
                        // Other vendor dependencies
                        return 'vendor-misc';
                    }
                    
                    // Camera and media components - separate chunk for lazy loading
                    if (id.includes('CameraCapture') || id.includes('ImageCropper') || id.includes('MediaCapture')) {
                        return 'camera-components';
                    }
                    
                    // PDF Components - separate chunk for lazy loading
                    if (id.includes('PDFViewer') || id.includes('PDFAnnotator') || id.includes('SimplePDFViewer')) {
                        return 'pdf-components';
                    }
                    
                    // Route-level chunks - major pages
                    if (id.includes('/Pages/Dashboard/') || id.includes('/Pages/Dashboard.vue')) return 'route-dashboard';
                    if (id.includes('/Pages/Auth/')) return 'route-auth';
                    if (id.includes('/Pages/Profile/')) return 'route-profile';
                    if (id.includes('/Pages/Students/') || id.includes('/Pages/Student/')) return 'route-students';
                    if (id.includes('/Pages/Teachers/') || id.includes('/Pages/Teacher/')) return 'route-teachers';
                    if (id.includes('/Pages/WeeklyPlans/')) return 'route-weekly-plans';
                    if (id.includes('/Pages/VocabularyFlashcards/')) return 'route-vocabulary';
                    if (id.includes('/Pages/Conversations/') || id.includes('/Pages/Chat/')) return 'route-chat';
                    if (id.includes('/Pages/Notifications/')) return 'route-notifications';
                    
                    // Feature-specific chunks - more granular
                    if (id.includes('/QuizManagement/')) {
                        if (id.includes('QuizBuilder') || id.includes('QuestionEditor')) return 'quiz-builder';
                        if (id.includes('QuizEngine') || id.includes('QuizPlayer')) return 'quiz-engine';
                        if (id.includes('Live/')) return 'quiz-live';
                        return 'quiz-management';
                    }
                    
                    if (id.includes('/reward_sys/')) {
                        if (id.includes('BehaviorManager') || id.includes('BehaviorIncidents')) return 'rewards-behavior';
                        if (id.includes('Leaderboard') || id.includes('TopLeaderboard')) return 'rewards-leaderboard';
                        if (id.includes('CameraCapture')) return 'rewards-camera';
                        return 'rewards-core';
                    }
                    
                    if (id.includes('/my_table_mnger/')) {
                        if (id.includes('attendance') || id.includes('Attendance')) return 'table-attendance';
                        if (id.includes('schedule') || id.includes('Schedule')) return 'table-schedule';
                        if (id.includes('lesson_presentation')) return 'table-lessons';
                        return 'table-manager-core';
                    }
                    
                    if (id.includes('/CourseManagement/')) return 'course-management';
                    if (id.includes('/LessonManagement/') || id.includes('/Lessons/')) return 'lessons';
                    if (id.includes('/QuestionManagement/')) return 'question-management';
                    if (id.includes('/dailyTasks/')) return 'daily-tasks';
                    if (id.includes('/project_manager/')) return 'project-manager';
                    
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


