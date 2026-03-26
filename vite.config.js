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
                        // ONLY vendor grouping — application code is handled by Rollup automatically.
                        // DO NOT add any application code groupings here (pages, components, features, roles).
                        // They ALL cause "Cannot access X before initialization" circular dependency errors.
                        if (id.includes('node_modules')) {
                            if (id.includes('firebase') || id.includes('@firebase')) return 'vendor-firebase';
                            if (id.includes('xlsx')) return 'vendor-xlsx';
                            if (id.includes('vuedraggable') || id.includes('sortablejs')) return 'vendor-draggable';
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
