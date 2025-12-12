<template>
    <div class="p-6" :class="settings.layout">
        <div v-if="!questions || questions.length === 0" class="text-center text-gray-500 py-8">
            No questions available
        </div>

        <div v-else class="space-y-8">
            <div v-for="(question, index) in questions" :key="index"
                 class="question-item bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <!-- Add delete button -->
                <div class="flex justify-between items-start mb-4">
                    <div class="font-semibold text-lg text-gray-800 flex items-center">
                        <span class="bg-indigo-600 text-white rounded-full w-8 h-8 flex items-center justify-center mr-3">
                            {{ index + 1 }}
                        </span>
                        <span v-if="settings.show_title">{{ question.title || 'Question' }}</span>
                    </div>
                    <button
                        @click="deleteQuestion(question, index)"
                        class="text-red-600 hover:text-red-800 transition-colors"
                        title="Delete question">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>

                <!-- Question Content -->
                <div class="mb-6 text-gray-700 question-content" v-html="renderQuestionBody(question.body)"></div>

                <!-- Options -->
                <div v-if="settings.showOptions && question.options"
                     class="options-container"
                     :style="{
                         display: 'grid',
                         gridTemplateColumns: `repeat(${Number(settings.optionsPerRow) || 1}, minmax(0, 1fr))`,
                         gap: '0.75rem',
                         width: '100%'
                     }">
                    <div v-for="(option, optIndex) in formatOptions(question.options)"
                         :key="optIndex"
                         class="option-item"
                         :style="{
                             minWidth: '0',
                             breakInside: 'avoid'
                         }">
                        <!-- Option content -->
                        <div class="p-3 rounded-lg border border-gray-200 hover:bg-gray-50 transition-colors">
                            <div class="flex items-start gap-3">
                                <span class="option-marker font-medium text-gray-600 flex-shrink-0">
                                    {{ String.fromCharCode(65 + optIndex) }}.
                                </span>
                                <div class="flex-grow" v-html="option.option"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Correct Answer Section -->
                <div v-if="settings.showCorrectAnswers && question.options"
                     class="mt-4 bg-green-50 p-4 rounded-lg border border-green-200">
                    <div class="font-medium text-green-800 mb-2">The Correct Answer:</div>
                    <div class="text-green-700">
                        {{ getCorrectAnswerText(question.options) }}
                    </div>
                </div>

                <!-- Score -->
                <div v-if="settings.showScore && question.score"
                     class="mt-4 text-sm text-gray-600 flex items-center">
                    <span class="font-medium">Score:</span>
                    <span class="ml-2 bg-gray-100 px-2 py-1 rounded">{{ question.score }}</span>
                </div>

                <!-- Explanation -->
                <div v-if="settings.showExplanations && question.explanation"
                     class="mt-4 bg-blue-50 p-4 rounded-lg border border-blue-100">
                    <div class="font-medium text-blue-800 mb-2">Explanation:</div>
                    <div class="text-blue-700" v-html="parseExplanation(question.explanation)"></div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { onMounted, watch } from 'vue';
// import html2pdf from 'html2pdf.js';
import { renderKaTeX } from '@/utils/questionParser';

const props = defineProps({
    questions: {
        type: Array,
        required: true,
        default: () => []
    },
    settings: {
        type: Object,
        required: true,
        default: () => ({
            showOptions: true,
            show_title: true,
            showCorrectAnswers: false,
            showExplanations: false,
            showScore: true,
            layout: 'compact',
            showStepNumbers: true,
            showNotes: true,
            optionsPerRow: '1'
        })
    }
});

const emit = defineEmits(['questionDeleted']);

// Add this for debugging
watch(() => props.settings, (newSettings) => {
    console.log('Settings changed:', newSettings);
    console.log('Options per row:', newSettings.optionsPerRow);
}, { deep: true });

onMounted(() => {
    console.log('Initial settings:', props.settings);
    console.log('Options per row:', props.settings.optionsPerRow);
});

// Add print method
const generatePrintHtml = () => {
    return props.questions.map((question, index) => `
        <div class="question-item" style="margin-bottom: 2em; page-break-inside: avoid;">
            <div style="font-weight: bold; margin-bottom: 1em;">
                <span>${index + 1}. </span>
                <span>${question.title || 'Question'}</span>
            </div>

            <div style="margin-bottom: 1.5em;">${renderQuestionBody(question.body)}</div>

            ${question.options && props.settings.showOptions ? `
                <div style="
                    display: grid;
                    grid-template-columns: repeat(${Number(props.settings.optionsPerRow) || 1}, minmax(0, 1fr));
                    gap: 12px;
                    width: 100%;
                    margin-bottom: 1.5em;
                ">
                    ${formatOptions(question.options).map((option, optIndex) => `
                        <div style="break-inside: avoid; min-width: 0;">
                            <div style="
                                border: 1px solid #ddd;
                                border-radius: 6px;
                                padding: 12px;
                                display: flex;
                                align-items: start;
                                gap: 8px;
                            ">
                                <span style="font-weight: bold; flex-shrink: 0;">
                                    ${String.fromCharCode(65 + optIndex)}.
                                </span>
                                <span style="flex-grow: 1; min-width: 0; word-wrap: break-word;">${option.option}</span>
                            </div>
                        </div>
                    `).join('')}
                </div>
            ` : ''}

            ${props.settings.showCorrectAnswers && question.options ? `
                <div style="
                    margin-top: 1em;
                    padding: 1em;
                    background-color: #f0fdf4;
                    border: 1px solid #bbf7d0;
                    border-radius: 6px;
                ">
                    <div style="font-weight: 500; color: #15803d;">Correct Answer:</div>
                    <div style="color: #166534;">${getCorrectAnswerText(question.options)}</div>
                </div>
            ` : ''}

            ${props.settings.showExplanations && question.explanation ? `
                <div style="
                    margin-top: 1em;
                    padding: 1em;
                    background-color: #eff6ff;
                    border: 1px solid #bfdbfe;
                    border-radius: 6px;
                ">
                    <div style="font-weight: 500; color: #1e40af;">Explanation:</div>
                    <div style="color: #1e3a8a;">${parseExplanation(question.explanation)}</div>
                </div>
            ` : ''}
        </div>
    `).join('');
};

const print = () => {
    const printWindow = window.open('', '_blank');
    const printContent = `
        <!DOCTYPE html>
        <html>
        <head>
            <title>Test Worksheet</title>
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.0/dist/katex.min.css">
            <style>
                @page { margin: 1cm; }
                body {
                    font-family: system-ui, -apple-system, sans-serif;
                    line-height: 1.5;
                    margin: 0;
                    padding: 1cm;
                }
                * { box-sizing: border-box; }
            </style>
        </head>
        <body>
            ${generatePrintHtml()}
        </body>
        </html>
    `;

    printWindow.document.write(printContent);
    printWindow.document.close();

    // Wait for styles and resources to load
    setTimeout(() => {
        printWindow.print();
        printWindow.onafterprint = () => {
            printWindow.close();
        };
    }, 500);
};

// Helper function to generate options HTML
const generateOptionsHtml = (question) => {
    if (!props.settings.showOptions || !question.options) return '';

    const options = formatOptions(question.options);
    return `
        <div class="options-container">
            ${options.map((option, index) => `
                <div class="option-item">
                    <div class="option-content">
                        <span class="option-label">${String.fromCharCode(65 + index)}.</span>
                        <span class="option-text">${option.option}</span>
                        ${props.settings.showCorrectAnswers && option.isCorrect ?
                            '<span class="correct-marker">(Correct)</span>' : ''}
                    </div>
                </div>
            `).join('')}
        </div>
    `;
};

// Helper function to generate questions HTML
const generateQuestionsHtml = () => {
    return props.questions.map((question, index) => `
        <div class="question-item">
            <div class="question-number">
                <span class="number">${index + 1}.</span>
                <span class="title">${question.title || 'Question'}</span>
            </div>
            <div class="question-content">${renderQuestionBody(question.body)}</div>
            ${generateOptionsHtml(question)}
            ${props.settings.showExplanations && question.explanation ?
                `<div class="explanation">${question.explanation}</div>` : ''}
        </div>
    `).join('');
};

const formatOptions = (options) => {
    if (!Array.isArray(options)) return [];
    return options.map(option => ({
        ...option,
        option: renderKaTeX(option.option),
        feedback: option.feedback ? renderKaTeX(option.feedback) : ''
    }));
};

const parseExplanation = (explanation) => {
    if (!explanation) return '';

    try {
        const parsed = typeof explanation === 'string'
            ? JSON.parse(explanation)
            : explanation;

        if (Array.isArray(parsed)) {
            return parsed.map((step, index) => {
                let content = '';
                if (step.step) {
                    content += `<div class="explanation-step">
                        ${props.settings.showStepNumbers ? `<span class="step-number">Step ${index + 1}:</span> ` : ''}
                        ${step.step}
                    </div>`;
                }
                if (props.settings.showNotes && step.note) {
                    content += `<div class="explanation-note text-gray-600 text-sm mt-1 ml-4">${step.note}</div>`;
                }
                return content;
            }).join('<div class="my-2"></div>');
        }

        return explanation.toString();
    } catch (e) {
        return explanation?.toString() || '';
    }
};

// Add helper function to get correct answer text
const getCorrectAnswerText = (options) => {
    const parsedOptions = formatOptions(options);
    const correctOptions = parsedOptions
        .map((option, index) => ({
            letter: String.fromCharCode(65 + index),
            text: option.option,
            isCorrect: option.isCorrect
        }))
        .filter(opt => opt.isCorrect);

    if (correctOptions.length === 0) return 'No correct answer specified';

    const correctAnswerLetters = correctOptions.map(opt => opt.letter).join(', ');
    const correctAnswerTexts = correctOptions.map(opt => opt.text).join('; ');

    return `${correctAnswerLetters} - ${correctAnswerTexts}`;
};

// Add downloadPDF method
const downloadPDF = async ({ format = 'A4', includeAnswers = false }) => {
    try {
        // Get the content element
        const element = document.getElementById('worksheet-content');
        if (!element) throw new Error('Content element not found');

        // Configure PDF options
        const opt = {
            margin: [0.5, 0.5, 0.5, 0.5], // [top, left, bottom, right]
            filename: `test_worksheet_${new Date().toISOString().slice(0, 10)}.pdf`,
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: {
                scale: 2,
                useCORS: true,
                letterRendering: true
            },
            jsPDF: {
                unit: 'in',
                format: format,
                orientation: 'portrait'
            },
            pagebreak: { mode: ['avoid-all', 'css', 'legacy'] }
        };

        // Create a clone of the element to modify for PDF
        const clonedElement = element.cloneNode(true);

        // Apply PDF-specific styles
        const styleElement = document.createElement('style');
        styleElement.textContent = `
            .question-item {
                page-break-inside: avoid;
                margin-bottom: 20px;
            }
            img {
                max-width: 100%;
                height: auto;
            }
            @media print {
                .question-item {
                    page-break-inside: avoid;
                }
            }
        `;
        clonedElement.prepend(styleElement);

        // Hide answer key if not included
        if (!includeAnswers) {
            const answers = clonedElement.querySelectorAll('.correct-answer');
            answers.forEach(answer => answer.style.display = 'none');
        }

        // Generate PDF
        // await html2pdf().set(opt).from(clonedElement).save();
console.log('not working html2pdf___.....');

        return true;
    } catch (error) {
        console.error('PDF generation failed:', error);
        throw error;
    }
};

// Expose print and downloadPDF methods
defineExpose({
    downloadPDF,
    print
});

// Add this computed or method
const renderQuestionBody = (body) => {
    if (!body) return '';
    return renderKaTeX(body);
};

const deleteQuestion = (question, index) => {
    if (!confirm('Are you sure you want to delete this question?')) return;

    try {
        // Emit event to parent component to handle the actual deletion
        emit('questionDeleted', { question, index });
    } catch (error) {
        console.error('Error deleting question:', error);
        alert('Failed to delete question');
    }
};
</script>

<style scoped>
.question-item {
    @apply transition-all duration-200;
}

.option-container {
    @apply transition-all duration-200;
}

.option-container:hover {
    @apply transform translate-x-1;
}

.option-marker {
    @apply w-6 text-center;
}

/* Question content styling */
:deep(.question-content) {
    @apply prose prose-sm max-w-none;
}

:deep(.question-content img) {
    @apply max-w-full h-auto my-2 rounded-lg;
}

:deep(.question-content table) {
    @apply border-collapse border border-gray-300 my-2;
}

:deep(.question-content td),
:deep(.question-content th) {
    @apply border border-gray-300 p-2;
}

@media print {
    .question-item {
        page-break-inside: avoid;
    }
}

.correct-answer {
    @apply mt-4 bg-green-50 p-4 rounded-lg border border-green-200;
}

.correct-answer-header {
    @apply font-medium text-green-800 mb-2;
}

.correct-answer-text {
    @apply text-green-700;
}

:deep(.explanation-step) {
    @apply text-blue-700 font-medium leading-relaxed;
}

:deep(.step-number) {
    @apply font-bold text-blue-800;
}

:deep(.explanation-note) {
    @apply text-blue-600 opacity-80 leading-relaxed;
}

/* Layout variations */
.spacious {
    @apply space-y-12;
}

.spacious .question-item {
    @apply p-8;
}

.compact {
    @apply space-y-6;
}

.compact .question-item {
    @apply p-4;
}

/* Add these styles to ensure proper KaTeX rendering */
.question-content :deep(.katex) {
    font-size: 1.1em;
}

.question-content :deep(.katex-display) {
    margin: 1em 0;
    overflow-x: auto;
    overflow-y: hidden;
}

.question-content :deep(strong) {
    font-weight: 600;
}

/* Add these styles to handle KaTeX in options */
.option-container :deep(.katex) {
    font-size: 1.1em;
}

.option-container :deep(.katex-display) {
    margin: 0.5em 0;
    overflow-x: auto;
    overflow-y: hidden;
}

.option-container :deep(strong) {
    font-weight: 600;
}

/* Add responsive grid styles */
.options-grid {
    display: grid;
    gap: 0.75rem;
    width: 100%;
}

.options-full-width {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.options-one-col {
    grid-template-columns: 1fr;
}

.options-two-col {
    grid-template-columns: repeat(2, 1fr);
}

.options-three-col {
    grid-template-columns: repeat(3, 1fr);
}

.options-four-col {
    grid-template-columns: repeat(4, 1fr);
}

.options-five-col {
    grid-template-columns: repeat(5, 1fr);
}

/* Responsive breakpoints */
@media (max-width: 768px) {
    .options-grid {
        grid-template-columns: 1fr !important;
    }
}

.option-container {
    min-width: 0;
    break-inside: avoid;
}

/* Ensure proper spacing in grid layout */
.gap-3 {
    gap: 0.75rem;
}

.options-container {
    display: grid !important;
}

.option-item {
    break-inside: avoid;
}

@media (max-width: 768px) {
    .options-container {
        grid-template-columns: 1fr !important;
    }
}
</style>
























