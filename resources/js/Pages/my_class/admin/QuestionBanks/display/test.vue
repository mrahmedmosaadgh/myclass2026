
<template>
    <div>
        <!-- Settings Modal -->
        <!-- <TestWorksheetSettings
            :show="props.showSettings"
            @close="closeTestSettings"
            :questions="props.questions"
            @generate="generateTest"
        />
        showTestPreview:{{ showTestPreview }} <br> -->
        <!-- props.questions:{{ props.questions }} <br> -->
        <!-- Preview Modal -->
        <PrimaryButton
                            @click="showTestSettings=true"
                            class="flex items-center"
                            >
                            <!-- v-if="items.length > 0" -->
                            <LucideIcon name="file-text" class="w-4 h-4 mr-2" />
                            Generate Test/Worksheet
                        </PrimaryButton>

        <Modal_99
            :show="showTestSettings"
            @close="closeTestPreview"
            :maxWidth="'7xl'"
            :closeable="true"
        >
            <template #title>
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-medium">Test Preview</h2>
                    <div class="flex items-center space-x-2">
                        <button
                            type="button"
                            class="inline-flex items-center px-3 py-1 bg-purple-600 text-white text-sm rounded-md hover:bg-purple-700 mr-2"
                            @click="randomizeQuestions"
                        >
                            <LucideIcon name="shuffle" class="w-4 h-4 mr-1" />
                            Randomize
                        </button>
                        <button
                            type="button"
                            class="inline-flex items-center px-3 py-1 bg-blue-600 text-white text-sm rounded-md hover:bg-blue-700"
                            @click="handlePrint"
                        >
                            <LucideIcon name="printer" class="w-4 h-4 mr-1" />
                            Print
                        </button>
                        <button
                            type="button"
                            class="inline-flex items-center px-3 py-1 bg-green-600 text-white text-sm rounded-md hover:bg-green-700"
                            @click="handleDownloadPDF"
                        >
                            <LucideIcon name="file-down" class="w-4 h-4 mr-1" />
                            Download PDF
                        </button>
                    </div>
                </div>
            </template>

            <template #content>


                <!-- :show="props.showSettings" -->
                <!-- @close="closeTestSettings" -->
                <TestWorksheetSettings
            :questions="props.questions"
            @generate="generateTest"
        />





<div class="p-0">
    <div class="min-h-[60vh] p-6">
                 <!-- Debug logs -->
                <div class="hidden">
                    showTestPreview: {{ showTestPreview }}
                    hasQuestions: {{ props.questions && props.questions.length > 0 }}
                    questionsLength: {{ props.questions?.length }}
                    settings: {{ testSettings }}
                </div>

                <TestWorksheetDisplay
                    v-if="props.questions && props.questions.length > 0"
                    :questions="randomizedQuestions"
                    :settings="testSettings"
                    ref="worksheetDisplay"
                        @questionDeleted="handleQuestionDelete"
                />
                <div v-else-if="showSettings && (!props.questions || !props.questions.length)"
                    class="text-center text-gray-500 py-8">
                    No questions available to display
                </div>
            </div>
</div>
            </template>


            <template #footer>
                <div class="flex items-center justify-between w-full px-6 py-4 bg-gray-50">
                    <div class="flex items-center space-x-2">
                        <select
                            v-model="selectedFormat"
                            class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        >
                            <option value="A4">A4 Format</option>
                            <option value="Letter">Letter Format</option>
                            <option value="Legal">Legal Format</option>
                        </select>
                        <label class="inline-flex items-center">
                            <input
                                type="checkbox"
                                v-model="includeAnswers"
                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                            <span class="ml-2 text-sm text-gray-600">Include Answer Key</span>
                        </label>
                    </div>
                    <div class="flex space-x-3">
                        <button
                            type="button"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
                            @click="handleSave"
                        >
                            Save Test
                        </button>
                        <button
                            type="button"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300"
                            @click="closeTestPreview"
                        >
                            Close
                        </button>
                    </div>
                </div>
            </template>
        </Modal_99>


























    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { toast } from 'vue3-toastify';
import Modal_99 from '../Modal_99.vue';
import TestWorksheetSettings from '../TestWorksheetSettings.vue';
import TestWorksheetDisplay from '../TestWorksheetDisplay.vue';
import LucideIcon from '@/Components/Common/LucideIcon.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { renderKaTeX } from '@/utils/questionParser';

// First, define props
const props = defineProps({
    questions: {
        type: Array,
        required: true,
        validator: (value) => Array.isArray(value)
    }
});

// Then define other refs and state
const showTestPreview = ref(false);
const showTestSettings = ref(false);
const testSettings = ref({
    showOptions: true,
    showCorrectAnswers: false,
    showExplanations: false,
    showScore: true,
    layout: 'compact',
    showStepNumbers: true,
    showNotes: true,
    optionsPerRow: '2'
});

const worksheetDisplay = ref(null);
const selectedFormat = ref('A4');
const includeAnswers = ref(false);
const randomizedQuestions = ref([...props.questions]);

// Function to randomize questions
const randomizeQuestions = () => {
    randomizedQuestions.value = [...props.questions]
        .map(value => ({ value, sort: Math.random() }))
        .sort((a, b) => a.sort - b.sort)
        .map(({ value }) => value);

    // Update the display
    if (worksheetDisplay.value) {
        worksheetDisplay.value.$forceUpdate();
    }

    toast.success('Questions randomized successfully');
};

const emit = defineEmits(['generate', 'close', 'update:questions']);

// Functions
const generateTest = (settings) => {
    console.log('Received settings:', settings);
    testSettings.value = settings;
};

const closeTestPreview = () => {
    // showTestPreview.value = false;
    showTestSettings.value = false;
};

const closeTestSettings = () => {
    emit('close');
};

const generatePrintContent = (questions) => {
    return questions.map((question, index) => `
        <div class="question-item">
            <div class=" inline-block question-content">
                <span class="question-number  pr-2 ">  ${index + 1}.</span>
                ${renderKaTeX(question.body)}</div>
            ${question.options && testSettings.value.showOptions ?
                `<div class="options-container">
                    ${question.options.map((option, optIndex) => `
                        <div class="option-item">
                            <div style="display: flex; align-items: start; gap: 8px;">
                                <span class="option-label">${String.fromCharCode(65 + optIndex)}.</span>
                                <span class="option-text">${renderKaTeX(option.option)}</span>
                                ${testSettings.value.showCorrectAnswers && option.isCorrect ?
                                    '<span class="correct-marker" style="color: #15803d; margin-left: 8px;">(Correct)</span>' : ''}
                            </div>
                        </div>
                    `).join('')}
                </div>` : ''}
            ${testSettings.value.showExplanations && question.explanation ?
                `<div class="explanation" style="margin-top: 1em; padding: 1em; background-color: #eff6ff; border: 1px solid #bfdbfe; border-radius: 6px;">
                    <div style="font-weight: 500; color: #1e40af;">Explanation:</div>
                    <div style="color: #1e3a8a;">${renderKaTeX(question.explanation)}</div>
                </div>` : ''}
        </div>
    `).join('');
};

const handlePrint = () => {
    const printWindow = window.open('', '_blank');
    const printContent = `
        <!DOCTYPE html>
        <html>
        <head>
            <title>Test Worksheet</title>
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.0/dist/katex.min.css">
            <style>
                @page {
                    size: ${selectedFormat.value};
                    margin: 1cm;
                }
                body {
                    font-family: system-ui, -apple-system, sans-serif;
                    line-height: 1.5;
                    margin: 0;
                    padding: 1cm;
                }
                .question-item {
                    page-break-inside: avoid;
                    margin-bottom: 2em;
                }
                .options-container {
                    display: grid;
                    grid-template-columns: repeat(${testSettings.value.optionsPerRow}, minmax(0, 1fr));
                    gap: 12px;
                    width: 100%;
                    margin-bottom: 1em;
                }
                .option-item {
                    break-inside: avoid;
                    min-width: 0;
                    padding: 12px;
                }
            </style>
        </head>
        <body>
            ${generatePrintContent(randomizedQuestions.value)}
        </body>
        </html>
    `;

    printWindow.document.write(printContent);
    printWindow.document.close();

    setTimeout(() => {
        printWindow.print();
        printWindow.onafterprint = () => {
            printWindow.close();
        };
    }, 500);
};

const handleDownloadPDF = async () => {
    if (worksheetDisplay.value) {
        const toastId = 'pdf-generation';
        try {
            toast.info('Generating PDF...', {
                autoClose: false,
                toastId
            });

            // Create a new window for printing
            const printWindow = window.open('', '_blank');
            const content = document.getElementById('worksheet-content');

            // Setup print window content
            printWindow.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Test Worksheet</title>
                    <style>
                        @page {
                            size: ${selectedFormat.value};
                            margin: 1cm;
                        }
                        body {
                            font-family: system-ui, -apple-system, sans-serif;
                            line-height: 1.5;
                            margin: 0;
                            padding: 1cm;
                        }
                        .question-item {
                            page-break-inside: avoid;
                            margin-bottom: 2em;
                        }
                        img {
                            max-width: 100%;
                            height: auto;
                        }
                        @media print {
                            .question-item {
                                break-inside: avoid;
                            }
                        }
                        ${!includeAnswers.value ? '.correct-answer { display: none; }' : ''}
                    </style>
                </head>
                <body>
                    ${content.innerHTML}
                </body>
                </html>
            `);

            printWindow.document.close();

            // Wait for content to load
            printWindow.onload = () => {
                // Trigger PDF download
                printWindow.document.title = `test_worksheet_${new Date().toISOString().slice(0, 10)}`;
                printWindow.print();

                // Close the window after printing
                printWindow.onafterprint = () => {
                    printWindow.close();
                    toast.update(toastId, {
                        type: toast.TYPE.SUCCESS,
                        content: 'PDF generated successfully',
                        autoClose: 3000
                    });
                };
            };

        } catch (error) {
            console.error('PDF generation error:', error);
            toast.update(toastId, {
                type: toast.TYPE.ERROR,
                content: 'Failed to generate PDF',
                autoClose: 3000
            });
        }
    }
};

const handleSave = async () => {
    try {
        const response = await axios.post('/api/worksheets', {
            settings: testSettings.value,
            format: selectedFormat.value,
            includeAnswers: includeAnswers.value,
            questions: props.questions
        });
        toast.success('Test saved successfully');
        closeTestPreview();
    } catch (error) {
        toast.error('Failed to save test');
    }
};

const handleQuestionDelete = ({ question, index }) => {
    try {
        // Remove the question from randomizedQuestions
        randomizedQuestions.value = randomizedQuestions.value.filter((_, i) => i !== index);

        // Also update the original questions prop by emitting an event to parent
        const updatedQuestions = props.questions.filter(q => q.id !== question.id);
        emit('update:questions', updatedQuestions);

        toast.success('Question removed from worksheet');
    } catch (error) {
        console.error('Error removing question:', error);
        toast.error('Failed to remove question');
    }
};

// Add a watch to debug props
watch(() => props.questions, (newVal) => {
    console.log('Questions updated:', newVal);
}, { immediate: true });

watch(() => showTestPreview.value, (newVal) => {
    console.log('showTestPreview updated:', newVal);
}, { immediate: true });

watch(() => testSettings.value, (newVal) => {
    console.log('testSettings updated:', newVal);
}, { immediate: true });
</script>



























