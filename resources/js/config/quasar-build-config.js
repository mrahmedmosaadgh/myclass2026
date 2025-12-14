/**
 * Quasar Build Configuration for Manual Chunk Splitting
 * Implements Requirements 2.3, 2.4, 2.5
 */

/**
 * Quasar components that should be tree-shaken and split
 */
export const QUASAR_COMPONENTS = {
  // Core components (always included)
  core: [
    'QBtn',
    'QIcon',
    'QSpinner',
    'QSpinnerDots',
    'QDialog',
    'QCard',
    'QCardSection',
    'QCardActions',
    'QLayout',
    'QHeader',
    'QDrawer',
    'QPageContainer',
    'QPage',
    'QToolbar',
    'QToolbarTitle',
    'QSpace',
    'QList',
    'QItem',
    'QItemSection',
    'QItemLabel'
  ],
  
  // Form components (lazy loaded)
  forms: [
    'QInput',
    'QSelect',
    'QCheckbox',
    'QRadio',
    'QToggle',
    'QSlider',
    'QRange',
    'QField',
    'QFile',
    'QDate',
    'QTime',
    'QEditor'
  ],
  
  // Table components (lazy loaded)
  tables: [
    'QTable',
    'QTh',
    'QTr',
    'QTd',
    'QPagination',
    'QVirtualScroll'
  ],
  
  // Advanced components (lazy loaded)
  advanced: [
    'QTree',
    'QTimeline',
    'QTimelineEntry',
    'QCarousel',
    'QCarouselSlide',
    'QCarouselControl',
    'QSplitter',
    'QResizeObserver',
    'QIntersection'
  ],
  
  // Media components (lazy loaded)
  media: [
    'QVideo',
    'QImg',
    'QAvatar',
    'QChip',
    'QBadge'
  ]
}

/**
 * Quasar plugins configuration
 */
export const QUASAR_PLUGINS = {
  // Always loaded
  core: [
    'Notify',
    'Loading',
    'Dialog',
    'Dark'
  ],
  
  // Lazy loaded
  optional: [
    'LocalStorage',
    'SessionStorage',
    'Cookies',
    'Platform',
    'Screen',
    'Meta'
  ]
}

/**
 * Build optimization configuration
 */
export const BUILD_CONFIG = {
  // Chunk size limits (in KB)
  chunkSizeWarningLimit: 1000,
  maxChunkSize: 2000,
  
  // Tree shaking configuration
  treeShaking: {
    enabled: true,
    sideEffects: false,
    usedExports: true
  },
  
  // Code splitting thresholds
  codeSplitting: {
    minSize: 20000,      // 20KB minimum chunk size
    maxSize: 1000000,    // 1MB maximum chunk size
    maxAsyncRequests: 30, // Maximum parallel requests
    maxInitialRequests: 10, // Maximum initial requests
    cacheGroups: {
      vendor: {
        test: /[\\/]node_modules[\\/]/,
        name: 'vendors',
        priority: 10,
        chunks: 'all'
      },
      quasar: {
        test: /[\\/]node_modules[\\/]quasar[\\/]/,
        name: 'quasar',
        priority: 20,
        chunks: 'all'
      },
      common: {
        name: 'common',
        minChunks: 2,
        priority: 5,
        chunks: 'all',
        reuseExistingChunk: true
      }
    }
  },
  
  // Compression settings
  compression: {
    brotli: {
      enabled: true,
      test: /\.(js|css|html|svg)$/,
      threshold: 10240, // 10KB
      minRatio: 0.8
    },
    gzip: {
      enabled: true,
      test: /\.(js|css|html|svg)$/,
      threshold: 10240, // 10KB
      minRatio: 0.8
    }
  }
}

/**
 * Get Quasar components for specific feature
 * @param {string} feature - Feature name
 * @returns {Array<string>}
 */
export function getQuasarComponentsForFeature(feature) {
  const featureMap = {
    'core': QUASAR_COMPONENTS.core,
    'forms': [...QUASAR_COMPONENTS.core, ...QUASAR_COMPONENTS.forms],
    'tables': [...QUASAR_COMPONENTS.core, ...QUASAR_COMPONENTS.tables],
    'advanced': [...QUASAR_COMPONENTS.core, ...QUASAR_COMPONENTS.advanced],
    'media': [...QUASAR_COMPONENTS.core, ...QUASAR_COMPONENTS.media],
    'all': Object.values(QUASAR_COMPONENTS).flat()
  }
  
  return featureMap[feature] || QUASAR_COMPONENTS.core
}

/**
 * Generate Vite build configuration
 * @param {Object} options - Build options
 * @returns {Object}
 */
export function generateViteBuildConfig(options = {}) {
  const config = {
    chunkSizeWarningLimit: BUILD_CONFIG.chunkSizeWarningLimit,
    target: 'es2022',
    minify: 'terser',
    terserOptions: {
      compress: {
        drop_console: options.production !== false,
        drop_debugger: true,
        pure_funcs: options.production !== false ? ['console.log', 'console.info'] : []
      },
      mangle: {
        safari10: true
      }
    },
    rollupOptions: {
      output: {
        manualChunks: (id) => {
          // Vendor chunks
          if (id.includes('node_modules')) {
            // Quasar components
            if (id.includes('quasar/src/components')) {
              return 'quasar-components'
            }
            if (id.includes('quasar')) {
              return 'quasar-core'
            }
            
            // Vue ecosystem
            if (id.includes('vue') || id.includes('@vue')) {
              return 'vue'
            }
            
            // Other vendors
            return 'vendor'
          }
          
          // App chunks
          if (id.includes('/Pages/')) {
            // Route-level splitting
            if (id.includes('/Auth/')) return 'route-auth'
            if (id.includes('/Dashboard/')) return 'route-dashboard'
            if (id.includes('/QuizManagement/')) return 'route-quiz'
            if (id.includes('/reward_sys/')) return 'route-rewards'
          }
          
          // Component chunks
          if (id.includes('CameraCapture') || id.includes('camera')) {
            return 'camera-components'
          }
          if (id.includes('PDFViewer') || id.includes('pdf')) {
            return 'pdf-components'
          }
        },
        chunkFileNames: (chunkInfo) => {
          const facadeModuleId = chunkInfo.facadeModuleId
          if (facadeModuleId) {
            if (facadeModuleId.includes('/Pages/')) {
              return 'assets/pages/[name]-[hash].js'
            }
            if (facadeModuleId.includes('/components/')) {
              return 'assets/components/[name]-[hash].js'
            }
          }
          return 'assets/[name]-[hash].js'
        }
      }
    }
  }
  
  return config
}

/**
 * Get tree shaking configuration for Quasar
 * @returns {Object}
 */
export function getTreeShakingConfig() {
  return {
    // Enable tree shaking for Quasar components
    quasar: {
      importStrategy: 'kebab', // Use kebab-case imports
      components: QUASAR_COMPONENTS.core, // Only include core components by default
      directives: ['Ripple', 'ClosePopup'],
      plugins: QUASAR_PLUGINS.core
    },
    
    // Optimize imports
    optimizeDeps: {
      include: [
        'quasar/src/css/index.sass',
        ...QUASAR_COMPONENTS.core.map(comp => `quasar/src/components/${comp}`)
      ]
    }
  }
}

export default {
  QUASAR_COMPONENTS,
  QUASAR_PLUGINS,
  BUILD_CONFIG,
  getQuasarComponentsForFeature,
  generateViteBuildConfig,
  getTreeShakingConfig
}