<template>
    <div v-if="event" class="mb-6 bg-white rounded-xl shadow-lg overflow-hidden border border-gray-100">
        <div class="relative">
            <!-- Status Bar -->
            <div class="absolute top-0 left-0 w-full h-1.5 bg-gray-100">
                <div
                    class="h-full transition-all duration-1000"
                    :class="progressBarClass"
                    :style="{ width: `${progress}%` }"
                >
                </div>
            </div>

            <div class="p-6 pt-8">
                <!-- Header Section -->
                <div class="flex items-start justify-between mb-4">
                    <div class="space-y-1">
                        <h2 class="text-xl font-semibold text-gray-900">{{ event.title }}</h2>
                        <p class="text-sm text-gray-600">{{ event.description }}</p>
                    </div>
                    <div class="flex flex-col items-end gap-2">
                        <!-- Time Badge -->
                        <div class="px-3 py-1.5 bg-blue-50 rounded-lg">
                            <div class="text-xs text-blue-600 font-medium">Time Remaining</div>
                            <div class="text-lg font-semibold text-blue-700">{{ timeRemaining }}</div>
                        </div>


                                                <!-- Time Badge fixed top left -->
                        <div class="px-3 py-1.5   rounded-lg fixed top-0 left-0">
                            <div class="p-0 flex flex-col justify-center w-full relative">

                                <span class=" w-24  font-semibold text-gray-200 rounded bg-black text-center">{{ currentTimeString }}</span>

                                <div class="  font-semibold text-blue-800 rounded   text-center">
                                    <span class="  font-semibold text-green-100 bg-black ">{{ event.title }}</span>

                                    <span class="p-0 text-xs text-gray-400">left: </span> {{ timeRemaining }}</div>
                            </div>
                        </div>


                    </div>
                </div>

                <!-- Time Info -->
                <div class="flex items-center gap-6 mb-6">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span class="text-sm text-gray-600">
                            {{ formatTime(event.from) }} - {{ formatTime(event.to) }}
                        </span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="text-sm text-gray-600">
                            {{ formatDuration(calculateDuration) }}
                        </span>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-3">


    <svg
     @click="$emit('edit', event)"
    class="  h-6 mr-2 cursor-pointer
text-gray-700 bg-white hover:bg-gray-200 px-2 p-1 rounded focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors
    " fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
        d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
    </svg>

                    <!-- <button @click="$emit('edit', event)"
                        class="flex-1 inline-flex items-center justify-center px-4 py-2 border border-gray-200 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                        </svg>
                        Edit Event
                    </button>
                    <button @click="$emit('status-change', event)"
                        class="flex-1 inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-lg text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Update Status
                    </button> -->
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, ref, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    event: {
        type: Object,
        required: true
    },
    progress: {
        type: Number,
        required: true
    },
    progressBarClass: {
        type: String,
        default: 'bg-blue-500'
    }
,
currentTimeString: {
        type: String,
        default: '00:00:00'
    }

});

defineEmits(['edit', 'status-change']);

// Add ref for time remaining
const timeRemaining = ref('00:00');
let timer = null;

// Calculate time remaining
const calculateTimeRemaining = () => {
    const now = new Date();
    const [hours, minutes] = props.event.to.split(':').map(Number);
    const endTime = new Date(now);
    endTime.setHours(hours, minutes, 0, 0);

    const diffMs = endTime - now;
    const diffMins = Math.floor(diffMs / 60000);
    const diffSecs = Math.floor((diffMs % 60000) / 1000);

    // If time is up, clear the interval
    if (diffMs <= 0) {
        timeRemaining.value = '00:00';
        if (timer) {
            clearInterval(timer);
        }
        return;
    }

    timeRemaining.value = `${String(diffMins).padStart(2, '0')}:${String(diffSecs).padStart(2, '0')}`;
};

// Lifecycle hooks
onMounted(() => {
    // Initial calculation
    calculateTimeRemaining();
    // Update every second
    timer = setInterval(calculateTimeRemaining, 1000);
});

onUnmounted(() => {
    if (timer) {
        clearInterval(timer);
    }
});

// Calculate remaining time
const calculateDuration = computed(() => {
    if (!props.event.from || !props.event.to) return 0;
    const [fromHours, fromMinutes] = props.event.from.split(':').map(Number);
    const [toHours, toMinutes] = props.event.to.split(':').map(Number);
    return ((toHours * 60) + toMinutes) - ((fromHours * 60) + fromMinutes);
});

const formatTime = (time) => {
    if (!time) return '';
    const [hours, minutes] = time.split(':');
    return `${hours.padStart(2, '0')}:${minutes.padStart(2, '0')}`;
};

const formatDuration = (minutes) => {
    const hrs = Math.floor(minutes / 60);
    const mins = minutes % 60;
    return hrs > 0 ? `${hrs}h ${mins}m` : `${mins}m`;
};
</script>


