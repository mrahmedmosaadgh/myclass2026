<template>
    <div class="p-6 bg-white rounded-lg shadow">
        <h2 class="text-xl font-semibold mb-4">Question Text to JSON Parser</h2>

        <!-- Preview with KaTeX -->
        <div v-if="inputText" class="mb-6">
            <h3 class="text-lg font-medium text-gray-900 mb-2">Preview:</h3>
            <div class="bg-gray-50 p-4 rounded-lg">
                <div v-html="renderedPreview" class="katex-preview prose max-w-none"></div>
            </div>
        </div>

        <!-- Output Section -->
        <div v-if="parsedQuestion" class="space-y-4">
            <h3 class="text-lg font-medium text-gray-900">Parsed Result:</h3>

            <!-- Preview -->
            <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="font-medium text-gray-700 mb-2">Question Body:</h4>
                <div class="mb-4" v-html="renderKaTeX(parsedQuestion.body)"></div>

                <h4 class="font-medium text-gray-700 mb-2">Options:</h4>
                <div class="space-y-2">
                    <div v-for="(option, index) in parsedQuestion.options" :key="index" class="flex items-center gap-2">
                        <span class="font-medium">{{ ['A', 'B', 'C', 'D'][index] }})</span>
                        <div v-html="renderKaTeX(option.option)"></div>
                        <span v-if="option.isCorrect" class="text-green-600 ml-2">(Correct)</span>
                    </div>
                </div>
            </div>

            <!-- JSON Output -->
            <div>
                <h4 class="font-medium text-gray-700 mb-2">JSON Output:</h4>
                <pre class="bg-gray-800 text-white p-4 rounded-lg overflow-x-auto">{{ JSON.stringify(parsedQuestion, null, 2) }}</pre>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import { toast } from 'vue3-toastify';
import katex from 'katex';
import 'katex/dist/katex.min.css';
import DOMPurify from 'dompurify';

const props = defineProps({
    get_text: {
        type: String,
        required: true
    }
});

const emit = defineEmits(['set_question']);

const inputText = ref('');
const parsedQuestion = ref(null);
const renderedPreview = ref('');

// Watch for changes in props.get_text
watch(() => props.get_text, (newValue) => {
    if (newValue) {
        inputText.value = newValue;
        parseQuestion();
    }
}, { immediate: true });

// Enhanced renderKaTeX function
const renderKaTeX = (text) => {
    try {
        // First, handle markdown-style bold text
        let processed = text.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');

        // Then handle LaTeX expressions
        processed = processed.replace(/\\\((.*?)\\\)/g, (match, latex) => {
            try {
                return katex.renderToString(latex.trim(), {
                    throwOnError: false,
                    displayMode: false,
                    strict: false
                });
            } catch (e) {
                console.warn('KaTeX rendering error:', e);
                return match;
            }
        });

        // Add line breaks
        processed = processed.replace(/\n/g, '<br>');

        return processed;
    } catch (error) {
        console.error('Error in renderKaTeX:', error);
        return text;
    }
};

// Watch for changes in input text and update preview
watch(inputText, (newValue) => {
    if (newValue) {
        const rendered = renderKaTeX(newValue);
        renderedPreview.value = DOMPurify.sanitize(rendered);
    } else {
        renderedPreview.value = '';
    }
});

const parseQuestion = () => {
    try {
        if (!inputText.value.trim()) {
            toast.error('Please enter question text');
            return;
        }

        // Extract the question body (everything before the first "A)")
        const bodyMatch = inputText.value.match(/(.*?)(?=\s*\*\*A\)\*\*)/s);
        const body = bodyMatch ? bodyMatch[1].trim() : '';

        // Extract options using regex
        const optionRegex = /\*\*([A-D])\)\*\*\s*(.*?)(?=\s*\*\*[A-D]\)\*\*|$)/gs;
        const options = [];
        let match;

        while ((match = optionRegex.exec(inputText.value)) !== null) {
            options.push({
                option: match[2].trim(),
                feedback: '',
                isCorrect: false
            });
        }

        // Validate parsed data
        if (!body) {
            throw new Error('Could not extract question body');
        }
        if (options.length === 0) {
            throw new Error('Could not extract any options');
        }

        // Create the question data object
        const questionData = {
            body,
            options
        };

        parsedQuestion.value = questionData;
        emit('set_question', questionData);
        toast.success('Question parsed successfully');
    } catch (error) {
        console.error('Error parsing question:', error);
        toast.error(error.message || 'Error parsing question. Please check the format.');
    }
};
</script>

<style>
/* Enhanced styles for KaTeX preview */
.katex-preview {
    font-size: 1.1em;
    line-height: 1.5;
}

.katex-preview :deep(.katex) {
    font-size: 1.1em;
}

.katex-preview :deep(.katex-display) {
    margin: 1em 0;
    overflow-x: auto;
    overflow-y: hidden;
}

.katex-preview :deep(strong) {
    font-weight: 600;
}

.katex-preview :deep(br) {
    margin-bottom: 0.5em;
    display: block;
    content: "";
}
</style>

