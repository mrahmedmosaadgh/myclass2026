<template>
    <div class="upcoming-events  bg-yellow-200 rounded-lg shadow-md p-1 mb-1">
        <h3 class="text-lg font-semibold mb-3 text-gray-700">Upcoming Classes</h3>
        <div v-if="events.length" class="space-y-3">
            <div v-for="event in events" :key="event.id"
                class="flex items-center p-2 rounded-md transition-colors"
                :style="{
                    backgroundColor: event.bgColor + '20',
                    borderLeft: `4px solid ${event.bgColor}`
                }"
            >
                <div class="flex-1 ">
                    <div class="font-medium text-gray-600 px-1 rounded shadow-lg" :style="{ color: event.textColor,'background-color': event.bgColor }">
                       {{ event.title }} {{ event.classroom ? `- ${event.classroom}` : '' }}


                    </div>
                    <div class="text-sm text-gray-600">
                        {{ event.label }} | {{ event.from }} - {{ event.to }}
                    </div>
                    <div class="  mt-1 font-medium"
                        :class="getCountdownColor(countdowns[event.id] || 0)">
                       <span class="p-1 text-xs  rounded-full">
                           Starts in
                        </span>
                       <span class="px-1 text-xl  rounded-full bg-black">

                         {{ formatCountdown(countdowns[event.id] || 0) }}
                        </span>

                    </div>
                </div>
            </div>
        </div>
        <div v-else class="text-gray-500 text-center py-2">
            No upcoming classes
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    events: {
        type: Array,
        required: true
    }
});

// Add reactive countdown values
const countdowns = ref({});
let timer = null;

// Calculate time until event starts (in seconds)
const getCountdown = (event) => {
    const now = new Date();
    const [hours, minutes] = event.from.split(':').map(Number);
    const eventTime = new Date();
    eventTime.setHours(hours, minutes, 0);

    return Math.max(0, Math.floor((eventTime - now) / 1000));
};

// Format the countdown in HH:MM:SS
const formatCountdown = (seconds) => {
    const hours = Math.floor(seconds / 3600);
    const minutes = Math.floor((seconds % 3600) / 60);
    const secs = seconds % 60;

    const pad = (num) => num.toString().padStart(2, '0');

    if (hours > 0) {
        return `${pad(hours)}:${pad(minutes)}:${pad(secs)}`;
    }
    return `${pad(minutes)}:${pad(secs)}`;
};

// Get color based on remaining seconds
const getCountdownColor = (seconds) => {
    const minutes = Math.floor(seconds / 60);
    if (minutes <= 5) return 'text-red-500';
    if (minutes <= 15) return 'text-orange-500';
    return 'text-green-600';
};

// Update template to use countdowns ref
const updateCountdowns = () => {
    props.events.forEach(event => {
        countdowns.value[event.id] = getCountdown(event);
    });
};

// Update the timer
onMounted(() => {
    // Initial update
    updateCountdowns();

    // Set interval for updates
    timer = setInterval(() => {
        updateCountdowns();
    }, 1000);
});

onUnmounted(() => {
    if (timer) clearInterval(timer);
});
</script>

<style scoped>
.countdown-urgent {
    @apply text-red-500;
}
.countdown-warning {
    @apply text-orange-500;
}
.countdown-normal {
    @apply text-green-600;
}
</style>
