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
        exclude: ['@quasar/extras'],
        // Force dependency pre-bundling to avoid circular deps
        force: true
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
                
                // Simplified manual chunks to avoid circular dependencies
                manualChunks: (id) => {
                    // Only split node_modules to avoid circular dependencies in app code
                    if (id.includes('node_modules')) {
                        // Keep Vue ecosystem together to prevent circular deps
                        if (id.includes('vue') || id.includes('@vue/') || id.includes('@inertiajs')) {
                            return 'vendor-vue';
                        }
                        
                        // Quasar framework
                        if (id.includes('quasar')) return 'vendor-quasar';
                        
                        // Large libraries that are safe to split
                        if (id.includes('katex') || id.includes('mathjax')) return 'vendor-math';
                        if (id.includes('pdf') || id.includes('jspdf') || id.includes('html2canvas')) return 'vendor-pdf';
                        if (id.includes('echarts') || id.includes('chart')) return 'vendor-charts';
                        
                        // Everything else goes to vendor-misc
                        return 'vendor-misc';
                    }
                    
                    // Don't split application code to avoid circular dependencies
                    return undefined;
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


