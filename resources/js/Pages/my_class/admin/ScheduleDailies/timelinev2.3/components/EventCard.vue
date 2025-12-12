<template>
    <div class="h-full rounded-lg shadow-sm hover:shadow-xl transition-all duration-200 bg-white relative group overflow-hidden"
        :class="computedStatusClass">
        <!-- Decorative gradient overlay -->
        <div class="absolute inset-0 opacity-5 bg-gradient-to-br from-white via-transparent to-black pointer-events-none"></div>

        <!-- Event Header -->
        <div class="px-4 py-2 rounded-t-lg backdrop-blur-sm backdrop-saturate-150" :class="headerClass">
            <div class="flex justify-between items-start">
                <h4 class="font-semibold text-sm truncate max-w-[80%] group-hover:text-opacity-100 transition-all">
                    {{ event.title }}
                </h4>
                <span class="text-xs font-medium bg-white/30 px-2 py-0.5 rounded-full">
                    {{ formatTime(event.startTime) }}
                </span>
            </div>
        </div>

        <!-- Event Body -->
        <div class="px-4 py-2 relative">
            <p class="text-xs text-gray-600 line-clamp-2 group-hover:line-clamp-none transition-all duration-300">
                {{ event.description }}
            </p>

            <!-- Status and Duration Badges -->
            <div class="mt-2.5 flex items-center gap-2 flex-wrap">
                <EventTimingBadge :timing="timing" />
                <EventDurationBadge :duration="calculateDuration" />
            </div>

            <!-- Action Buttons -->
            <div class="absolute bottom-2 right-3 flex gap-1.5 transform translate-x-full group-hover:translate-x-0 transition-transform duration-300 ease-out">
                <button @click="$emit('edit', event)"
                    class="p-1.5 rounded-full bg-white/80 hover:bg-blue-50 text-gray-600 hover:text-blue-600 transition-colors shadow-sm backdrop-blur-sm">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                    </svg>
                </button>
                <button @click="$emit('delete', event.id)"
                    class="p-1.5 rounded-full bg-white/80 hover:bg-red-50 text-gray-600 hover:text-red-600 transition-colors shadow-sm backdrop-blur-sm">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import EventTimingBadge from './EventTimingBadge.vue';
import EventDurationBadge from './EventDurationBadge.vue';

const props = defineProps({
    event: {
        type: Object,
        required: true
    },
    statusClass: {
        type: [String, Object],
        required: true
    },
    headerClass: {
        type: [String, Object],
        required: true
    },
    timing: {
        type: Object,
        required: true,
        validator: (value) => {
            return value && typeof value === 'object' && 'type' in value && 'text' in value;
        }
    }
});

// Calculate duration in minutes from 'from' and 'to' times
const calculateDuration = computed(() => {
    if (!props.event.from || !props.event.to) return 0;

    const [fromHours, fromMinutes] = props.event.from.split(':').map(Number);
    const [toHours, toMinutes] = props.event.to.split(':').map(Number);

    const startMinutes = (fromHours * 60) + fromMinutes;
    const endMinutes = (toHours * 60) + toMinutes;

    return endMinutes - startMinutes;
});

const computedStatusClass = computed(() => {
    return typeof props.statusClass === 'string' ? props.statusClass : Object.keys(props.statusClass).filter(key => props.statusClass[key]).join(' ');
});

defineEmits(['edit', 'delete']);

const formatTime = (time) => {
    return time.toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit',
        hour12: false
    });
};
</script>





