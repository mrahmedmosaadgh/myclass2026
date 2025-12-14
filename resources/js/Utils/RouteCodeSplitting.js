/**
 * Route-Level Code Splitting Configuration
 * Implements dynamic imports for major pages
 * Implements Requirements 2.3
 */

import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import lazyLoader from './LazyComponentLoader.js'
import { debugRoute } from './RouteDebugger.js'

/**
 * Route patterns that should be code-split
 */
const ROUTE_PATTERNS = {
    // Authentication routes
    'Auth/': 'route-auth',
    
    // Dashboard and main pages
    'Dashboard': 'route-dashboard',
    'Dashboard/': 'route-dashboard',
    
    // User management
    'Students/': 'route-students',
    'Student/': 'route-students',
    'Teachers/': 'route-teachers',
    'Teacher/': 'route-teachers',
    'Profile/': 'route-profile',
    
    // Educational features
    'QuizManagement/': 'route-quiz',
    'QuestionManagement/': 'route-questions',
    'CourseManagement/': 'route-courses',
    'Lessons/': 'route-lessons',
    'WeeklyPlans/': 'route-weekly-plans',
    'VocabularyFlashcards/': 'route-vocabulary',
    
    // Communication
    'Conversations/': 'route-chat',
    'Chat/': 'route-chat',
    'PrivateChat/': 'route-chat',
    'Notifications/': 'route-notifications',
    
    // Management tools
    'my_table_mnger/': 'route-table-manager',
    'project_manager/': 'route-project-manager',
    'dailyTasks/': 'route-daily-tasks',
    
    // Documentation and utilities
    'Documentation/': 'route-docs',
    'DocumentationPortal/': 'route-docs'
}

/**
 * Heavy components that should be lazy loaded
 */
const HEAVY_COMPONENTS = [
    'CameraCapture',
    'PDFViewer',
    'SimplePDFViewer',
    'QuizBuilder',
    'QuestionEditor',
    'ChartComponent',
    'Leaderboard',
    'TopLeaderboard'
]

/**
 * Enhanced page resolver with code splitting
 * @param {string} name - Page name/path
 * @returns {Promise<Component>}
 */
export function resolvePageWithCodeSplitting(name) {
    // Use the standard Laravel Vite helper which handles dynamic imports properly
    return resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue'))
        .then(module => {
            // Set default layout if none is specified
            if (!module.default.layout) {
                // Lazy load the default layout
                module.default.layout = () => import('../Layouts/AppLayoutDefault.vue')
            }
            return module
        })
        .catch(error => {
            console.error(`Failed to load route component ${name}:`, error.message)
            
            // Provide more helpful error information
            if (error.message.includes('Page not found')) {
                const debug = debugRoute(name)
                console.warn(`Route ${name} does not exist.`)
                
                if (debug.suggestion) {
                    console.warn(`Did you mean: ${debug.suggestion}?`)
                }
                
                if (debug.similar.length > 1) {
                    console.warn(`Similar routes: ${debug.similar.slice(0, 3).join(', ')}`)
                }
                
                if (import.meta.env.DEV) {
                    console.warn('Use window.debugRoutes.logAvailableRoutes() to see all available routes')
                }
            }
            
            throw error
        })
}

/**
 * Get the chunk name for a route
 * @param {string} routeName - Route name/path
 * @returns {string|null}
 */
function getRouteChunk(routeName) {
    for (const [pattern, chunk] of Object.entries(ROUTE_PATTERNS)) {
        if (routeName.includes(pattern)) {
            return chunk
        }
    }
    return null
}

/**
 * Preload critical routes
 * @param {Array<string>} routes - Routes to preload
 */
export function preloadCriticalRoutes(routes = []) {
    const criticalRoutes = routes.length > 0 ? routes : [
        'Dashboard',
        'Welcome'
    ]
    
    // Preload after a short delay to not block initial load
    setTimeout(() => {
        criticalRoutes.forEach(route => {
            lazyLoader.preloadComponent(
                route,
                () => resolvePageComponent(`./Pages/${route}.vue`, import.meta.glob('./Pages/**/*.vue'))
                    .catch(error => {
                        console.warn(`Could not preload route ${route}:`, error.message)
                        return null
                    })
            )
        })
    }, 2000)
}

/**
 * Preload route based on user interaction hints
 * @param {string} routeName - Route to preload
 */
export function preloadRoute(routeName) {
    if (!lazyLoader.isCached(routeName) && !lazyLoader.isLoading(routeName)) {
        lazyLoader.preloadComponent(
            routeName,
            () => resolvePageComponent(`./Pages/${routeName}.vue`, import.meta.glob('./Pages/**/*.vue'))
                .catch(error => {
                    console.warn(`Could not preload route ${routeName}:`, error.message)
                    return null
                })
        )
    }
}

/**
 * Create a lazy route component with proper error handling
 * @param {string} routePath - Path to the route component
 * @param {Object} options - Additional options
 * @returns {AsyncComponent}
 */
export function createLazyRoute(routePath, options = {}) {
    return lazyLoader.createAsyncComponent(
        () => resolvePageComponent(`./Pages/${routePath}.vue`, import.meta.glob('./Pages/**/*.vue')),
        {
            delay: 100,
            timeout: 10000,
            loadingMessage: `Loading ${routePath.split('/').pop()}...`,
            ...options
        }
    )
}

/**
 * Check if a component should be lazy loaded
 * @param {string} componentName - Component name
 * @returns {boolean}
 */
export function shouldLazyLoad(componentName) {
    return HEAVY_COMPONENTS.some(heavy => componentName.includes(heavy))
}

/**
 * Get route performance metrics
 * @returns {Object}
 */
export function getRouteMetrics() {
    return {
        cacheStats: lazyLoader.getCacheStats(),
        routePatterns: Object.keys(ROUTE_PATTERNS).length,
        heavyComponents: HEAVY_COMPONENTS.length
    }
}

/**
 * Initialize route code splitting
 */
export function initializeRouteCodeSplitting() {
    // Preload critical routes after initial load
    if (typeof window !== 'undefined') {
        window.addEventListener('load', () => {
            preloadCriticalRoutes()
        })
        
        // Preload routes on hover/focus for better UX
        document.addEventListener('mouseover', (event) => {
            const link = event.target.closest('a[href]')
            if (link && link.href.includes('/')) {
                const routePath = extractRouteFromUrl(link.href)
                if (routePath) {
                    preloadRoute(routePath)
                }
            }
        })
    }
}

/**
 * Extract route path from URL
 * @param {string} url - Full URL
 * @returns {string|null}
 */
function extractRouteFromUrl(url) {
    try {
        const urlObj = new URL(url)
        const pathname = urlObj.pathname
        // Remove leading slash and convert to component path
        return pathname.substring(1).replace(/\//g, '/')
    } catch {
        return null
    }
}

export default {
    resolvePageWithCodeSplitting,
    preloadCriticalRoutes,
    preloadRoute,
    createLazyRoute,
    shouldLazyLoad,
    getRouteMetrics,
    initializeRouteCodeSplitting
}