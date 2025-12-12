<template>
    <div class="space-y-4">
        <div v-for="(step, index) in steps" :key="step.id" class="flex gap-4 items-start">
            <div class="flex-grow space-y-2">
                <div class="flex gap-2 items-center">
                    <span class="font-medium">Step {{ index + 1 }}</span>
                    <input
                        v-model="step.step"
                        type="text"
                        class="w-full rounded-md border-gray-300 shadow-sm"
                        placeholder="Enter step description"
                        @input="updateSteps"
                    />
                </div>
                <textarea
                    v-model="step.note"
                    class="w-full rounded-md border-gray-300 shadow-sm"
                    rows="2"
                    placeholder="Additional notes for this step"
                    @input="updateSteps"
                ></textarea>
            </div>
            <button
                type="button"
                @click="removeStep(index)"
                class="text-red-600 hover:text-red-800"
            >
                <LucideIcon name="trash-2" class="w-5 h-5" />
            </button>
        </div>

        <button
            type="button"
            @click="addStep"
            class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
        >
            <LucideIcon name="plus" class="w-4 h-4 mr-2" />
            Add Step
        </button>
    </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import LucideIcon from '@/Components/Common/LucideIcon.vue';

const props = defineProps({
    modelValue: {
        type: Array,
        default: () => []
    }
});

const emit = defineEmits(['update:modelValue']);

const steps = ref([]);

// Initialize steps only once when the component is mounted
const initializeSteps = () => {
    if (props.modelValue.length > 0) {
        steps.value = props.modelValue.map(step => ({
            id: step.id || Date.now() + Math.random(),
            step: step.step || '',
            note: step.note || ''
        }));
    } else {
        steps.value = [{
            id: Date.now(),
            step: '',
            note: ''
        }];
    }
};

// Watch for external changes
watch(() => props.modelValue, (newValue) => {
    if (JSON.stringify(newValue) !== JSON.stringify(steps.value)) {
        initializeSteps();
    }
}, { deep: true });

// Initialize on component mount
initializeSteps();

const updateSteps = () => {
    emit('update:modelValue', steps.value);
};

const addStep = () => {
    steps.value.push({
        id: Date.now() + Math.random(),
        step: '',
        note: ''
    });
    updateSteps();
};

const removeStep = (index) => {
    steps.value.splice(index, 1);
    if (steps.value.length === 0) {
        steps.value.push({
            id: Date.now(),
            step: '',
            note: ''
        });
    }
    updateSteps();
};
</script>
