import { defineConfig } from 'vitest/config'
import vue from '@vitejs/plugin-vue'
import { quasar, transformAssetUrls } from '@quasar/vite-plugin'
import path from 'path'

export default defineConfig({
  plugins: [
    vue({
      template: { transformAssetUrls }
    }),
    quasar({
      sassVariables: 'resources/css/quasar-variables.sass'
    })
  ],
  test: {
    environment: 'happy-dom',
    globals: true,
    setupFiles: ['tests/setup.js']
  },
  resolve: {
    alias: {
      '@': path.resolve(__dirname, 'resources/js'),
      '~': path.resolve(__dirname, 'resources')
    }
  }
})