/**
 * Lazy Component Loader Utility
 * Provides dynamic component loading with preloading capabilities
 * Implements Requirements 2.3, 4.1, 4.5
 */

import { defineAsyncComponent, ref, markRaw } from 'vue'
import { Loading } from 'quasar'

class LazyComponentLoader {
    constructor() {
        this.componentCache = new Map()
        this.preloadPromises = new Map()
        this.loadingStates = new Map()
    }

    /**
     * Load a component dynamically with caching
     * @param {string} name - Component name/path
     * @param {Function} importFn - Dynamic import function
     * @param {Object} options - Loading options
     * @returns {Promise<Component>}
     */
    async loadComponent(name, importFn, options = {}) {
        // Return cached component if available
        if (this.componentCache.has(name)) {
            return this.componentCache.get(name)
        }

        // Return existing loading promise if component is already being loaded
        if (this.preloadPromises.has(name)) {
            return this.preloadPromises.get(name)
        }

        // Show loading indicator if requested
        if (options.showLoading) {
            Loading.show({
                message: options.loadingMessage || 'Loading component...',
                spinnerColor: 'primary'
            })
        }

        // Create loading promise
        const loadPromise = importFn()
            .then(module => {
                const component = markRaw(module.default || module)
                this.componentCache.set(name, component)
                return component
            })
            .catch(error => {
                console.error(`Failed to load component ${name}:`, error)
                // Remove failed promise from cache to allow retry
                this.preloadPromises.delete(name)
                throw error
            })
            .finally(() => {
                if (options.showLoading) {
                    Loading.hide()
                }
                this.loadingStates.delete(name)
            })

        this.preloadPromises.set(name, loadPromise)
        this.loadingStates.set(name, true)

        return loadPromise
    }

    /**
     * Preload a component without returning it
     * @param {string} name - Component name/path
     * @param {Function} importFn - Dynamic import function
     */
    preloadComponent(name, importFn) {
        if (!this.componentCache.has(name) && !this.preloadPromises.has(name)) {
            this.loadComponent(name, importFn, { showLoading: false })
        }
    }

    /**
     * Create an async component with loading and error states
     * @param {Function} importFn - Dynamic import function
     * @param {Object} options - Component options
     * @returns {AsyncComponent}
     */
    createAsyncComponent(importFn, options = {}) {
        return defineAsyncComponent({
            loader: importFn,
            loadingComponent: options.loadingComponent || this.createLoadingComponent(),
            errorComponent: options.errorComponent || this.createErrorComponent(),
            delay: options.delay || 200,
            timeout: options.timeout || 10000,
            suspensible: options.suspensible !== false,
            onError: (error, retry, fail, attempts) => {
                console.error('Async component loading error:', error)
                if (attempts <= 3) {
                    // Retry up to 3 times
                    retry()
                } else {
                    fail()
                }
            }
        })
    }

    /**
     * Unload a component from cache
     * @param {string} name - Component name/path
     */
    unloadComponent(name) {
        this.componentCache.delete(name)
        this.preloadPromises.delete(name)
        this.loadingStates.delete(name)
    }

    /**
     * Check if component is currently loading
     * @param {string} name - Component name/path
     * @returns {boolean}
     */
    isLoading(name) {
        return this.loadingStates.has(name)
    }

    /**
     * Check if component is cached
     * @param {string} name - Component name/path
     * @returns {boolean}
     */
    isCached(name) {
        return this.componentCache.has(name)
    }

    /**
     * Clear all cached components
     */
    clearCache() {
        this.componentCache.clear()
        this.preloadPromises.clear()
        this.loadingStates.clear()
    }

    /**
     * Get cache statistics
     * @returns {Object}
     */
    getCacheStats() {
        return {
            cached: this.componentCache.size,
            loading: this.loadingStates.size,
            preloading: this.preloadPromises.size
        }
    }

    /**
     * Create default loading component
     * @returns {Component}
     */
    createLoadingComponent() {
        return {
            template: `
                <div class="lazy-loading-container">
                    <div class="lazy-loading-spinner">
                        <q-spinner-dots size="2em" color="primary" />
                    </div>
                    <div class="lazy-loading-text">Loading component...</div>
                </div>
            `,
            style: `
                .lazy-loading-container {
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    justify-content: center;
                    padding: 2rem;
                    min-height: 200px;
                }
                .lazy-loading-text {
                    margin-top: 1rem;
                    color: #666;
                    font-size: 0.9rem;
                }
            `
        }
    }

    /**
     * Create default error component
     * @returns {Component}
     */
    createErrorComponent() {
        return {
            template: `
                <div class="lazy-error-container">
                    <q-icon name="error_outline" size="3em" color="negative" />
                    <div class="lazy-error-text">Failed to load component</div>
                    <q-btn 
                        flat 
                        color="primary" 
                        label="Retry" 
                        @click="$emit('retry')"
                        class="q-mt-md"
                    />
                </div>
            `,
            emits: ['retry'],
            style: `
                .lazy-error-container {
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    justify-content: center;
                    padding: 2rem;
                    min-height: 200px;
                }
                .lazy-error-text {
                    margin-top: 1rem;
                    color: #c10015;
                    font-size: 0.9rem;
                    text-align: center;
                }
            `
        }
    }
}

// Create singleton instance
const lazyLoader = new LazyComponentLoader()

// Export commonly used lazy components
export const LazyPDFViewer = lazyLoader.createAsyncComponent(
    () => import('@/Pages/print_html/components/ReusableHtmlViewer.vue'),
    { 
        delay: 100,
        timeout: 15000,
        loadingMessage: 'Loading PDF viewer...'
    }
)

export const LazyCameraCapture = lazyLoader.createAsyncComponent(
    () => import('@/Pages/my_table_mnger/reward_sys/reward_sys_comp/CameraCapture.vue'),
    { 
        delay: 200,
        timeout: 10000,
        loadingMessage: 'Loading camera...'
    }
)

export const LazyQuizBuilder = lazyLoader.createAsyncComponent(
    () => import('@/Pages/QuizManagement/QuizBuilder.vue'),
    { 
        delay: 150,
        timeout: 12000,
        loadingMessage: 'Loading quiz builder...'
    }
)

export const LazyLeaderboard = lazyLoader.createAsyncComponent(
    () => import('@/Pages/my_table_mnger/reward_sys/reward_sys_comp/TopLeaderboard.vue'),
    { 
        delay: 100,
        timeout: 8000,
        loadingMessage: 'Loading leaderboard...'
    }
)

export const LazyChartComponents = lazyLoader.createAsyncComponent(
    () => import('vue-echarts'),
    { 
        delay: 200,
        timeout: 10000,
        loadingMessage: 'Loading charts...'
    }
)

// Route-level lazy components
export const createRouteLazyComponent = (routePath, options = {}) => {
    return lazyLoader.createAsyncComponent(
        () => import(`@/Pages/${routePath}.vue`),
        {
            delay: 100,
            timeout: 10000,
            loadingMessage: `Loading ${routePath.split('/').pop()}...`,
            ...options
        }
    )
}

export default lazyLoader