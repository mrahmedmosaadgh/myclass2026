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
                        // 1. Heavy Vendor Grouping (node_modules)
                        // Note: We avoid grouping 'vue', '@inertiajs', and 'pinia' here 
                        // because they have complex initialization dependencies that can 11break 
                        // if forced into a single chunk.
                        if (id.includes('node_modules')) {
                            // UI Framework (Quasar) - very heavy
                            if (id.includes('quasar') || id.includes('@quasar')) {
                                return 'vendor-quasar';
                            }
                            // Visualization (ECharts) - very heavy
                            if (id.includes('echarts') || id.includes('chart.js')) {
                                return 'vendor-charts';
                            }
                            // Firebase - heavy
                            if (id.includes('firebase') || id.includes('@firebase')) {
                                return 'vendor-firebase';
                            }
                            // Math (KaTeX) - heavy
                            if (id.includes('katex')) {
                                return 'vendor-katex';
                            }
                            // Big Utilities
                            if (id.includes('lodash') || id.includes('axios') || id.includes('date-fns') || id.includes('xlsx')) {
                                return 'vendor-utils-big';
                            }

                            // Other vendor libs that are relatively independent
                            if (id.includes('tesseract.js') || id.includes('quagga2') || id.includes('@zxing/library')) {
                                return 'vendor-scanners';
                            }
                        }

                        // 2. Application Feature Grouping
                        // This helps reduce the 1200+ requests significantly by grouping 
                        // related Pages and Components together.

                        // Group Quiz System (The most fragmented part)
                        if (id.includes('resources/js/Components/QuestionSystem') ||
                            id.includes('resources/js/Pages/QuizManagement') ||
                            id.includes('resources/js/Pages/my_table_mnger/lesson_presentation/quiz') ||
                            id.includes('resources/js/composables/useLazyQuizComponents')) {
                            return 'feature-quiz-engine';
                        }

                        // Group Admin & HR sections
                        if (id.includes('resources/js/Pages/Admin') ||
                            id.includes('resources/js/Pages/my_class/admin') ||
                            id.includes('resources/js/Pages/my_class/hr') ||
                            id.includes('resources/js/Pages/my_class/super_admin')) {
                            return 'feature-admin-core';
                        }

                        // Group Teacher sections
                        if (id.includes('resources/js/Pages/Teacher') ||
                            id.includes('resources/js/Pages/my_class/teacher')) {
                            return 'feature-teacher-portal';
                        }

                        // Group Reward System
                        if (id.includes('resources/js/Pages/my_table_mnger/reward_sys')) {
                            return 'feature-reward-system';
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
