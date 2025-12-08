import { useI18n } from 'vue-i18n';
import { computed } from 'vue';

/**
 * Composable for quiz internationalization
 * Provides easy access to quiz-specific translations
 */
export function useQuizI18n() {
  const { t, locale } = useI18n();
  
  // Check if current locale is RTL
  const isRtl = computed(() => locale.value === 'ar');
  
  // Quiz-specific translation helpers
  const quizT = (key, params = {}) => t(`quiz.${key}`, params);
  
  // Common translations
  const translations = {
    // Progress
    progress: (current, total) => quizT('progress', { current, total }),
    complete: (percentage) => quizT('complete', { percentage }),
    questionNumber: (number) => quizT('questionNumber', { number }),
    
    // Actions
    submit: () => quizT('submit'),
    next: () => quizT('next'),
    previous: () => quizT('previous'),
    finish: () => quizT('finish'),
    review: () => quizT('review'),
    startQuiz: () => quizT('startQuiz'),
    retakeQuiz: () => quizT('retakeQuiz'),
    
    // Feedback
    correct: () => quizT('correct'),
    incorrect: () => quizT('incorrect'),
    explanation: () => quizT('explanation'),
    hint: () => quizT('hint'),
    rationale: () => quizT('rationale'),
    correctAnswer: () => quizT('correctAnswer'),
    yourAnswer: () => quizT('yourAnswer'),
    
    // Results
    resultsTitle: () => quizT('results.title'),
    resultsScore: (correct, total) => quizT('results.score', { correct, total }),
    resultsPercentage: (percentage) => quizT('results.percentage', { percentage }),
    resultsPassed: () => quizT('results.passed'),
    resultsFailed: () => quizT('results.failed'),
    resultsTimeSpent: (time) => quizT('results.timeSpent', { time }),
    resultsCompletedAt: (time) => quizT('results.completedAt', { time }),
    resultsSummary: () => quizT('results.summary'),
    resultsCorrectAnswers: () => quizT('results.correctAnswers'),
    resultsIncorrectAnswers: () => quizT('results.incorrectAnswers'),
    resultsUnanswered: () => quizT('results.unanswered'),
    resultsReviewAnswers: () => quizT('results.reviewAnswers'),
    
    // Question types
    questionType: (type) => quizT(`questionTypes.${type}`),
    
    // Options
    option: (key) => quizT(`options.${key}`),
    optionTrue: () => quizT('options.true'),
    optionFalse: () => quizT('options.false'),
    selectAll: () => quizT('options.selectAll'),
    
    // Status
    statusLoading: () => quizT('status.loading'),
    statusSubmitting: () => quizT('status.submitting'),
    statusCalculating: () => quizT('status.calculating'),
    statusAnswered: () => quizT('status.answered'),
    statusUnanswered: () => quizT('status.unanswered'),
    statusCurrent: () => quizT('status.current'),
    
    // Errors
    errorLoadFailed: () => quizT('errors.loadFailed'),
    errorSubmitFailed: () => quizT('errors.submitFailed'),
    errorNoQuestions: () => quizT('errors.noQuestions'),
    errorInvalidAnswer: () => quizT('errors.invalidAnswer'),
    errorNetworkError: () => quizT('errors.networkError'),
    errorTimeout: () => quizT('errors.timeout'),
    errorSelectAnswer: () => quizT('errors.selectAnswer'),
    errorFillAnswer: () => quizT('errors.fillAnswer'),
    
    // Validation
    validationRequired: () => quizT('validation.required'),
    validationMinLength: (min) => quizT('validation.minLength', { min }),
    validationMaxLength: (max) => quizT('validation.maxLength', { max }),
    validationSelectAtLeastOne: () => quizT('validation.selectAtLeastOne'),
    
    // Time
    timeSeconds: (count) => quizT('time.seconds', { count }),
    timeMinutes: (count) => quizT('time.minutes', { count }),
    timeHours: (count) => quizT('time.hours', { count }),
    timeRemaining: (time) => quizT('time.remaining', { time }),
    timeExpired: () => quizT('time.expired'),
    timeWarning: (time) => quizT('time.warning', { time }),
    
    // Accessibility
    a11yQuizRegion: () => quizT('a11y.quizRegion'),
    a11yProgressBar: (percentage) => quizT('a11y.progressBar', { percentage }),
    a11yQuestionOptions: (number) => quizT('a11y.questionOptions', { number }),
    a11ySelectedOption: () => quizT('a11y.selectedOption'),
    a11yCorrectOption: () => quizT('a11y.correctOption'),
    a11yIncorrectOption: () => quizT('a11y.incorrectOption'),
    a11yNavigationControls: () => quizT('a11y.navigationControls'),
    a11yQuestionNavigator: () => quizT('a11y.questionNavigator'),
    a11ySkipToResults: () => quizT('a11y.skipToResults'),
    a11yAnnounceCorrect: () => quizT('a11y.announceCorrect'),
    a11yAnnounceIncorrect: () => quizT('a11y.announceIncorrect'),
    a11yAnnounceProgress: (current, total) => quizT('a11y.announceProgress', { current, total }),
    a11yAnnounceComplete: (percentage) => quizT('a11y.announceComplete', { percentage }),
    
    // Import
    importTitle: () => quizT('import.title'),
    importSelectFile: () => quizT('import.selectFile'),
    importUploadFile: () => quizT('import.uploadFile'),
    importFileFormat: () => quizT('import.fileFormat'),
    importCsvFormat: () => quizT('import.csvFormat'),
    importExcelFormat: () => quizT('import.excelFormat'),
    importTemplateDownload: () => quizT('import.templateDownload'),
    importImporting: () => quizT('import.importing'),
    importSuccess: (count) => quizT('import.success', { count }),
    importPartialSuccess: (success, errors) => quizT('import.partialSuccess', { success, errors }),
    importFailed: () => quizT('import.failed'),
    importValidationErrors: () => quizT('import.validationErrors'),
    importRowError: (row, error) => quizT('import.rowError', { row, error })
  };
  
  return {
    t,
    quizT,
    locale,
    isRtl,
    ...translations
  };
}
