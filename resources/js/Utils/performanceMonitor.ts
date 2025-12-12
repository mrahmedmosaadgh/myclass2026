/**
 * Performance monitoring utilities for the quiz system
 * 
 * Provides tools for measuring and logging performance metrics
 * to help identify bottlenecks and optimize the application.
 * 
 * @module performanceMonitor
 */

/**
 * Performance metric data structure
 */
interface PerformanceMetric {
  name: string
  duration: number
  timestamp: number
  metadata?: Record<string, any>
}

/**
 * Performance monitor class
 */
class PerformanceMonitor {
  private metrics: PerformanceMetric[] = []
  private timers: Map<string, number> = new Map()
  private enabled: boolean = true

  /**
   * Enable or disable performance monitoring
   */
  setEnabled(enabled: boolean): void {
    this.enabled = enabled
  }

  /**
   * Check if monitoring is enabled
   */
  isEnabled(): boolean {
    return this.enabled
  }

  /**
   * Start timing an operation
   * 
   * @param name - Unique name for this timer
   * @example
   * ```typescript
   * performanceMonitor.startTimer('quiz-load')
   * // ... perform operation
   * performanceMonitor.endTimer('quiz-load')
   * ```
   */
  startTimer(name: string): void {
    if (!this.enabled) return
    this.timers.set(name, performance.now())
  }

  /**
   * End timing an operation and record the metric
   * 
   * @param name - Name of the timer to end
   * @param metadata - Optional metadata to attach to the metric
   * @returns Duration in milliseconds, or null if timer not found
   */
  endTimer(name: string, metadata?: Record<string, any>): number | null {
    if (!this.enabled) return null

    const startTime = this.timers.get(name)
    if (!startTime) {
      console.warn(`Timer "${name}" not found`)
      return null
    }

    const duration = performance.now() - startTime
    this.timers.delete(name)

    this.recordMetric({
      name,
      duration,
      timestamp: Date.now(),
      metadata
    })

    return duration
  }

  /**
   * Measure the duration of a function execution
   * 
   * @param name - Name for this measurement
   * @param fn - Function to measure
   * @param metadata - Optional metadata
   * @returns Result of the function
   * 
   * @example
   * ```typescript
   * const result = await performanceMonitor.measure('fetch-questions', async () => {
   *   return await fetchQuestions()
   * })
   * ```
   */
  async measure<T>(
    name: string,
    fn: () => T | Promise<T>,
    metadata?: Record<string, any>
  ): Promise<T> {
    if (!this.enabled) return fn()

    this.startTimer(name)
    try {
      const result = await fn()
      this.endTimer(name, metadata)
      return result
    } catch (error) {
      this.endTimer(name, { ...metadata, error: true })
      throw error
    }
  }

  /**
   * Record a performance metric
   */
  private recordMetric(metric: PerformanceMetric): void {
    this.metrics.push(metric)

    // Log slow operations (> 1 second)
    if (metric.duration > 1000) {
      console.warn(`Slow operation detected: ${metric.name} took ${metric.duration.toFixed(2)}ms`, metric.metadata)
    }

    // Keep only last 100 metrics to prevent memory leaks
    if (this.metrics.length > 100) {
      this.metrics.shift()
    }
  }

  /**
   * Get all recorded metrics
   */
  getMetrics(): PerformanceMetric[] {
    return [...this.metrics]
  }

  /**
   * Get metrics by name
   */
  getMetricsByName(name: string): PerformanceMetric[] {
    return this.metrics.filter(m => m.name === name)
  }

  /**
   * Get average duration for a metric
   */
  getAverageDuration(name: string): number {
    const metrics = this.getMetricsByName(name)
    if (metrics.length === 0) return 0

    const total = metrics.reduce((sum, m) => sum + m.duration, 0)
    return total / metrics.length
  }

  /**
   * Get performance summary
   */
  getSummary(): Record<string, { count: number; avg: number; min: number; max: number }> {
    const summary: Record<string, { count: number; avg: number; min: number; max: number }> = {}

    this.metrics.forEach(metric => {
      if (!summary[metric.name]) {
        summary[metric.name] = {
          count: 0,
          avg: 0,
          min: Infinity,
          max: -Infinity
        }
      }

      const s = summary[metric.name]
      s.count++
      s.min = Math.min(s.min, metric.duration)
      s.max = Math.max(s.max, metric.duration)
    })

    // Calculate averages
    Object.keys(summary).forEach(name => {
      const metrics = this.getMetricsByName(name)
      const total = metrics.reduce((sum, m) => sum + m.duration, 0)
      summary[name].avg = total / metrics.length
    })

    return summary
  }

  /**
   * Clear all metrics
   */
  clear(): void {
    this.metrics = []
    this.timers.clear()
  }

  /**
   * Log performance summary to console
   */
  logSummary(): void {
    const summary = this.getSummary()
    console.table(summary)
  }

  /**
   * Measure Web Vitals (Core Web Vitals)
   */
  measureWebVitals(): void {
    if (!this.enabled || typeof window === 'undefined') return

    // Largest Contentful Paint (LCP)
    if ('PerformanceObserver' in window) {
      try {
        const lcpObserver = new PerformanceObserver((list) => {
          const entries = list.getEntries()
          const lastEntry = entries[entries.length - 1] as any
          
          this.recordMetric({
            name: 'web-vital-lcp',
            duration: lastEntry.renderTime || lastEntry.loadTime,
            timestamp: Date.now(),
            metadata: { type: 'Largest Contentful Paint' }
          })
        })
        lcpObserver.observe({ entryTypes: ['largest-contentful-paint'] })
      } catch (e) {
        // LCP not supported
      }

      // First Input Delay (FID)
      try {
        const fidObserver = new PerformanceObserver((list) => {
          const entries = list.getEntries()
          entries.forEach((entry: any) => {
            this.recordMetric({
              name: 'web-vital-fid',
              duration: entry.processingStart - entry.startTime,
              timestamp: Date.now(),
              metadata: { type: 'First Input Delay' }
            })
          })
        })
        fidObserver.observe({ entryTypes: ['first-input'] })
      } catch (e) {
        // FID not supported
      }

      // Cumulative Layout Shift (CLS)
      try {
        let clsValue = 0
        const clsObserver = new PerformanceObserver((list) => {
          const entries = list.getEntries()
          entries.forEach((entry: any) => {
            if (!entry.hadRecentInput) {
              clsValue += entry.value
            }
          })
          
          this.recordMetric({
            name: 'web-vital-cls',
            duration: clsValue,
            timestamp: Date.now(),
            metadata: { type: 'Cumulative Layout Shift' }
          })
        })
        clsObserver.observe({ entryTypes: ['layout-shift'] })
      } catch (e) {
        // CLS not supported
      }
    }
  }

  /**
   * Measure navigation timing
   */
  measureNavigationTiming(): void {
    if (!this.enabled || typeof window === 'undefined') return

    if (window.performance && window.performance.timing) {
      const timing = window.performance.timing
      const navigationStart = timing.navigationStart

      // DNS lookup time
      this.recordMetric({
        name: 'navigation-dns',
        duration: timing.domainLookupEnd - timing.domainLookupStart,
        timestamp: Date.now()
      })

      // TCP connection time
      this.recordMetric({
        name: 'navigation-tcp',
        duration: timing.connectEnd - timing.connectStart,
        timestamp: Date.now()
      })

      // Request time
      this.recordMetric({
        name: 'navigation-request',
        duration: timing.responseStart - timing.requestStart,
        timestamp: Date.now()
      })

      // Response time
      this.recordMetric({
        name: 'navigation-response',
        duration: timing.responseEnd - timing.responseStart,
        timestamp: Date.now()
      })

      // DOM processing time
      this.recordMetric({
        name: 'navigation-dom',
        duration: timing.domComplete - timing.domLoading,
        timestamp: Date.now()
      })

      // Total page load time
      this.recordMetric({
        name: 'navigation-total',
        duration: timing.loadEventEnd - navigationStart,
        timestamp: Date.now()
      })
    }
  }
}

// Export singleton instance
export const performanceMonitor = new PerformanceMonitor()

// Disable in production by default (can be enabled via console)
if (import.meta.env.PROD) {
  performanceMonitor.setEnabled(false)
}

// Make available in console for debugging
if (typeof window !== 'undefined') {
  (window as any).performanceMonitor = performanceMonitor
}

/**
 * Decorator for measuring method execution time
 * 
 * @example
 * ```typescript
 * class MyClass {
 *   @measurePerformance('my-method')
 *   async myMethod() {
 *     // method implementation
 *   }
 * }
 * ```
 */
export function measurePerformance(name: string) {
  return function (target: any, propertyKey: string, descriptor: PropertyDescriptor) {
    const originalMethod = descriptor.value

    descriptor.value = async function (...args: any[]) {
      return performanceMonitor.measure(name, () => originalMethod.apply(this, args))
    }

    return descriptor
  }
}

export default performanceMonitor
