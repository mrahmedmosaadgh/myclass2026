<template>
    <div class="space-y-4">
        <!-- Paste Area -->
        <div class="border-2 border-dashed border-gray-300 rounded-lg p-4">
            <div class="flex items-center gap-2 mb-4">
                <button
                    @click="handleClipboardPaste"
                    class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600 transition-colors flex items-center gap-2"
                >
                    <LucideIcon name="clipboard" class="w-4 h-4" />
                    Paste from Clipboard
                </button>
                <button
                    @click="clearContent"
                    class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600 transition-colors flex items-center gap-2"
                >
                    <LucideIcon name="trash-2" class="w-4 h-4" />
                    Clear
                </button>
            </div>

            <textarea
                v-model="pastedContent"
                class="w-full h-48 p-4 border rounded-lg"
                placeholder="Paste your questions here..."
                @paste="handlePaste"
            ></textarea>
        </div>

        <!-- Preview Section -->
        <div v-if="parsedQuestions.length" class="space-y-4">
            <h3 class="text-lg font-semibold">Preview ({{ parsedQuestions.length }} questions)</h3>

            <div class="space-y-2">
                <div v-for="(question, index) in parsedQuestions" :key="index"
                    class="border rounded-lg p-4 bg-gray-50">
                    <div class="flex justify-between items-start">
                        <span class="font-medium">Question {{ index + 1 }}</span>
                        <button @click="removeQuestion(index)"
                            class="text-red-500 hover:text-red-700">
                            <LucideIcon name="x" class="w-4 h-4" />
                        </button>
                    </div>
                    <div class="mt-2">
                        <div v-html="question.body"></div>
                        <div class="ml-4 mt-2">
                            <div v-for="(option, optIndex) in question.options"
                                :key="optIndex" class="mt-1">
                                {{ String.fromCharCode(65 + optIndex) }}) {{ option.option }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Import Button -->
            <div class="flex justify-end mt-4">
                <PrimaryButton
                    @click="importQuestions"
                    :disabled="isImporting"
                    class="flex items-center gap-2"
                >
                    <LucideIcon name="upload" class="w-4 h-4" />
                    {{ isImporting ? 'Importing...' : 'Import Questions' }}
                </PrimaryButton>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import LucideIcon from '@/Components/Common/LucideIcon.vue';
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';

const emit = defineEmits(['close', 'questions-imported']);

const pastedContent = ref('');
const parsedQuestions = ref([]);
const isImporting = ref(false);

const handlePaste = (event) => {
    event.preventDefault();
    const text = event.clipboardData.getData('text');
    pastedContent.value = text;
    parseQuestions(text);
};

const handleClipboardPaste = async () => {
    try {
        const text = await navigator.clipboard.readText();
        pastedContent.value = text;
        parseQuestions(text);
    } catch (error) {
        toast.error('Failed to read from clipboard');
    }
};

const parseQuestions = (text) => {
    try {
        // Split text into individual questions
        const questionBlocks = text.split(/(?=\d+\))/);

        const questions = questionBlocks
            .filter(block => block.trim())
            .map(block => {
                // Extract question body (everything before A))
                const bodyMatch = block.match(/(.*?)(?=\s*A\))/s);
                const body = bodyMatch ? bodyMatch[1].trim() : '';

                // Extract options
                const optionRegex = /([A-D]\))\s*(.*?)(?=(?:\s*[A-D]\)|$))/gs;
                const options = [];
                let match;

                while ((match = optionRegex.exec(block)) !== null) {
                    options.push({
                        option: match[2].trim(),
                        feedback: '',
                        isCorrect: false
                    });
                }

                return {
                    body,
                    options,
                    type: 'mcq',
                    score: 1,
                    difficulty: 'medium'
                };
            });

        parsedQuestions.value = questions;
        toast.success(`${questions.length} questions parsed successfully`);
    } catch (error) {
        toast.error('Error parsing questions');
        console.error(error);
    }
};

const clearContent = () => {
    pastedContent.value = '';
    parsedQuestions.value = [];
};

const removeQuestion = (index) => {
    parsedQuestions.value.splice(index, 1);
};

const importQuestions = async () => {
    try {
        isImporting.value = true;
        emit('questions-imported', parsedQuestions.value);
        toast.success('Questions imported successfully');
        clearContent();
    } catch (error) {
        toast.error('Failed to import questions');
    } finally {
        isImporting.value = false;
    }
};
</script>

