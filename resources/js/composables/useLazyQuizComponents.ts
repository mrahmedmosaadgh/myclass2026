/**
 * Composable for lazy loading quiz components
 * 
 * This composable provides lazy-loaded versions of quiz components
 * to improve initial page load performance by code splitting.
 * 
 * @module useLazyQuizComponents
 */

import { defineAsyncComponent, Component } from 'vue'

/**
 * Loading component shown while async component is loading
 */
const LoadingComponent = {
  template: `
    <div class="component-loading" role="status" aria-live="polite">
      <div class="loading-spinner" aria-hidden="true"></div>
      <span class="sr-only">Loading component...</span>
    </div>
  `
}

/**
 * Error component shown if async component fails to load
 */
const ErrorComponent = {
  template: `
    <div class="component-error" role="alert">
      <p>Failed to load component. Please refresh the page.</p>
    </div>
  `
}

/**
 * Options for async component loading
 */
const asyncComponentOptions = {
  loadingComponent: LoadingComponent,
  errorComponent: ErrorComponent,
  delay: 200, // Show loading component after 200ms
  timeout: 10000 // Timeout after 10 seconds
}

/**
 * Composable that provides lazy-loaded quiz components
 * 
 * @returns Object containing lazy-loaded component definitions
 * 
 * @example
 * ```typescript
 * import { useLazyQuizComponents } from '@/composables/useLazyQuizComponents'
 * 
 * const { QuizEngine, ProgressIndicator } = useLazyQuizComponents()
 * ```
 */
export function useLazyQuizComponents() {
  /**
   * Main QuizEngine component (lazy loaded)
   */
  const QuizEngine = defineAsyncComponent({
    loader: () => import('@/Pages/my_table_mnger/lesson_presentation/quiz/QuizEngine.vue'),
    ...asyncComponentOptions
  })

  /**
   * ProgressIndicator component (lazy loaded)
   */
  const ProgressIndicator = defineAsyncComponent({
    loader: () => import('@/Pages/my_table_mnger/lesson_presentation/quiz/components/ProgressIndicator.vue')
      .catch(() => {
        // Fallback if component doesn't exist yet
        console.warn('ProgressIndicator component not found, using placeholder')
        return { template: '<div>Progress Indicator</div>' }
      }),
    ...asyncComponentOptions
  })

  /**
   * QuestionRenderer component (lazy loaded)
   */
  const QuestionRenderer = defineAsyncComponent({
    loader: () => import('@/Pages/my_table_mnger/lesson_presentation/quiz/components/QuestionRenderer.vue')
      .catch(() => {
        console.warn('QuestionRenderer component not found, using placeholder')
        return { template: '<div>Question Renderer</div>' }
      }),
    ...asyncComponentOptions
  })

  /**
   * NavigationControls component (lazy loaded)
   */
  const NavigationControls = defineAsyncComponent({
    loader: () => import('@/Pages/my_table_mnger/lesson_presentation/quiz/components/NavigationControls.vue')
      .catch(() => {
        console.warn('NavigationControls component not found, using placeholder')
        return { template: '<div>Navigation Controls</div>' }
      }),
    ...asyncComponentOptions
  })

  /**
   * QuestionNavigator component (lazy loaded)
   */
  const QuestionNavigator = defineAsyncComponent({
    loader: () => import('@/Pages/my_table_mnger/lesson_presentation/quiz/components/QuestionNavigator.vue')
      .catch(() => {
        console.warn('QuestionNavigator component not found, using placeholder')
        return { template: '<div>Question Navigator</div>' }
      }),
    ...asyncComponentOptions
  })

  /**
   * ExplanationPanel component (lazy loaded)
   */
  const ExplanationPanel = defineAsyncComponent({
    loader: () => import('@/Pages/my_table_mnger/lesson_presentation/quiz/components/ExplanationPanel.vue')
      .catch(() => {
        console.warn('ExplanationPanel component not found, using placeholder')
        return { template: '<div>Explanation Panel</div>' }
      }),
    ...asyncComponentOptions
  })

  /**
   * OptionItem component (lazy loaded)
   */
  const OptionItem = defineAsyncComponent({
    loader: () => import('@/Pages/my_table_mnger/lesson_presentation/quiz/components/OptionItem.vue')
      .catch(() => {
        console.warn('OptionItem component not found, using placeholder')
        return { template: '<div>Option Item</div>' }
      }),
    ...asyncComponentOptions
  })

  /**
   * MultipleChoiceQuestion component (lazy loaded)
   */
  const MultipleChoiceQuestion = defineAsyncComponent({
    loader: () => import('@/Pages/my_table_mnger/lesson_presentation/quiz/components/MultipleChoiceQuestion.vue')
      .catch(() => {
        console.warn('MultipleChoiceQuestion component not found, using placeholder')
        return { template: '<div>Multiple Choice Question</div>' }
      }),
    ...asyncComponentOptions
  })

  /**
   * TrueFalseQuestion component (lazy loaded)
   */
  const TrueFalseQuestion = defineAsyncComponent({
    loader: () => import('@/Pages/my_table_mnger/lesson_presentation/quiz/components/TrueFalseQuestion.vue')
      .catch(() => {
        console.warn('TrueFalseQuestion component not found, using placeholder')
        return { template: '<div>True/False Question</div>' }
      }),
    ...asyncComponentOptions
  })

  /**
   * FillBlankQuestion component (lazy loaded)
   */
  const FillBlankQuestion = defineAsyncComponent({
    loader: () => import('@/Pages/my_table_mnger/lesson_presentation/quiz/components/FillBlankQuestion.vue')
      .catch(() => {
        console.warn('FillBlankQuestion component not found, using placeholder')
        return { template: '<div>Fill Blank Question</div>' }
      }),
    ...asyncComponentOptions
  })

  /**
   * MultiSelectQuestion component (lazy loaded)
   */
  const MultiSelectQuestion = defineAsyncComponent({
    loader: () => import('@/Pages/my_table_mnger/lesson_presentation/quiz/components/MultiSelectQuestion.vue')
      .catch(() => {
        console.warn('MultiSelectQuestion component not found, using placeholder')
        return { template: '<div>Multi Select Question</div>' }
      }),
    ...asyncComponentOptions
  })

  return {
    QuizEngine,
    ProgressIndicator,
    QuestionRenderer,
    NavigationControls,
    QuestionNavigator,
    ExplanationPanel,
    OptionItem,
    MultipleChoiceQuestion,
    TrueFalseQuestion,
    FillBlankQuestion,
    MultiSelectQuestion
  }
}

/**
 * Preload quiz components for better performance
 * 
 * Call this function when you know the user will need quiz components soon
 * (e.g., when navigating to a lesson that contains quizzes)
 * 
 * @example
 * ```typescript
 * import { preloadQuizComponents } from '@/composables/useLazyQuizComponents'
 * 
 * // Preload when user navigates to lesson page
 * onMounted(() => {
 *   preloadQuizComponents()
 * })
 * ```
 */
export function preloadQuizComponents(): void {
  // Preload main QuizEngine component
  import('@/Pages/my_table_mnger/lesson_presentation/quiz/QuizEngine.vue')
    .catch(() => console.warn('Failed to preload QuizEngine'))

  // Preload commonly used components
  const componentsToPreload = [
    '@/Pages/my_table_mnger/lesson_presentation/quiz/components/ProgressIndicator.vue',
    '@/Pages/my_table_mnger/lesson_presentation/quiz/components/QuestionRenderer.vue',
    '@/Pages/my_table_mnger/lesson_presentation/quiz/components/NavigationControls.vue',
    '@/Pages/my_table_mnger/lesson_presentation/quiz/components/OptionItem.vue'
  ]

  componentsToPreload.forEach(component => {
    import(component).catch(() => {
      // Silently fail if component doesn't exist
    })
  })
}
