import { useI18n } from 'vue-i18n';
import { computed, type ComputedRef, type Ref } from 'vue';

export interface QuizI18n {
    t: (key: string, params?: Record<string, any>) => string;
    quizT: (key: string, params?: Record<string, any>) => string;
    locale: Ref<string>;
    isRtl: ComputedRef<boolean>;

    // Progress
    progress: (current: number, total: number) => string;
    complete: (percentage: number) => string;
    questionNumber: (number: number) => string;

    // Actions
    submit: () => string;
    next: () => string;
    previous: () => string;
    finish: () => string;
    review: () => string;
    startQuiz: () => string;
    retakeQuiz: () => string;

    // Feedback
    correct: () => string;
    incorrect: () => string;
    explanation: () => string;
    hint: () => string;
    rationale: () => string;
    correctAnswer: () => string;
    yourAnswer: () => string;

    // Results
    resultsTitle: () => string;
    resultsScore: (correct: number, total: number) => string;
    resultsPercentage: (percentage: number) => string;
    resultsPassed: () => string;
    resultsFailed: () => string;
    resultsTimeSpent: (time: string) => string;
    resultsCompletedAt: (time: string) => string;
    resultsSummary: () => string;
    resultsCorrectAnswers: () => string;
    resultsIncorrectAnswers: () => string;
    resultsUnanswered: () => string;
    resultsReviewAnswers: () => string;

    // Question types
    questionType: (type: string) => string;

    // Options
    option: (key: string) => string;
    optionTrue: () => string;
    optionFalse: () => string;
    selectAll: () => string;

    // Status
    statusLoading: () => string;
    statusSubmitting: () => string;
    statusCalculating: () => string;
    statusAnswered: () => string;
    statusUnanswered: () => string;
    statusCurrent: () => string;

    // Errors
    errorLoadFailed: () => string;
    errorSubmitFailed: () => string;
    errorNoQuestions: () => string;
    errorInvalidAnswer: () => string;
    errorNetworkError: () => string;
    errorTimeout: () => string;
    errorSelectAnswer: () => string;
    errorFillAnswer: () => string;

    // Validation
    validationRequired: () => string;
    validationMinLength: (min: number) => string;
    validationMaxLength: (max: number) => string;
    validationSelectAtLeastOne: () => string;

    // Time
    timeSeconds: (count: number) => string;
    timeMinutes: (count: number) => string;
    timeHours: (count: number) => string;
    timeRemaining: (time: string) => string;
    timeExpired: () => string;
    timeWarning: (time: string) => string;

    // Accessibility
    a11yQuizRegion: () => string;
    a11yProgressBar: (percentage: number) => string;
    a11yQuestionOptions: (number: number) => string;
    a11ySelectedOption: () => string;
    a11yCorrectOption: () => string;
    a11yIncorrectOption: () => string;
    a11yNavigationControls: () => string;
    a11yQuestionNavigator: () => string;
    a11ySkipToResults: () => string;
    a11yAnnounceCorrect: () => string;
    a11yAnnounceIncorrect: () => string;
    a11yAnnounceProgress: (current: number, total: number) => string;
    a11yAnnounceComplete: (percentage: number) => string;

    // Import
    importTitle: () => string;
    importSelectFile: () => string;
    importUploadFile: () => string;
    importFileFormat: () => string;
    importCsvFormat: () => string;
    importExcelFormat: () => string;
    importTemplateDownload: () => string;
    importImporting: () => string;
    importSuccess: (count: number) => string;
    importPartialSuccess: (success: number, errors: number) => string;
    importFailed: () => string;
    importValidationErrors: () => string;
    importRowError: (row: number, error: string) => string;
}

/**
 * Composable for quiz internationalization
 * Provides easy access to quiz-specific translations
 */
export function useQuizI18n(): QuizI18n {
    const { t, locale } = useI18n();

    // Check if current locale is RTL
    const isRtl = computed(() => locale.value === 'ar');

    // Quiz-specific translation helpers
    const quizT = (key: string, params: Record<string, any> = {}) => t(`quiz.${key}`, params);

    // Common translations
    const translations = {
        // Progress
        progress: (current: number, total: number) => quizT('progress', { current, total }),
        complete: (percentage: number) => quizT('complete', { percentage }),
        questionNumber: (number: number) => quizT('questionNumber', { number }),

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
        resultsScore: (correct: number, total: number) => quizT('results.score', { correct, total }),
        resultsPercentage: (percentage: number) => quizT('results.percentage', { percentage }),
        resultsPassed: () => quizT('results.passed'),
        resultsFailed: () => quizT('results.failed'),
        resultsTimeSpent: (time: string) => quizT('results.timeSpent', { time }),
        resultsCompletedAt: (time: string) => quizT('results.completedAt', { time }),
        resultsSummary: () => quizT('results.summary'),
        resultsCorrectAnswers: () => quizT('results.correctAnswers'),
        resultsIncorrectAnswers: () => quizT('results.incorrectAnswers'),
        resultsUnanswered: () => quizT('results.unanswered'),
        resultsReviewAnswers: () => quizT('results.reviewAnswers'),

        // Question types
        questionType: (type: string) => quizT(`questionTypes.${type}`),

        // Options
        option: (key: string) => quizT(`options.${key}`),
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
        validationMinLength: (min: number) => quizT('validation.minLength', { min }),
        validationMaxLength: (max: number) => quizT('validation.maxLength', { max }),
        validationSelectAtLeastOne: () => quizT('validation.selectAtLeastOne'),

        // Time
        timeSeconds: (count: number) => quizT('time.seconds', { count }),
        timeMinutes: (count: number) => quizT('time.minutes', { count }),
        timeHours: (count: number) => quizT('time.hours', { count }),
        timeRemaining: (time: string) => quizT('time.remaining', { time }),
        timeExpired: () => quizT('time.expired'),
        timeWarning: (time: string) => quizT('time.warning', { time }),

        // Accessibility
        a11yQuizRegion: () => quizT('a11y.quizRegion'),
        a11yProgressBar: (percentage: number) => quizT('a11y.progressBar', { percentage }),
        a11yQuestionOptions: (number: number) => quizT('a11y.questionOptions', { number }),
        a11ySelectedOption: () => quizT('a11y.selectedOption'),
        a11yCorrectOption: () => quizT('a11y.correctOption'),
        a11yIncorrectOption: () => quizT('a11y.incorrectOption'),
        a11yNavigationControls: () => quizT('a11y.navigationControls'),
        a11yQuestionNavigator: () => quizT('a11y.questionNavigator'),
        a11ySkipToResults: () => quizT('a11y.skipToResults'),
        a11yAnnounceCorrect: () => quizT('a11y.announceCorrect'),
        a11yAnnounceIncorrect: () => quizT('a11y.announceIncorrect'),
        a11yAnnounceProgress: (current: number, total: number) => quizT('a11y.announceProgress', { current, total }),
        a11yAnnounceComplete: (percentage: number) => quizT('a11y.announceComplete', { percentage }),

        // Import
        importTitle: () => quizT('import.title'),
        importSelectFile: () => quizT('import.selectFile'),
        importUploadFile: () => quizT('import.uploadFile'),
        importFileFormat: () => quizT('import.fileFormat'),
        importCsvFormat: () => quizT('import.csvFormat'),
        importExcelFormat: () => quizT('import.excelFormat'),
        importTemplateDownload: () => quizT('import.templateDownload'),
        importImporting: () => quizT('import.importing'),
        importSuccess: (count: number) => quizT('import.success', { count }),
        importPartialSuccess: (success: number, errors: number) => quizT('import.partialSuccess', { success, errors }),
        importFailed: () => quizT('import.failed'),
        importValidationErrors: () => quizT('import.validationErrors'),
        importRowError: (row: number, error: string) => quizT('import.rowError', { row, error })
    };

    return {
        t,
        quizT,
        locale: locale as Ref<string>,
        isRtl,
        ...translations
    };
}
