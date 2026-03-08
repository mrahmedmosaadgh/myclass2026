// Export all components for easy import
export { default as LinePlot } from './components/core/LinePlot.vue';
export { default as NumberLine } from './components/core/NumberLine.vue';
export { default as XMark } from './components/core/XMark.vue';

export { default as QuestionCard } from './components/question/QuestionCard.vue';
export { default as AnswerInput } from './components/question/AnswerInput.vue';
export { default as SubmitButton } from './components/question/SubmitButton.vue';

export { default as SmartScoreDisplay } from './components/smart-score/SmartScoreDisplay.vue';
export { default as SmartScoreBadge } from './components/smart-score/SmartScoreBadge.vue';

// Export utility functions
export * from './utils/linePlotUtils';
export * from './utils/smartScoreUtils';