import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import { quasar, transformAssetUrls } from '@quasar/vite-plugin';
import { visualizer } from 'rollup-plugin-visualizer';

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '');
    const isAnalyze = mode === 'analyze';
    const isProduction = mode === 'production';
    const isPerf = mode === 'perf';

    const plugins = [
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
    ];

    if (isAnalyze || isPerf) {
        plugins.push(
            visualizer({
                filename: './dist/stats.html',
                open: true,
                gzipSize: true,
                brotliSize: true,
            })
        );
    }

    return {
        plugins,
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
            exclude: ['@quasar/extras', 'vue-pdf-embed']
        },
        build: {
            sourcemap: isProduction ? false : true,
            cssMinify: true,
            minify: 'terser',
            target: 'es2020',
            chunkSizeWarningLimit: 500,
            terserOptions: {
                compress: {
                    drop_console: isProduction,
                    drop_debugger: isProduction,
                    pure_funcs: isProduction ? ['console.log', 'console.info', 'console.debug'] : [],
                },
            },
            rollupOptions: {
                output: {
                    manualChunks(id) {
                        // CRITICAL: Check node_modules FIRST
                        if (id.includes('node_modules')) {
                            // Extract package name
                            const match = id.match(/node_modules\/(@[^/]+\/[^/]+|[^/]+)/);
                            const packageName = match ? match[1] : '';

                            // 1. QUASAR - Split into smaller chunks
                            if (id.includes('quasar/') || id.includes('@quasar/')) {
                                if (id.includes('quasar/src/components')) return 'quasar-components';
                                if (id.includes('quasar/src/directives')) return 'quasar-directives';
                                if (id.includes('quasar/src/plugins')) return 'quasar-plugins';
                                return 'quasar-core';
                            }

                            // 2. VUE ECOSYSTEM
                            if (packageName === 'vue' || id.includes('node_modules/vue/')) return 'vue-core';
                            if (packageName === '@vue/') return 'vue-core';
                            if (packageName === '@vueuse/core') return 'vueuse-core';
                            if (packageName === '@vueuse/integrations') return 'vueuse-integrations';
                            if (packageName === 'pinia') return 'pinia';
                            if (packageName === 'pinia-plugin-persistedstate') return 'pinia';
                            if (packageName === '@inertiajs/vue3') return 'inertia';
                            if (packageName === 'vue-i18n') return 'i18n';
                            if (packageName === 'vue-echarts') return 'vendor-charts';
                            if (packageName === 'vue3-toastify') return 'toastify';
                            if (packageName === 'vuedraggable') return 'ui-libs';

                            // 3. PDF LIBRARIES - Split separately (HEAVY!)
                            if (packageName === 'vue-pdf-embed') return 'vendor-pdf-embed';
                            if (id.includes('pdfjs-dist')) return 'vendor-pdfjs';
                            if (id.includes('pdf.js')) return 'vendor-pdfjs';
                            if (packageName === 'html2pdf.js') return 'vendor-html2pdf';
                            if (packageName === 'jspdf') return 'vendor-jspdf';
                            if (packageName === 'html2canvas') return 'vendor-html2canvas';

                            // 4. OTHER HEAVY LIBRARIES
                            if (packageName === 'xlsx') return 'vendor-xlsx';
                            if (packageName === 'tesseract.js') return 'vendor-tesseract';
                            if (packageName === '@ericblade/quagga2') return 'vendor-quagga2';
                            if (packageName === 'katex') return 'vendor-katex';
                            if (packageName === 'echarts') return 'vendor-charts';
                            if (packageName === 'mammoth') return 'vendor-mammoth';
                            if (packageName === 'openai') return 'vendor-openai';
                            if (packageName === '@google/generative-ai') return 'vendor-google-ai';

                            // 5. FIREBASE - Split by module
                            if (id.includes('firebase')) {
                                if (id.includes('@firebase/app')) return 'firebase-app';
                                if (id.includes('@firebase/auth')) return 'firebase-auth';
                                if (id.includes('@firebase/firestore')) return 'firebase-firestore';
                                if (id.includes('@firebase/storage')) return 'firebase-storage';
                                if (id.includes('@firebase/messaging')) return 'firebase-messaging';
                                return 'firebase-other';
                            }

                            // 6. UI LIBRARIES
                            if (packageName === 'lucide-vue-next') return 'icons';
                            if (packageName === 'emoji-picker-element') return 'vendor-emoji';
                            if (packageName === 'cropperjs') return 'ui-libs';
                            if (packageName === 'sortablejs') return 'ui-libs';
                            if (packageName === 'simplebar-vue') return 'ui-libs';
                            if (packageName === 'canvas-confetti') return 'ui-libs';
                            if (packageName === '@he-tree/vue3') return 'ui-libs';
                            if (packageName === 'animate.css') return 'ui-libs';
                            if (packageName === 'vue3-qr-reader') return 'vendor-qr';

                            // 7. UTILITIES
                            if (packageName === 'lodash') return 'vendor-lodash';
                            if (packageName === 'date-fns') return 'vendor-date-fns';
                            if (packageName === 'axios') return 'vendor-axios';
                            if (packageName === 'qs') return 'vendor-qs';
                            if (packageName === 'dompurify') return 'vendor-dompurify';
                            if (packageName === 'dexie') return 'vendor-dexie';
                            if (packageName === 'qrcode.vue' || packageName === '@zxing/library') return 'vendor-qrcode';
                            if (packageName === 'file-saver') return 'vendor-file-saver';
                            if (packageName === 'nprogress') return 'vendor-nprogress';
                            if (packageName === 'ziggy-js') return 'ziggy';

                            // 8. COMMUNICATION
                            if (packageName === 'pusher-js') return 'vendor-pusher';
                            if (packageName === 'laravel-echo') return 'vendor-echo';
                            if (packageName === 'web-push') return 'vendor-web-push';

                            // 9. REMAINING - Should be small now
                            return 'vendor-common';
                        }

                        // APPLICATION CODE - Split by route/feature
                        if (id.includes('Pages/my_class/admin')) return 'admin-section';
                        if (id.includes('Pages/my_class/teacher')) return 'teacher-section';
                        if (id.includes('Pages/my_class/hr')) return 'hr-section';
                        if (id.includes('Pages/my_class/super_admin')) return 'super-admin-section';
                        if (id.includes('Pages/my_class/student')) return 'student-section';
                        if (id.includes('Pages/QuizManagement')) return 'quiz-management';
                        if (id.includes('Pages/WeeklyPlans')) return 'weekly-plans';
                        if (id.includes('Pages/CourseManagement')) return 'course-management';
                        if (id.includes('Pages/RewardSystem')) return 'reward-sys';
                        if (id.includes('Pages/Chat')) return 'chat-section';
                        if (id.includes('Pages/Conversation')) return 'conversation-section';
                        if (id.includes('Pages/DailyPlanner')) return 'daily-planner';
                        if (id.includes('Pages/Settings')) return 'settings';
                        if (id.includes('Pages/ClassworkRecords')) return 'classwork-records';
                        if (id.includes('Pages/StudentsTable')) return 'students-table';
                    },
                    assetFileNames: (assetInfo) => {
                        const extType = assetInfo.name.split('.').pop();
                        if (/png|jpe?g|svg|gif|tiff|bmp|ico|webp/i.test(extType)) {
                            return 'assets/images/[name]-[hash][extname]';
                        }
                        if (/woff|woff2|eot|ttf|otf/i.test(extType)) {
                            return 'assets/fonts/[name]-[hash][extname]';
                        }
                        return 'assets/css/[name]-[hash][extname]';
                    },
                    chunkFileNames: 'assets/js/[name]-[hash].js',
                    entryFileNames: 'assets/js/[name]-[hash].js',
                },
                treeshake: {
                    moduleSideEffects: false,
                    propertyReadSideEffects: false,
                    tryCatchDeoptimization: false,
                },
            },
            cssCodeSplit: true,
            reportCompressedSize: false,
        },
        server: {
            host: 'localhost',
            port: 5173,
            fs: {
                allow: ['..', 'node_modules/@quasar/extras']
            }
        },
    };
});
