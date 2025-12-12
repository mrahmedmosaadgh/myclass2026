<template>
    <div class="flex items-center gap-2">
        <label class="text-sm text-gray-600">Line Spacing:</label>
        <select
            :value="modelValue"
            @change="$emit('update:model-value', parseFloat($event.target.value))"
            class="border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
        >
            <option value="1">Single</option>
            <option value="1.15">1.15</option>
            <option value="1.5">1.5</option>
            <option value="2">Double</option>
        </select>
    </div>
</template>

<script setup>
defineProps({
    modelValue: {
        type: Number,
        default: 1.5
    }
});

defineEmits(['update:model-value']);
</script>