import { ref, shallowRef } from 'vue'

/**
 * Composable for lazy loading PDF components
 * Provides loading states and error handling for PDF viewer components
 */
export function useLazyPDFComponents() {
  const isLoading = ref(false)
  const error = ref(null)
  const loadedComponents = shallowRef(new Map())

  /**
   * Lazy load a PDF component
   * @param {string} componentName - Name of the component to load
   * @param {Function} importFn - Dynamic import function
   * @returns {Promise<Component>}
   */
  const loadPDFComponent = async (componentName, importFn) => {
    // Return cached component if already loaded
    if (loadedComponents.value.has(componentName)) {
      return loadedComponents.value.get(componentName)
    }

    try {
      isLoading.value = true
      error.value = null

      console.log(`Loading PDF component: ${componentName}`)
      
      // Dynamic import with timeout
      const timeoutPromise = new Promise((_, reject) => {
        setTimeout(() => reject(new Error('Component load timeout')), 10000)
      })

      const componentModule = await Promise.race([importFn(), timeoutPromise])
      const component = componentModule.default

      // Cache the loaded component
      loadedComponents.value.set(componentName, component)
      
      console.log(`Successfully loaded PDF component: ${componentName}`)
      return component

    } catch (err) {
      console.error(`Failed to load PDF component ${componentName}:`, err)
      error.value = err
      throw err
    } finally {
      isLoading.value = false
    }
  }

  /**
   * Preload PDF components for better performance
   * @param {Array<{name: string, importFn: Function}>} components
   */
  const preloadPDFComponents = async (components) => {
    const preloadPromises = components.map(({ name, importFn }) => 
      loadPDFComponent(name, importFn).catch(err => {
        console.warn(`Failed to preload ${name}:`, err)
        return null
      })
    )

    await Promise.allSettled(preloadPromises)
  }

  /**
   * Clear component cache
   */
  const clearCache = () => {
    loadedComponents.value.clear()
  }

  /**
   * Get loading state for a specific component
   * @param {string} componentName
   * @returns {boolean}
   */
  const isComponentLoaded = (componentName) => {
    return loadedComponents.value.has(componentName)
  }

  return {
    isLoading,
    error,
    loadPDFComponent,
    preloadPDFComponents,
    clearCache,
    isComponentLoaded,
    loadedComponentsCount: () => loadedComponents.value.size
  }
}

/**
 * PDF Component import functions
 * These are separated to enable better tree shaking
 */
export const pdfComponentImports = {
  ReusableHtmlViewer: () => import('@/Pages/print_html/components/ReusableHtmlViewer.vue'),
  PrintLayout: () => import('@/Pages/print_html/components/PrintLayout.vue'),
  MathRenderer: () => import('@/Pages/print_html/MathRenderer.vue')
}