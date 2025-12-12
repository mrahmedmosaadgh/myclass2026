<template>
    <div v-if="events.length" class=" scale-75    rounded-xl shadow-sm p-1">
        <span class="text-sm font-medium text-gray-700 mb-2">Next:</span>
        <div class="space-y-2">
            <div
                class="flex justify-between items-center text-sm">
                <div class="flex items-center"  >
                    <svg class="w-4 h-4 text-blue-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div class="p-0 flex flex-col justify-between ">

                        <span class="bg-gray-400 px-2 rounded text-gray-100">{{ events[0].title }}</span>
                        <span class="text-gray-500">
                            {{ getMinutesToStart(events[0]) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>




        <div class="space-y-2">
            <div
                class="flex justify-between items-center text-sm">
                <div class="flex items-center"  >
                    <svg class="w-4 h-4 text-blue-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div class="p-0 flex flex-col justify-between ">

                        <span class="bg-gray-400 px-2 rounded text-gray-100">{{ events?.[1]?.title }}</span>
                        <span class="text-gray-500">
                            {{ getMinutesToStart(events?.[1]) }}
                        </span>
                    </div>
                </div>
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

    const diffMs = eventStart - now;
    const diffMins = Math.floor(diffMs / 60000);

    if (diffMins < 1) {
        return 'Starting now';
    } else if (diffMins === 1) {
        return 'Starts in 1 minute';
    } else {
        return `Starts in ${diffMins} minutes`;
    }
};
</script>

