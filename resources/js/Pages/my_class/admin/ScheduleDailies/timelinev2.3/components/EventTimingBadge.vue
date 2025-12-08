<template>
    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium" :class="[timing.color || badgeClass]">
        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        {{ timing.text }}
    </span>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    timing: {
        type: Object,
        required: true,
        validator: (value) => {
            return value &&
                   typeof value === 'object' &&
                   'type' in value &&
                   'text' in value;
        }
    }
});

const badgeClass = computed(() => {
    switch (props.timing.type) {
        case 'upcoming':
            return 'bg-blue-100 text-blue-800';
        case 'in-progress':
            return 'bg-yellow-100 text-yellow-800';
        case 'ended':
            return 'bg-gray-100 text-gray-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
});
</script>

