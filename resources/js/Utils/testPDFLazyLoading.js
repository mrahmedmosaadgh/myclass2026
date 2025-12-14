/**
 * Test utility to verify PDF lazy loading implementation
 * This can be run in the browser console to test the lazy loading behavior
 */

import { logBundleAnalysis, verifyPDFCodeSplitting, monitorChunkLoading } from './bundleAnalyzer.js'

/**
 * Test PDF lazy loading functionality
 * @returns {Promise<Object>} Test results
 */
export async function testPDFLazyLoading() {
  console.group('🧪 Testing PDF Lazy Loading')
  
  const results = {
    bundleAnalysis: null,
    codeSplittingVerification: null,
    lazyLoadingTest: null,
    performanceMetrics: null
  }
  
  try {
    // 1. Analyze current bundle state
    console.log('📊 Analyzing bundle state...')
    results.bundleAnalysis = verifyPDFCodeSplitting()
    console.log('Bundle Analysis:', results.bundleAnalysis)
    
    // 2. Test lazy loading by dynamically importing PDF components
    console.log('🔄 Testing lazy loading...')
    const monitor = monitorChunkLoading('pdf-lazy-test')
    
    try {
      // Test LazyPDFViewer import
      const { default: LazyPDFViewer } = await import('@/Components/LazyPDFViewer.vue')
      console.log('✅ LazyPDFViewer loaded successfully')
      
      // Test LazyPDFAnnotator import
      const { default: LazyPDFAnnotator } = await import('@/Components/LazyPDFAnnotator.vue')
      console.log('✅ LazyPDFAnnotator loaded successfully')
      
      // Test LazySimplePDFViewer import
      const { default: LazySimplePDFViewer } = await import('@/Components/LazySimplePDFViewer.vue')
      console.log('✅ LazySimplePDFViewer loaded successfully')
      
      const performanceData = monitor.markLoaded()
      results.performanceMetrics = performanceData
      
      results.lazyLoadingTest = {
        success: true,
        componentsLoaded: ['LazyPDFViewer', 'LazyPDFAnnotator', 'LazySimplePDFViewer'],
        loadTime: performanceData.loadTime
      }
      
    } catch (error) {
      console.error('❌ Lazy loading test failed:', error)
      results.lazyLoadingTest = {
        success: false,
        error: error.message
      }
    }
    
    // 3. Log comprehensive bundle analysis
    logBundleAnalysis()
    
    // 4. Summary
    console.log('📋 Test Summary:')
    console.log('- PDF Code Splitting:', results.bundleAnalysis.verification.passed ? '✅ PASSED' : '❌ FAILED')
    console.log('- Lazy Loading:', results.lazyLoadingTest?.success ? '✅ PASSED' : '❌ FAILED')
    console.log('- Load Time:', results.performanceMetrics?.loadTime ? `${results.performanceMetrics.loadTime}ms` : 'N/A')
    
  } catch (error) {
    console.error('❌ Test failed:', error)
    results.error = error.message
  }
  
  console.groupEnd()
  return results
}

/**
 * Quick test to check if PDF components are in main bundle
 * @returns {boolean} True if PDF components are properly code-split
 */
export function quickPDFSplitTest() {
  const verification = verifyPDFCodeSplitting()
  console.log('🔍 Quick PDF Split Test:', verification.verification.passed ? '✅ PASSED' : '❌ FAILED')
  return verification.verification.passed
}

/**
 * Test that can be run from browser console
 */
if (typeof window !== 'undefined') {
  window.testPDFLazyLoading = testPDFLazyLoading
  window.quickPDFSplitTest = quickPDFSplitTest
}