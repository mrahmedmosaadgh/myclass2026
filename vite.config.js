import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import { splitVendorChunkPlugin } from 'vite';
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
        splitVendorChunkPlugin(),
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
            chunkSizeWarningLimit: 2000,
            rollupOptions: {
                output: {
                    manualChunks: {
                        vendor: ['vue', '@inertiajs/vue3', 'pinia'],
                        ui: ['quasar', 'vue3-toastify'],
                        i18n: ['vue-i18n', 'ziggy-js'],
                        offline: ['nprogress']
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