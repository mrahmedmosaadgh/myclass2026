<template>
    <div class="relative">
        <div
            class="min-h-[38px] w-full rounded-md border border-gray-300 shadow-sm focus-within:border-indigo-500 focus-within:ring-1 focus-within:ring-indigo-500"
            :class="{ 'border-red-500': error }"
        >
            <div class="flex flex-wrap gap-2 p-2">
                <!-- Existing Tags -->
                <span
                    v-for="(tag, index) in modelValue"
                    :key="index"
                    class="inline-flex items-center px-2.5 py-0.5 rounded-md text-sm font-medium bg-indigo-100 text-indigo-800"
                >
                    {{ tag }}
                    <button
                        type="button"
                        class="ml-1 inline-flex text-indigo-400 hover:text-indigo-600"
                        @click="removeTag(index)"
                    >
                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                            <path
                                fill-rule="evenodd"
                                d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                clip-rule="evenodd"
                            />
                        </svg>
                    </button>
                </span>

                <!-- Input Field -->
                <input
                    type="text"
                    class="flex-1 border-0 p-0 focus:ring-0 text-sm"
                    :placeholder="placeholder"
                    v-model="newTag"
                    @keydown.enter.prevent="addTag"
                    @keydown.tab.prevent="addTag"
                    @keydown.comma.prevent="addTag"
                    @blur="addTag"
                />
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';

const props = defineProps({
    modelValue: {
        type: Array,
        default: () => []
    },
    placeholder: {
        type: String,
        default: 'Add tags...'
    },
    error: {
        type: [String, Boolean],
        default: false
    }
});

const emit = defineEmits(['update:modelValue']);

const newTag = ref('');

const addTag = () => {
    const tag = newTag.value.trim();
    if (tag && !props.modelValue.includes(tag)) {
        emit('update:modelValue', [...props.modelValue, tag]);
    }
    newTag.value = '';
};

const removeTag = (index) => {
    const newTags = [...props.modelValue];
    newTags.splice(index, 1);
    emit('update:modelValue', newTags);
};
</script>

<style scoped>
input:focus {
    outline: none;
}
</style>