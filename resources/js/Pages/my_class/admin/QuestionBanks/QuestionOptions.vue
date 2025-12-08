<template>
    <div class="space-y-4">
        <!-- Counters Section -->
        <div class="flex space-x-4 mb-6">
            <div class="bg-gray-100 rounded-lg p-3 flex-1 text-center shadow-sm">
                <div class="text-sm text-gray-600">Total Options</div>
                <div class="text-2xl font-bold text-gray-800">{{ localOptions.length }}</div>
            </div>
            <div class="bg-green-100 rounded-lg p-3 flex-1 text-center shadow-sm">
                <div class="text-sm text-gray-600">Correct Answers</div>
                <div class="text-2xl font-bold text-green-600">{{ correctAnswersCount }}</div>
            </div>
        </div>

        <!-- Options List -->
        <div v-for="(option, index) in localOptions"
             :key="index"
             class="group relative transition-all duration-200 hover:shadow-lg bg-white rounded-lg border"
             :class="option.isCorrect ? 'border-green-200' : 'border-gray-200'"
        >
            <div class="p-4">
                <!-- Main Option Row -->
                <div class="flex items-start space-x-4">
                    <!-- Option Number -->
                    <div class="flex-shrink-0 w-8 h-8 rounded-full bg-indigo-600 text-white flex items-center justify-center font-semibold shadow-sm">
                        {{ index + 1 }}
                    </div>

                    <!-- Option Content -->
                    <div class="flex-grow space-y-3">
                        <div class="flex items-center space-x-4">
                            <div class="flex-grow">
                                <TextInput
                                    v-model="option.option"
                                    class="w-full"
                                    :placeholder="`Enter option ${index + 1} text`"
                                />
                            </div>

                            <!-- Controls -->
                            <div class="flex items-center space-x-2">
                                <!-- Feedback Toggle Button -->
                                <button
                                    @click="toggleFeedback(index)"
                                    type="button"
                                    class="p-2 rounded-full hover:bg-gray-100 transition-colors"
                                    :class="option.showFeedback ? 'text-indigo-600 bg-indigo-50' : 'text-gray-400'"
                                    :title="option.showFeedback ? 'Hide feedback' : 'Add feedback'"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                                    </svg>
                                </button>

                                <!-- Is Correct Toggle -->
                                <button
                                    @click="toggleCorrect(index)"
                                    type="button"
                                    class="p-2 rounded-full transition-colors"
                                    :class="option.isCorrect ? 'bg-green-100 text-green-600' : 'hover:bg-gray-100 text-gray-400'"
                                    :title="option.isCorrect ? 'Mark as incorrect' : 'Mark as correct'"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M5 13l4 4L19 7" />
                                    </svg>
                                </button>

                                <!-- Delete Button -->
                                <button
                                    @click="removeOption(index)"
                                    class="p-2 rounded-full hover:bg-red-100 text-red-400 hover:text-red-600 transition-colors"
                                    type="button"
                                    title="Delete option"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Feedback Section -->
                        <div v-if="option.showFeedback"
                             class="mt-3 animate-fade-in"
                        >
                            <textarea
                                v-model="option.feedback"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 text-sm"
                                rows="2"
                                :placeholder="`Enter feedback for option ${index + 1} (optional)`"
                            ></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Option Button -->
        <button
            @click="addOption"
            type="button"
            class="w-full flex items-center justify-center px-4 py-3 border-2 border-dashed border-gray-300 rounded-lg text-gray-600 hover:text-indigo-600 hover:border-indigo-300 transition-colors duration-200 hover:bg-indigo-50/50"
        >
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Add Option
        </button>
    </div>
</template>

<script setup>
import { ref, watch, computed, onMounted } from 'vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    modelValue: {
        type: [Array, String],
        default: () => []
    }
});

const emit = defineEmits(['update:modelValue']);

function parseOptions(value) {
    if (typeof value === 'string') {
        try {
            return JSON.parse(value);
        } catch {
            return [];
        }
    }
    return Array.isArray(value) ? value : [];
}

// Initialize localOptions without triggering immediate updates
const localOptions = ref([]);

// Initialize from props
onMounted(() => {
    localOptions.value = parseOptions(props.modelValue).map(option => ({
        ...option,
        showFeedback: !!option.feedback
    }));
});

const correctAnswersCount = computed(() => {
    return localOptions.value.filter(option => option.isCorrect).length;
});

// Watch for external changes
watch(() => props.modelValue, (newVal) => {
    const parsed = parseOptions(newVal);
    if (JSON.stringify(parsed) !== JSON.stringify(localOptions.value)) {
        localOptions.value = parsed.map(option => ({
            ...option,
            showFeedback: !!option.feedback
        }));
    }
}, { deep: true });

// Watch for internal changes
watch(localOptions, (newVal) => {
    const valueToEmit = newVal.map(({ showFeedback, ...option }) => option);
    const currentValue = typeof props.modelValue === 'string'
        ? JSON.parse(props.modelValue)
        : props.modelValue;

    if (JSON.stringify(valueToEmit) !== JSON.stringify(currentValue)) {
        if (typeof props.modelValue === 'string') {
            emit('update:modelValue', JSON.stringify(valueToEmit));
        } else {
            emit('update:modelValue', valueToEmit);
        }
    }
}, { deep: true });

function addOption() {
    localOptions.value.push({
        option: '',
        isCorrect: false,
        feedback: '',
        showFeedback: false
    });
}

function removeOption(index) {
    localOptions.value.splice(index, 1);
}

function toggleFeedback(index) {
    localOptions.value[index].showFeedback = !localOptions.value[index].showFeedback;
}

function toggleCorrect(index) {
    localOptions.value[index].isCorrect = !localOptions.value[index].isCorrect;
}
</script>

<style scoped>
.animate-fade-in {
    animation: fadeIn 0.2s ease-in-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>



