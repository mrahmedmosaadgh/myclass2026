<template>
    <div v-if="events.length" class="mb-4 bg-white rounded-xl shadow-sm p-4">
        <h3 class="text-sm font-medium text-gray-700 mb-2">Upcoming Events</h3>
        <div class="space-y-2">
            <div v-for="event in events" :key="event.id"
                class="flex justify-between items-center text-sm">
                <div class="flex items-center">
                    <svg class="w-4 h-4 text-blue-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ event.title }}</span>
                </div>
                <span class="text-gray-500">
                    {{ getMinutesToStart(event) }}
                </span>
            </div>
        </div>
    </div>
</template>

<script setup>
const props = defineProps({
    events: {
        type: Array,
        required: true
    }
});

const getMinutesToStart = (event) => {
    const now = new Date();
    const [hours, minutes] = event.from.split(':').map(Number);
    const eventStart = new Date(now);
    eventStart.setHours(hours, minutes, 0, 0);
    const diffInMinutes = Math.round((eventStart - now) / 60000);
    return `Starts in ${diffInMinutes} minutes`;
};
</script>