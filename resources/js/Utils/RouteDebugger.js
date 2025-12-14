/**
 * Route Debugger Utility
 * Helps debug route loading issues
 */

/**
 * Get all available routes from the glob pattern
 * @returns {Array<string>}
 */
export function getAvailableRoutes() {
    const pages = import.meta.glob('../Pages/**/*.vue')
    return Object.keys(pages).map(path => {
        // Convert from './Pages/path/to/Component.vue' to 'path/to/Component'
        return path.replace('../Pages/', '').replace('.vue', '')
    })
}

/**
 * Check if a route exists
 * @param {string} routeName - Route name to check
 * @returns {boolean}
 */
export function routeExists(routeName) {
    const availableRoutes = getAvailableRoutes()
    return availableRoutes.includes(routeName)
}

/**
 * Find similar routes (for suggestions)
 * @param {string} routeName - Route name to find similar routes for
 * @returns {Array<string>}
 */
export function findSimilarRoutes(routeName) {
    const availableRoutes = getAvailableRoutes()
    const routeLower = routeName.toLowerCase()
    
    return availableRoutes.filter(route => {
        const routeNameLower = route.toLowerCase()
        return routeNameLower.includes(routeLower) || 
               routeLower.includes(routeNameLower) ||
               levenshteinDistance(routeLower, routeNameLower) <= 3
    })
}

/**
 * Calculate Levenshtein distance between two strings
 * @param {string} a 
 * @param {string} b 
 * @returns {number}
 */
function levenshteinDistance(a, b) {
    const matrix = []
    
    for (let i = 0; i <= b.length; i++) {
        matrix[i] = [i]
    }
    
    for (let j = 0; j <= a.length; j++) {
        matrix[0][j] = j
    }
    
    for (let i = 1; i <= b.length; i++) {
        for (let j = 1; j <= a.length; j++) {
            if (b.charAt(i - 1) === a.charAt(j - 1)) {
                matrix[i][j] = matrix[i - 1][j - 1]
            } else {
                matrix[i][j] = Math.min(
                    matrix[i - 1][j - 1] + 1,
                    matrix[i][j - 1] + 1,
                    matrix[i - 1][j] + 1
                )
            }
        }
    }
    
    return matrix[b.length][a.length]
}

/**
 * Log all available routes to console
 */
export function logAvailableRoutes() {
    const routes = getAvailableRoutes()
    console.group('📄 Available Routes')
    routes.sort().forEach(route => {
        console.log(`  - ${route}`)
    })
    console.groupEnd()
}

/**
 * Enhanced route resolver with debugging
 * @param {string} routeName - Route name
 * @returns {Object}
 */
export function debugRoute(routeName) {
    const exists = routeExists(routeName)
    const similar = exists ? [] : findSimilarRoutes(routeName)
    
    return {
        routeName,
        exists,
        similar,
        suggestion: similar.length > 0 ? similar[0] : null
    }
}

// Expose debugging functions globally in development
if (typeof window !== 'undefined' && import.meta.env.DEV) {
    window.debugRoutes = {
        getAvailableRoutes,
        routeExists,
        findSimilarRoutes,
        logAvailableRoutes,
        debugRoute
    }
}

export default {
    getAvailableRoutes,
    routeExists,
    findSimilarRoutes,
    logAvailableRoutes,
    debugRoute
}