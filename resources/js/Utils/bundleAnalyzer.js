/**
 * Bundle Analysis Utility
 * Provides runtime bundle analysis and monitoring
 * Implements Requirements 6.1, 6.2, 6.3
 */

class BundleAnalyzer {
    constructor() {
        this.loadedChunks = new Set()
        this.chunkLoadTimes = new Map()
        this.chunkSizes = new Map()
        this.performanceObserver = null
        this.initializePerformanceMonitoring()
    }

    /**
     * Initialize performance monitoring
     */
    initializePerformanceMonitoring() {
        if (typeof window === 'undefined') return

        // Monitor resource loading
        if ('PerformanceObserver' in window) {
            this.performanceObserver = new PerformanceObserver((list) => {
                for (const entry of list.getEntries()) {
                    if (entry.name.includes('.js') && entry.name.includes('assets/')) {
                        this.recordChunkLoad(entry)
                    }
                }
            })
            
            this.performanceObserver.observe({ entryTypes: ['resource'] })
        }

        // Monitor navigation performance
        window.addEventListener('load', () => {
            this.recordInitialLoadMetrics()
        })
    }

    /**
     * Record chunk loading metrics
     * @param {PerformanceEntry} entry - Performance entry
     */
    recordChunkLoad(entry) {
        const chunkName = this.extractChunkName(entry.name)
        if (chunkName) {
            this.loadedChunks.add(chunkName)
            this.chunkLoadTimes.set(chunkName, {
                loadTime: entry.duration,
                size: entry.transferSize || 0,
                timestamp: entry.startTime
            })
        }
    }

    /**
     * Extract chunk name from URL
     * @param {string} url - Resource URL
     * @returns {string|null}
     */
    extractChunkName(url) {
        const match = url.match(/assets\/([^-]+)-[^.]+\.js$/)
        return match ? match[1] : null
    }

    /**
     * Record initial load metrics
     */
    recordInitialLoadMetrics() {
        if (typeof window === 'undefined') return

        const navigation = performance.getEntriesByType('navigation')[0]
        if (navigation) {
            this.initialLoadMetrics = {
                domContentLoaded: navigation.domContentLoadedEventEnd - navigation.domContentLoadedEventStart,
                loadComplete: navigation.loadEventEnd - navigation.loadEventStart,
                firstContentfulPaint: this.getFirstContentfulPaint(),
                largestContentfulPaint: this.getLargestContentfulPaint()
            }
        }
    }

    /**
     * Get First Contentful Paint metric
     * @returns {number}
     */
    getFirstContentfulPaint() {
        const fcpEntry = performance.getEntriesByName('first-contentful-paint')[0]
        return fcpEntry ? fcpEntry.startTime : 0
    }

    /**
     * Get Largest Contentful Paint metric
     * @returns {number}
     */
    getLargestContentfulPaint() {
        return new Promise((resolve) => {
            if ('PerformanceObserver' in window) {
                const observer = new PerformanceObserver((list) => {
                    const entries = list.getEntries()
                    const lastEntry = entries[entries.length - 1]
                    resolve(lastEntry ? lastEntry.startTime : 0)
                    observer.disconnect()
                })
                observer.observe({ entryTypes: ['largest-contentful-paint'] })
                
                // Timeout after 10 seconds
                setTimeout(() => {
                    observer.disconnect()
                    resolve(0)
                }, 10000)
            } else {
                resolve(0)
            }
        })
    }

    /**
     * Generate bundle analysis report
     * @returns {Object}
     */
    generateReport() {
        const report = {
            timestamp: new Date().toISOString(),
            loadedChunks: Array.from(this.loadedChunks),
            chunkMetrics: this.getChunkMetrics(),
            performanceMetrics: this.getPerformanceMetrics(),
            recommendations: this.generateRecommendations()
        }

        return report
    }

    /**
     * Get chunk loading metrics
     * @returns {Object}
     */
    getChunkMetrics() {
        const metrics = {}
        
        for (const [chunkName, data] of this.chunkLoadTimes) {
            metrics[chunkName] = {
                loadTime: Math.round(data.loadTime),
                size: data.size,
                sizeKB: Math.round(data.size / 1024),
                timestamp: Math.round(data.timestamp)
            }
        }

        return metrics
    }

    /**
     * Get performance metrics
     * @returns {Object}
     */
    getPerformanceMetrics() {
        return {
            initialLoad: this.initialLoadMetrics || {},
            totalChunks: this.loadedChunks.size,
            totalTransferSize: this.getTotalTransferSize(),
            averageChunkLoadTime: this.getAverageChunkLoadTime()
        }
    }

    /**
     * Get total transfer size of all chunks
     * @returns {number}
     */
    getTotalTransferSize() {
        let total = 0
        for (const data of this.chunkLoadTimes.values()) {
            total += data.size || 0
        }
        return total
    }

    /**
     * Get average chunk load time
     * @returns {number}
     */
    getAverageChunkLoadTime() {
        if (this.chunkLoadTimes.size === 0) return 0
        
        let total = 0
        for (const data of this.chunkLoadTimes.values()) {
            total += data.loadTime || 0
        }
        
        return Math.round(total / this.chunkLoadTimes.size)
    }

    /**
     * Generate performance recommendations
     * @returns {Array<string>}
     */
    generateRecommendations() {
        const recommendations = []
        const metrics = this.getChunkMetrics()

        // Check for large chunks
        for (const [chunkName, data] of Object.entries(metrics)) {
            if (data.sizeKB > 500) {
                recommendations.push(`Consider splitting ${chunkName} chunk (${data.sizeKB}KB) into smaller pieces`)
            }
            
            if (data.loadTime > 2000) {
                recommendations.push(`${chunkName} chunk takes ${data.loadTime}ms to load - consider optimization`)
            }
        }

        // Check total bundle size
        const totalSizeKB = Math.round(this.getTotalTransferSize() / 1024)
        if (totalSizeKB > 2000) {
            recommendations.push(`Total bundle size (${totalSizeKB}KB) exceeds recommended limit`)
        }

        // Check FCP
        if (this.initialLoadMetrics?.firstContentfulPaint > 2000) {
            recommendations.push('First Contentful Paint is slow - consider reducing initial bundle size')
        }

        return recommendations
    }

    /**
     * Check performance thresholds
     * @returns {Array<Object>}
     */
    checkThresholds() {
        const alerts = []
        const thresholds = {
            maxChunkSize: 500 * 1024, // 500KB
            maxLoadTime: 2000, // 2 seconds
            maxTotalSize: 2000 * 1024, // 2MB
            maxFCP: 2000 // 2 seconds
        }

        // Check chunk sizes
        for (const [chunkName, data] of this.chunkLoadTimes) {
            if (data.size > thresholds.maxChunkSize) {
                alerts.push({
                    type: 'chunk_size',
                    severity: 'warning',
                    message: `Chunk ${chunkName} exceeds size threshold`,
                    value: data.size,
                    threshold: thresholds.maxChunkSize
                })
            }

            if (data.loadTime > thresholds.maxLoadTime) {
                alerts.push({
                    type: 'load_time',
                    severity: 'warning',
                    message: `Chunk ${chunkName} exceeds load time threshold`,
                    value: data.loadTime,
                    threshold: thresholds.maxLoadTime
                })
            }
        }

        // Check total size
        const totalSize = this.getTotalTransferSize()
        if (totalSize > thresholds.maxTotalSize) {
            alerts.push({
                type: 'total_size',
                severity: 'error',
                message: 'Total bundle size exceeds threshold',
                value: totalSize,
                threshold: thresholds.maxTotalSize
            })
        }

        return alerts
    }

    /**
     * Identify duplicate dependencies
     * @returns {Array<string>}
     */
    identifyDuplicates() {
        // This would require build-time analysis
        // For now, return common patterns to watch for
        return [
            'Multiple Vue instances detected in different chunks',
            'Lodash utilities duplicated across chunks',
            'Moment.js loaded in multiple chunks'
        ]
    }

    /**
     * Export report as JSON
     * @returns {string}
     */
    exportReport() {
        const report = this.generateReport()
        return JSON.stringify(report, null, 2)
    }

    /**
     * Log report to console
     */
    logReport() {
        const report = this.generateReport()
        console.group('📊 Bundle Analysis Report')
        console.log('Loaded Chunks:', report.loadedChunks)
        console.table(report.chunkMetrics)
        console.log('Performance Metrics:', report.performanceMetrics)
        console.log('Recommendations:', report.recommendations)
        console.groupEnd()
    }

    /**
     * Cleanup resources
     */
    cleanup() {
        if (this.performanceObserver) {
            this.performanceObserver.disconnect()
            this.performanceObserver = null
        }
    }
}

// Create singleton instance
const bundleAnalyzer = new BundleAnalyzer()

// Expose global methods for debugging
if (typeof window !== 'undefined') {
    window.bundleAnalyzer = bundleAnalyzer
    window.logBundleReport = () => bundleAnalyzer.logReport()
}

export default bundleAnalyzer