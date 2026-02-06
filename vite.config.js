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

    // Add bundle analyzer plugin when in analyze mode
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
            exclude: ['@quasar/extras']
        },
        build: {
            sourcemap: isProduction ? false : true,
            cssMinify: true,
            minify: 'terser',
            target: 'es2018',
            chunkSizeWarningLimit: 1000, // Increased to account for large feature libraries
            rollupOptions: {
                output: {
                    // Use function-based manual chunks for better control
                    manualChunks(id) {
                        // CRITICAL: Separate Quasar first - it's the largest library
                        if (id.includes('node_modules/quasar/') || id.includes('node_modules/@quasar/')) {
                            return 'quasar';
                        }

                        // Extract heavy libraries into separate chunks - ONLY if they're used
                        if (id.includes('xlsx')) {
                            return 'xlsx';
                        }
                        if (id.includes('html2pdf')) {
                            return 'html2pdf';
                        }
                        if (id.includes('jspdf')) {
                            return 'jspdf';
                        }
                        if (id.includes('html2canvas')) {
                            return 'html2canvas';
                        }
                        if (id.includes('tesseract')) {
                            return 'tesseract';
                        }
                        if (id.includes('quagga2')) {
                            return 'quagga2';
                        }
                        if (id.includes('firebase')) {
                            return 'firebase';
                        }
                        if (id.includes('openai')) {
                            return 'openai';
                        }
                        if (id.includes('echarts') || id.includes('vue-echarts')) {
                            return 'charts';
                        }
                        if (id.includes('@google/generative-ai')) {
                            return 'google-ai';
                        }

                        // Group by major application areas
                        if (id.includes('Pages/my_class/admin')) {
                            return 'admin-section';
                        }
                        if (id.includes('Pages/my_class/teacher')) {
                            return 'teacher-section';
                        }
                        if (id.includes('Pages/my_class/hr')) {
                            return 'hr-section';
                        }
                        if (id.includes('Pages/my_class/super_admin')) {
                            return 'super-admin-section';
                        }
                        if (id.includes('Pages/QuizManagement')) {
                            return 'quiz-management';
                        }
                        if (id.includes('Pages/WeeklyPlans')) {
                            return 'weekly-plans';
                        }
                        if (id.includes('Pages/CourseManagement')) {
                            return 'course-management';
                        }
                        if (id.includes('Pages/Student') || id.includes('Pages/Students')) {
                            return 'student-section';
                        }
                        if (id.includes('Pages/Teacher') || id.includes('Pages/Teachers')) {
                            return 'teacher-pages';
                        }
                        if (id.includes('Pages/Chat')) {
                            return 'chat-section';
                        }
                        if (id.includes('Pages/Conversation')) {
                            return 'conversation-section';
                        }

                        // More granular vendor chunk splitting - check node_modules AFTER pages
                        if (id.includes('node_modules')) {
                            // Separate core frameworks
                            if (id.includes('node_modules/vue/') && !id.includes('vue-router')) {
                                return 'vue';
                            }
                            if (id.includes('@vueuse')) {
                                return 'vueuse';
                            }
                            if (id.includes('pinia')) {
                                return 'pinia';
                            }
                            if (id.includes('@inertiajs')) {
                                return 'inertia';
                            }
                            if (id.includes('vue-i18n')) {
                                return 'i18n';
                            }

                            // Split Ziggy separately
                            if (id.includes('ziggy')) {
                                return 'ziggy';
                            }

                            // Split toast library
                            if (id.includes('vue3-toastify')) {
                                return 'toastify';
                            }

                            // Separate heavy/special-use libraries that should be lazy-loaded
                            if (id.includes('katex')) {
                                return 'katex';
                            }
                            if (id.includes('mammoth')) {
                                return 'mammoth';
                            }
                            if (id.includes('cropperjs') || id.includes('sortablejs') || id.includes('vuedraggable')) {
                                return 'ui-libs';
                            }
                            if (id.includes('lucide')) {
                                return 'icons';
                            }

                            if (id.includes('lodash')) {
                                return 'vendor-lodash';
                            }
                            if (id.includes('date-fns')) {
                                return 'vendor-date-fns';
                            }
                            if (id.includes('axios')) {
                                return 'vendor-axios';
                            }
                            if (id.includes('pusher')) {
                                return 'vendor-pusher';
                            }
                            if (id.includes('laravel-echo')) {
                                return 'vendor-echo';
                            }

                            // Split large UI libraries
                            if (id.includes('simplebar')) {
                                return 'vendor-simplebar';
                            }
                            if (id.includes('emoji-picker')) {
                                return 'vendor-emoji';
                            }
                            if (id.includes('qrcode')) {
                                return 'vendor-qrcode';
                            }
                            if (id.includes('dexie')) {
                                return 'vendor-dexie';
                            }
                            if (id.includes('dompurify')) {
                                return 'vendor-dompurify';
                            }
                            if (id.includes('animate.css')) {
                                return 'vendor-animate';
                            }
                            if (id.includes('nprogress')) {
                                return 'vendor-nprogress';
                            }
                            if (id.includes('file-saver')) {
                                return 'vendor-file-saver';
                            }

                            // Split pdf libraries
                            if (id.includes('pdf.js')) {
                                return 'pdfjs';
                            }
                            if (id.includes('pdfjs-dist')) {
                                return 'pdfjs';
                            }

                            // Separate utility libraries
                            const utilModules = [
                                'uuid', 'nanoid', 'qs', 'query-string', 'uri-js', 
                                'validator', 'yup', 'joi', 'ajv', 'json5'
                            ];
                            
                            for (const module of utilModules) {
                                if (id.includes(`node_modules/${module}/`)) {
                                    return `vendor-${module.replace('@', '').replace('/', '-')}`;
                                }
                            }

                            // Default vendor chunk for remaining node_modules (keep small)
                            return 'vendor';
                        }
                    },
                    // Add more aggressive chunk splitting for better caching
                    ...(isProduction && {
                        assetFileNames: (assetInfo) => {
                            let extType = assetInfo.name.split('.').at(1);
                            if (/png|jpe?g|svg|gif|tiff|bmp|ico/i.test(extType)) {
                                return `assets/images/[name]-[hash][extname]`;
                            }
                            if (/woff|woff2|eot|ttf|otf/i.test(extType)) {
                                return `assets/fonts/[name]-[hash][extname]`;
                            }
                            return `assets/css/[name]-[hash][extname]`;
                        },
                        chunkFileNames: 'assets/js/[name]-[hash].js',
                        entryFileNames: 'assets/js/[name]-[hash].js',
                    })
                },
                // Enable treeshaking for production builds
                treeshake: isProduction ? 'smallest' : true,
            },
            // Optimize for production builds
            ...(isProduction && {
                cssCodeSplit: true,
                reportCompressedSize: true,
            }),
        },
        server: {
            host: 'localhost',
            port: 5173,
            fs: {
                allow: ['..', 'node_modules/@quasar/extras']
            }
        },
        // Enable compression in analyze/performance mode
        ...(isAnalyze && {
            build: {
                rollupOptions: {
                    output: {
                        compact: true,
                    }
                }
            }
        })
    };
});