<template>
    <div class="w-full">
        <select
            :value="modelValue"
            @input="$emit('update:modelValue', $event.target.value)"
            @change="$emit('change', $event.target.value)"
            class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
        >
            <option value="">{{ placeholder }}</option>
            <option 
                v-for="option in options" 
                :key="option[valueKey]" 
                :value="option[valueKey]"
            >
                {{ option[labelKey] }}
            </option>
        </select>
    </div>
</template>

<script setup>
defineProps({
    modelValue: {
        type: [String, Number],
        default: ''
    },
    options: {
        type: Array,
        default: () => []
    },
    placeholder: {
        type: String,
        default: 'Select an option'
    },
    valueKey: {
        type: String,
        default: 'value'
    },
    labelKey: {
        type: String,
        default: 'label'
    }
});

defineEmits(['update:modelValue', 'change']);
</script>