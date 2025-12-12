<template>
    <input
        type="text"
        :value="modelValue"
        @input="$emit('update:model-value', $event.target.value)"
        class="border-none text-xl font-semibold focus:ring-0 w-64"
        placeholder="Enter title..."
    >
</template>

<script setup>
defineProps({
    modelValue: {
        type: String,
        default: ''
    }
});

defineEmits(['update:model-value']);
</script>
