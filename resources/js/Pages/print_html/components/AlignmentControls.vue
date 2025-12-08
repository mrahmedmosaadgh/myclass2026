<template>
    <div class="flex items-center gap-2">
        <button
            v-for="align in alignments"
            :key="align.value"
            @click="$emit('update:model-value', align.value)"
            :class="[
                'p-2 rounded hover:bg-gray-100',
                { 'bg-gray-200': modelValue === align.value }
            ]"
            type="button"
        >
            <span class="sr-only">{{ align.label }}</span>
            <component :is="align.icon" class="w-4 h-4" />
        </button>
    </div>
</template>

<script setup>
import { AlignLeft, AlignCenter, AlignRight, AlignJustify } from 'lucide-vue-next';

defineProps({
    modelValue: {
        type: String,
        default: 'left'
    }
});

const alignments = [
    { value: 'left', label: 'Align Left', icon: AlignLeft },
    { value: 'center', label: 'Align Center', icon: AlignCenter },
    { value: 'right', label: 'Align Right', icon: AlignRight },
    { value: 'justify', label: 'Justify', icon: AlignJustify },
];

defineEmits(['update:model-value']);
</script>