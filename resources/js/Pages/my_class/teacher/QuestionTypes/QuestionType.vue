<template>
    <div class="space-y-4">
        <!-- Question Text -->
        <div>
            <label class="block text-sm font-medium text-gray-700">Question</label>
            <textarea
                v-model="questionData.question"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                rows="3"
                @input="emitUpdate"
            ></textarea>
        </div>

        <!-- Multiple Choice Options -->
        <div v-if="type === 'mcq'" class="space-y-2">
            <div v-for="(option, index) in questionData.options" :key="index" class="flex items-center gap-2">
                <input
                    type="radio"
                    :name="'correct-answer'"
                    :value="index"
                    v-model="questionData.correctAnswer"
                    @change="emitUpdate"
                >
                <input
                    type="text"
                    v-model="questionData.options[index]"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    @input="emitUpdate"
                >
                <button @click="removeOption(index)" class="text-red-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <button
                @click="addOption"
                class="mt-2 inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200"
            >
                Add Option
            </button>
        </div>

        <!-- True/False Options -->
        <div v-if="type === 'true_false'" class="space-y-2">
            <div class="flex items-center gap-4">
                <label class="inline-flex items-center">
                    <input
                        type="radio"
                        v-model="questionData.correctAnswer"
                        value="true"
                        @change="emitUpdate"
                    >
                    <span class="ml-2">True</span>
                </label>
                <label class="inline-flex items-center">
                    <input
                        type="radio"
                        v-model="questionData.correctAnswer"
                        value="false"
                        @change="emitUpdate"
                    >
                    <span class="ml-2">False</span>
                </label>
            </div>
        </div>

        <!-- Fill in the Blank -->
        <div v-if="type === 'fill_blank'" class="space-y-2">
            <input
                type="text"
                v-model="questionData.correctAnswer"
                placeholder="Correct Answer"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                @input="emitUpdate"
            >
        </div>

        <!-- Explanation -->
        <div>
            <label class="block text-sm font-medium text-gray-700">Explanation</label>
            <textarea
                v-model="questionData.explanation"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                rows="2"
                @input="emitUpdate"
            ></textarea>
        </div>
    </div>
</template>

<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
    type: {
        type: String,
        required: true
    },
    value: {
        type: Object,
        required: true
    }
});

const emit = defineEmits(['update:value']);

const questionData = ref({
    type: props.type,
    question: '',
    options: props.type === 'mcq' ? [''] : [],
    correctAnswer: props.type === 'mcq' ? null : '',
    explanation: ''
});

// Initialize from props
watch(() => props.value, (newValue) => {
    if (newValue) {
        questionData.value = { ...newValue };
    }
}, { immediate: true });

const addOption = () => {
    questionData.value.options.push('');
    emitUpdate();
};

const removeOption = (index) => {
    questionData.value.options.splice(index, 1);
    if (questionData.value.correctAnswer === index) {
        questionData.value.correctAnswer = null;
    }
    emitUpdate();
};

const emitUpdate = () => {
    emit('update:value', { ...questionData.value });
};
</script>