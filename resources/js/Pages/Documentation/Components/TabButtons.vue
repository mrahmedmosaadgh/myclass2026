<template>







    <div class="flex items-center justify-center space-x-1 bg-gray-100/40 backdrop-blur-sm p-1.5 rounded-xl">
        <button
            v-for="tab in tabs"
            :key="tab.value"
            @click="selectTab(tab)"
            :class="[
                'px-4 py-2 text-sm font-medium rounded-lg transition-all duration-200',
                selectedValue === tab.value
                    ? 'bg-white text-gray-900 shadow-sm'
                    : 'text-gray-600 hover:text-gray-900 hover:bg-white/50'
            ]"
        >
            <div class="flex items-center space-x-2">
                <component
                    v-if="tab.icon"
                    :is="tab.icon"
                    class="w-4 h-4"
                />
                <span>{{ tab.label }}</span>
            </div>
        </button>
    </div>
</template>

<script setup>
import { ref } from 'vue';

const props = defineProps({
    tabs: {
        type: Array,
        required: true,
        // Expected format:
        // [
        //   { label: 'Tab 1', value: 'tab1', icon?: Component },
        //   { label: 'Tab 2', value: 'tab2', icon?: Component }
        // ]
    },
    modelValue: {
        type: String,
        default: ''
    }
});

const emit = defineEmits(['update:modelValue', 'change']);

const selectedValue = ref(props.modelValue || (props.tabs[0]?.value ?? ''));

const selectTab = (tab) => {
    selectedValue.value = tab.value;
    emit('update:modelValue', tab.value);
    emit('change', tab);
};
</script>
