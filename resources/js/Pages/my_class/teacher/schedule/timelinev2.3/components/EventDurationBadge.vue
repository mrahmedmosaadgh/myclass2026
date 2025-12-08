<template>
    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        {{ formatDuration }}
    </span>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    duration: {
        type: Number,
        required: true
    }
});

const formatDuration = computed(() => {
    const hours = Math.floor(props.duration / 60);
    const minutes = props.duration % 60;
    
    if (hours > 0) {
        return `${hours}h ${minutes > 0 ? minutes + 'm' : ''}`;
    }
    return `${minutes}m`;
});
</script>