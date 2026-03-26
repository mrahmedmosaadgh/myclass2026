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
            compilerOptions: {
                isCustomElement: (tag) => {
                    // Exclude native HTML elements from component resolution
                    const nativeElements = ['img', 'div', 'span', 'p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'ul', 'ol', 'li', 'a', 'button', 'input', 'textarea', 'select', 'option', 'canvas', 'svg', 'video', 'audio', 'source', 'track']
                    return nativeElements.includes(tag)
                }
            }
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
            // standard production build settings
            sourcemap: !isProduction,
            chunkSizeWarningLimit: 2000,
            rollupOptions: {
                output: {
                    // usage of standard asset filenames
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
                    manualChunks: (id) => {
                        // 1. Minimal Vendor Grouping (Safety First)
                        // We ONLY group libraries we know are safe and independent.
                        // We do NOT group Vue, Quasar, or Utils to avoid "initialization errors".
                        if (id.includes('node_modules')) {
                            // Removing echarts/katex to avoid circular dependencies with feature chunks
                            if (id.includes('firebase') || id.includes('@firebase')) return 'vendor-firebase';
                            if (id.includes('xlsx')) return 'vendor-xlsx';
                        }

                        // 2. Application Feature Grouping (The Fix for 1200+ Requests)
                        // We MUST group these to stop the "1 file per component" madness.

                        // Group ALL Quiz components (Question System, Quiz Management, etc.)
                        if (id.includes('resources/js/Components/QuestionSystem') ||
                            id.includes('resources/js/Pages/QuizManagement') ||
                            id.includes('resources/js/Pages/my_table_mnger/lesson_presentation/quiz') ||
                            id.includes('resources/js/composables/useLazyQuizComponents')) {
                            return 'feature-quiz-engine';
                        }

                        // Group Admin sections
                        if (id.includes('resources/js/Pages/Admin') ||
                            id.includes('resources/js/Pages/my_class/admin') ||
                            id.includes('resources/js/Pages/my_class/super_admin')) {
                            return 'feature-admin-core';
                        }

                        // Group Teacher sections
                        if (id.includes('resources/js/Pages/Teacher') ||
                            id.includes('resources/js/Pages/my_class/teacher')) {
                            return 'feature-teacher-portal';
                        }

                        // Group Basic Math (BM) sections
                        if (id.includes('resources/js/Pages/Courses/bm') ||
                            id.includes('resources/js/Components/Courses/bm') ||
                            id.includes('resources/js/Composables/Courses/bm')) {
                            return 'feature-bm';
                        }
                        
                        // Group MyClass2026 Features & Roles
                        if (id.includes('resources/js/Pages/myclass2026/')) {
                            if (id.includes('/features/')) {
                                const parts = id.split('features/')[1].split('/');
                                const featureName = parts[0];
                                const featureMap = { 'cr': 'classroom-records', 'fg': 'focus-grid', 'qr-tools': 'qr-tools', 'smart-scanner': 'smart-scanner' };
                                return `feature-${featureMap[featureName] || featureName}`;
                            }
                            if (id.includes('/roles/')) {
                                const parts = id.split('roles/')[1].split('/');
                                return `role-${parts[0]}`;
                            }
                        }

                        // Group other major Page sections
                        if (id.includes('resources/js/Pages/')) {
                            const sections = ['Admin', 'Auth', 'CourseManagement', 'Courses', 'Dashboard', 'Documentation', 'Firebase', 'Notifications', 'Profile', 'Student', 'Teacher', 'WeeklyPlans', 'academy', 'modules', 'my_class', 'my_table_mnger', 'myclass_v2', 'old_features', 'qudratpro2026', 'print_html', 'project_manager'];
                            for (const section of sections) {
                                if (id.includes(`resources/js/Pages/${section}`)) {
                                    return `page-section-${section.toLowerCase()}`;
                                }
                            }
                        }

                        // Group major Component sections
                        if (id.includes('resources/js/Components/')) {
                            const compSections = ['AI', 'Chat', 'Common', 'Courses', 'Firebase', 'Icons', 'Messages', 'QuestionBank', 'Quiz', 'Realtime', 'Schedule', 'SkillPractice', 'Students', 'dailyTasks', 'templates'];
                            for (const compSection of compSections) {
                                if (id.includes(`resources/js/Components/${compSection}`)) {
                                    return `comp-section-${compSection.toLowerCase()}`;
                                }
                            }
                        }
                    }
                },
            },
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
