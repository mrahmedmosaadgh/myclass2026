<template>
    <div class="flex items-center gap-2">
        <button
            v-for="format in formats"
            :key="format.type"
            @click="$emit('format', format.type)"
            :class="[
                'p-2 rounded hover:bg-gray-100',
                { 'bg-gray-200': activeFormats?.includes(format.type) }
            ]"
            type="button"
        >
            <span class="sr-only">{{ format.label }}</span>
            <component :is="format.icon" class="w-4 h-4" />
        </button>
    </div>
</template>

<script setup>
import { Bold, Italic, Underline } from 'lucide-vue-next';

const props = defineProps({
    activeFormats: {
        type: Array,
        default: () => []
    }
});

const formats = [
    { type: 'bold', label: 'Bold', icon: Bold },
    { type: 'italic', label: 'Italic', icon: Italic },
    { type: 'underline', label: 'Underline', icon: Underline },
];

defineEmits(['format']);
</script>
