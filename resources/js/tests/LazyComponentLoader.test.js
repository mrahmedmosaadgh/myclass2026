/**
 * Tests for LazyComponentLoader utility
 * Validates Requirements 2.3, 4.1, 4.5
 */

import { describe, it, expect, vi, beforeEach } from 'vitest'
import lazyLoader from '../utils/LazyComponentLoader.js'

describe('LazyComponentLoader', () => {
    beforeEach(() => {
        // Clear cache before each test
        lazyLoader.clearCache()
    })

    it('should load and cache components', async () => {
        const mockComponent = { name: 'TestComponent' }
        const mockImport = vi.fn().mockResolvedValue({ default: mockComponent })
        
        const component = await lazyLoader.loadComponent('test', mockImport)
        
        expect(component).toBe(mockComponent)
        expect(mockImport).toHaveBeenCalledTimes(1)
        expect(lazyLoader.isCached('test')).toBe(true)
    })

    it('should return cached component on subsequent calls', async () => {
        const mockComponent = { name: 'TestComponent' }
        const mockImport = vi.fn().mockResolvedValue({ default: mockComponent })
        
        // First call
        await lazyLoader.loadComponent('test', mockImport)
        
        // Second call should use cache
        const component = await lazyLoader.loadComponent('test', mockImport)
        
        expect(component).toBe(mockComponent)
        expect(mockImport).toHaveBeenCalledTimes(1) // Should not be called again
    })

    it('should handle loading errors gracefully', async () => {
        const mockError = new Error('Failed to load')
        const mockImport = vi.fn().mockRejectedValue(mockError)
        
        await expect(lazyLoader.loadComponent('test', mockImport)).rejects.toThrow('Failed to load')
        expect(lazyLoader.isCached('test')).toBe(false)
    })

    it('should track loading state correctly', async () => {
        const mockComponent = { name: 'TestComponent' }
        const mockImport = vi.fn().mockImplementation(() => 
            new Promise(resolve => setTimeout(() => resolve({ default: mockComponent }), 100))
        )
        
        const loadPromise = lazyLoader.loadComponent('test', mockImport)
        
        expect(lazyLoader.isLoading('test')).toBe(true)
        
        await loadPromise
        
        expect(lazyLoader.isLoading('test')).toBe(false)
        expect(lazyLoader.isCached('test')).toBe(true)
    })

    it('should preload components without blocking', () => {
        const mockComponent = { name: 'TestComponent' }
        const mockImport = vi.fn().mockResolvedValue({ default: mockComponent })
        
        lazyLoader.preloadComponent('test', mockImport)
        
        expect(mockImport).toHaveBeenCalledTimes(1)
        // Should not block execution
    })

    it('should provide accurate cache statistics', async () => {
        const mockComponent1 = { name: 'Component1' }
        const mockComponent2 = { name: 'Component2' }
        const mockImport1 = vi.fn().mockResolvedValue({ default: mockComponent1 })
        const mockImport2 = vi.fn().mockImplementation(() => 
            new Promise(resolve => setTimeout(() => resolve({ default: mockComponent2 }), 100))
        )
        
        // Load one component
        await lazyLoader.loadComponent('test1', mockImport1)
        
        // Start loading another
        const loadPromise = lazyLoader.loadComponent('test2', mockImport2)
        
        const stats = lazyLoader.getCacheStats()
        expect(stats.cached).toBe(1)
        expect(stats.loading).toBe(1)
        
        await loadPromise
        
        const finalStats = lazyLoader.getCacheStats()
        expect(finalStats.cached).toBe(2)
        expect(finalStats.loading).toBe(0)
    })

    it('should clear cache correctly', async () => {
        const mockComponent = { name: 'TestComponent' }
        const mockImport = vi.fn().mockResolvedValue({ default: mockComponent })
        
        await lazyLoader.loadComponent('test', mockImport)
        expect(lazyLoader.isCached('test')).toBe(true)
        
        lazyLoader.clearCache()
        expect(lazyLoader.isCached('test')).toBe(false)
        
        const stats = lazyLoader.getCacheStats()
        expect(stats.cached).toBe(0)
    })

    it('should unload specific components', async () => {
        const mockComponent1 = { name: 'Component1' }
        const mockComponent2 = { name: 'Component2' }
        const mockImport1 = vi.fn().mockResolvedValue({ default: mockComponent1 })
        const mockImport2 = vi.fn().mockResolvedValue({ default: mockComponent2 })
        
        await lazyLoader.loadComponent('test1', mockImport1)
        await lazyLoader.loadComponent('test2', mockImport2)
        
        expect(lazyLoader.isCached('test1')).toBe(true)
        expect(lazyLoader.isCached('test2')).toBe(true)
        
        lazyLoader.unloadComponent('test1')
        
        expect(lazyLoader.isCached('test1')).toBe(false)
        expect(lazyLoader.isCached('test2')).toBe(true)
    })
})