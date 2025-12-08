import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import path from 'path';

export default defineConfig({
    plugins: [
        laravel({
            input: 'resources/js/app.js',
            ssr: 'resources/js/ssr.js',
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
    ],
    resolve: {
        alias: {
            '@': '/resources/js',
            '~': '/resources/js'
        },
    },
    optimizeDeps: {
        include: ['firebase/app', 'firebase/database'],
        exclude: ['firebase/analytics'] // Exclude analytics from initial bundle
    },
    build: {
        rollupOptions: {
            external: ['vue'],
            output: {
                manualChunks: {
                    firebase: ['firebase/app', 'firebase/database'],
                    analytics: ['firebase/analytics']
                },
            },
        },
    },
});




