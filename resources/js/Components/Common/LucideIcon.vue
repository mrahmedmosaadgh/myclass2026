<script setup>
import { computed } from 'vue';
import * as icons from 'lucide-vue-next';

const props = defineProps({
    name: {
        type: String,
        required: true
    },
    size: {
        type: [Number, String],
        default: 24
    },
    color: {
        type: String,
        default: 'currentColor'
    },
    strokeWidth: {
        type: [Number, String],
        default: 2
    },
    class: {
        type: String,
        default: ''
    }
});

const iconComponent = computed(() => {
    // Convert kebab-case to PascalCase
    const componentName = props.name
        .split('-')
        .map(word => word.charAt(0).toUpperCase() + word.slice(1))
        .join('');

    return icons[componentName] || icons.HelpCircle; // Fallback to HelpCircle if icon not found
});
</script>

<template>
    <component
        :is="iconComponent"
        :size="size"
        :color="color"
        :stroke-width="strokeWidth"
        :class="class"
        style="display: inline-block; vertical-align: middle;"
    />
</template>
