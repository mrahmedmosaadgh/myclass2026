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
        chunkSizeWarningLimit: 2000,
        rollupOptions: {
            output: {
                manualChunks: {
                    vendor: ['vue', '@inertiajs/vue3', 'pinia'],
                    ui: ['quasar', 'vue3-toastify'],
                    i18n: ['vue-i18n', 'ziggy-js'],
                    offline: ['nprogress']
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


