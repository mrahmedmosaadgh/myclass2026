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
                        // 1. Vendor Grouping (node_modules)
                        if (id.includes('node_modules')) {
                            // Core Vue Ecosystem
                            if (id.includes('vue') || id.includes('pinia') || id.includes('@inertiajs')) {
                                return 'vendor-vue-core';
                            }
                            // UI Framework (Quasar)
                            if (id.includes('quasar') || id.includes('@quasar')) {
                                return 'vendor-quasar';
                            }
                            // Utilities
                            if (id.includes('lodash') || id.includes('axios') || id.includes('date-fns')) {
                                return 'vendor-utils';
                            }
                            // Visualization
                            if (id.includes('echarts') || id.includes('chart.js')) {
                                return 'vendor-charts';
                            }
                            // Firebase
                            if (id.includes('firebase') || id.includes('@firebase')) {
                                return 'vendor-firebase';
                            }
                            // Math (KaTeX)
                            if (id.includes('katex')) {
                                return 'vendor-katex';
                            }
                            // Everything else
                            return 'vendor-libs';
                        }

                        // 2. Application Feature Grouping (Your Code)
                        // Group Quiz System components together (high priority for optimization)
                        if (id.includes('resources/js/Components/QuestionSystem') ||
                            id.includes('resources/js/Pages/QuizManagement') ||
                            id.includes('resources/js/composables/useLazyQuizComponents')) {
                            return 'feature-quiz-engine';
                        }

                        // Group Admin Dashboard pages
                        if (id.includes('resources/js/Pages/Admin') || id.includes('resources/js/Pages/my_class/admin')) {
                            return 'feature-admin-panel';
                        }

                        // Group Teacher Interface
                        if (id.includes('resources/js/Pages/Teacher') || id.includes('resources/js/Pages/my_class/teacher')) {
                            return 'feature-teacher-portal';
                        }

                        // Group Student Interface
                        if (id.includes('resources/js/Pages/Student') || id.includes('resources/js/Pages/Qudrat')) {
                            return 'feature-student-portal';
                        }
                    },
                }
            }
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
