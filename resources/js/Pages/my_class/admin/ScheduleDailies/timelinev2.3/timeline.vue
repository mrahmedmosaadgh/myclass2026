<template>
    <div class="timeline-outer-container">


        <!-- Current Event Card -->
        <CurrentEventCard
            v-if="getCurrentEvent && !isEventEnded(getCurrentEvent)"
            :event="getCurrentEvent"
            :currentTimeString="currentTimeString"
            :progress="getEventProgress(getCurrentEvent)"
            progress-bar-class="bg-blue-500"
            @edit="$emit('edit', $event)"
            @status-change="$emit('status-change', $event)"
        />


                <!-- Upcoming Events -->
                <UpcomingEvents :events="getUpcomingEvents" />
        <div class="p-0 fixed top-10 left-0">
            <UpcomingEvent  :events="getUpcomingEvents" />

        </div>
        <!-- Scroll to Now Button -->
        <button @click="scrollToNow"
            class="fixed bottom-4 right-4 bg-blue-500 hover:bg-blue-600 text-white rounded-full p-3 shadow-lg z-50 transition-all duration-200">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </button>

        <div class="timeline-container" ref="timelineRef">
            <!-- Grid container with increased height per hour -->
            <div class="relative" style="height: 2880px;"> <!-- 120px * 24 hours -->
                <!-- Time markers column - fixed width -->
                <div class="absolute left-0 w-20 h-full select-none border-r border-gray-200 bg-white">
                    <div v-for="hour in 24" :key="hour" class="relative h-[120px]">
                        <!-- Hour marker -->
                        <div class="absolute top-0 w-full flex items-center justify-end pr-2 h-6 bg-gray-50/40">
                            <span class="text-sm font-semibold text-gray-600">
                                {{ (hour - 1).toString().padStart(2, '0') }}:00
                            </span>
                        </div>

                        <!-- 15-minute marker -->
                        <div class="absolute top-[30px] w-full flex items-center justify-end pr-2">
                            <span class="text-xs text-gray-400">15</span>
                        </div>

                        <!-- 30-minute marker -->
                        <div class="absolute top-[60px] w-full flex items-center justify-end pr-2">
                            <span class="text-xs font-medium text-gray-500">30</span>
                        </div>

                        <!-- 45-minute marker -->
                        <div class="absolute top-[90px] w-full flex items-center justify-end pr-2">
                            <span class="text-xs text-gray-400">45</span>
                        </div>
                    </div>
                </div>

                <!-- Main timeline grid -->
                <div class="absolute left-20 right-0 h-full">
                    <!-- Hour and quarter-hour lines -->
                    <template v-for="hour in 24" :key="hour">
                        <!-- Main hour line -->
                        <div class="absolute w-full border-t"
                            :class="[
                                hour % 6 === 0
                                    ? 'border-gray-400 border-t-2' // Major hour marks (every 6 hours)
                                    : 'border-gray-300 border-t-[1.5px]', // Regular hour marks - darker now
                                'bg-gray-50/30' // Subtle background for hour marks
                            ]"
                            :style="{
                                top: `${(hour - 1) * 120}px`,
                                height: '2px'
                            }">
                        </div>

                        <!-- Hour background stripe -->
                        <div class="absolute w-full"
                            :class="[
                                hour % 2 === 0 ? 'bg-gray-50/30' : 'bg-transparent'
                            ]"
                            :style="{
                                top: `${(hour - 1) * 120}px`,
                                height: '120px'
                            }">
                        </div>

                        <!-- 15-minute line -->
                        <div class="absolute w-full border-t border-dashed border-gray-100"
                            :style="{ top: `${(hour - 1) * 120 + 30}px` }">
                        </div>

                        <!-- 30-minute line -->
                        <div class="absolute w-full border-t border-gray-200"
                            :style="{ top: `${(hour - 1) * 120 + 60}px` }">
                        </div>

                        <!-- 45-minute line -->
                        <div class="absolute w-full border-t border-dashed border-gray-100"
                            :style="{ top: `${(hour - 1) * 120 + 90}px` }">
                        </div>
                    </template>

                    <!-- Current time indicator -->
                    <div class="absolute left-0 right-0 z-30 transition-all duration-1000"
                        :style="{ top: `${currentTimePosition}px` }">
                        <div class="relative">
                            <div class="absolute -left-20 -top-2.5 flex items-center justify-end w-16">
                                <span class="text-sm font-semibold text-red-500">
                                    {{ currentTimeString }}
                                </span>
                                <div class="w-2 h-2 rounded-full bg-red-500 ml-2"></div>
                            </div>
                            <div class="border-t-2 border-red-500 w-full"></div>
                        </div>
                    </div>

                    <!-- Events -->
                    <TransitionGroup name="event" tag="div" class="relative">
                        <div v-for="event in events" :key="event.id"
                            class="absolute rounded-lg transition-all duration-300 group"
                            :style="getEventStyles(event)"
                            :class="[
                                'hover:z-30',
                                event.column > 0 ? 'ml-1' : '',
                            ]">
                            <EventCard
                                :event="event"
                                :status-class="getEventStatusClass(event.status)"
                                :header-class="getEventHeaderClass(event.status)"
                                :timing="getEventTiming(event)"
                                @edit="$emit('edit', $event)"
                                @delete="$emit('delete', $event)"
                            />
                        </div>
                    </TransitionGroup>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import UpcomingEvents from './components/UpcomingEvents.vue';
import UpcomingEvent  from './components/UpcomingEvent.vue';
import CurrentEventCard from './components/CurrentEventCard.vue';
import EventCard from './components/EventCard.vue';

const emit = defineEmits(['update:events', 'edit', 'delete', 'status-change']);

const timelineRef = ref(null);
const currentTimePosition = ref(getCurrentTimePosition());
const currentTimeString = ref(getCurrentTime());

// Timer references
let timeUpdateTimer = null;
let statusUpdateTimer = null;

const props = defineProps({
    events: {
        type: Array,
        required: true
    }
});

const getEventStyles = (event) => {
    // Parse the from and to times
    const [fromHours, fromMinutes] = event.from.split(':').map(Number);
    const [toHours, toMinutes] = event.to.split(':').map(Number);

    // Calculate start position (120px per hour, 2px per minute)
    const startMinutes = (fromHours * 60 + fromMinutes);
    const endMinutes = (toHours * 60 + toMinutes);

    // Calculate top position and height
    const top = `${startMinutes * 2}px`; // 2px per minute
    const height = `${(endMinutes - startMinutes) * 2}px`; // 2px per minute

    // Calculate width based on columns
    const width = `${95 / event.totalColumns}%`;
    const left = `${(event.column * (95 / event.totalColumns))}%`;

    return {
        top,
        height,
        width,
        left
    };
};

const getEventStatusClass = (status) => ({
    'border-l-4 border-blue-400 hover:border-blue-500': status === 'pending',
    'border-l-4 border-yellow-400 hover:border-yellow-500': status === 'in-progress',
    'border-l-4 border-green-400 hover:border-green-500': status === 'completed'
});

const getEventHeaderClass = (status) => ({
    'bg-blue-50/80 text-blue-700': status === 'pending',
    'bg-yellow-50/80 text-yellow-700': status === 'in-progress',
    'bg-green-50/80 text-green-700': status === 'completed'
});

// Get current time position in pixels
function getCurrentTimePosition() {
    const now = new Date();
    return (now.getHours() * 120 + (now.getMinutes() * 2)); // 120px per hour, 2px per minute
}

// Get formatted current time
function getCurrentTime() {
    return new Date().toLocaleTimeString([], {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: false
    });
}

// Update time indicators
function updateTimeIndicators() {
    currentTimePosition.value = getCurrentTimePosition();
    currentTimeString.value = getCurrentTime();
}

// Auto-scroll function
function autoScrollToCurrentTime() {
    const position = currentTimePosition.value;
    const containerHeight = timelineRef.value?.clientHeight || 0;
    const currentScroll = timelineRef.value?.scrollTop || 0;
    const viewportMiddle = containerHeight / 2;

    // Only auto-scroll if current time is not visible in viewport
    const timelineTop = currentScroll;
    const timelineBottom = currentScroll + containerHeight;

    if (position < timelineTop || position > timelineBottom) {
        timelineRef.value?.scrollTo({
            top: position - viewportMiddle,
            behavior: 'smooth'
        });
    }
}

// Manual scroll to current time
const scrollToNow = () => {
    const position = currentTimePosition.value;
    const containerHeight = timelineRef.value?.clientHeight || 0;
    timelineRef.value?.scrollTo({
        top: position - (containerHeight / 2),
        behavior: 'smooth'
    });
};

const getEventTiming = (event) => {
    const now = new Date();
    const [fromHours, fromMinutes] = event.from.split(':').map(Number);
    const [toHours, toMinutes] = event.to.split(':').map(Number);

    // Convert to current date
    const startTime = new Date(now);
    startTime.setHours(fromHours, fromMinutes, 0, 0);

    const endTime = new Date(now);
    endTime.setHours(toHours, toMinutes, 0, 0);

    const diffStart = startTime - now;
    const diffEnd = endTime - now;

    const minutesUntilStart = Math.floor(diffStart / 60000);
    const minutesUntilEnd = Math.floor(diffEnd / 60000);
    const secondsUntilEnd = Math.floor((diffEnd % 60000) / 1000);

    if (minutesUntilStart > 0) {
        return {
            type: 'upcoming',
            text: `Starts in ${minutesUntilStart} minutes`,
            color: 'text-blue-600'
        };
    } else if (minutesUntilEnd >= 0 || (minutesUntilEnd === 0 && secondsUntilEnd > 0)) {
        const minutes = String(Math.max(0, minutesUntilEnd)).padStart(2, '0');
        const seconds = String(Math.max(0, secondsUntilEnd)).padStart(2, '0');
        return {
            type: 'in-progress',
            text: `${minutes}:${seconds} minutes left`,
            color: 'text-green-600'
        };
    } else {
        return {
            type: 'ended',
            text: 'Ended',  // Changed text to just show 'Ended'
            color: 'text-gray-600',
            remove: true    // Add a flag to indicate this event should be removed
        };
    }
};

const shouldShowCurrentEvent = computed(() => {
    const now = new Date();
    return props.events.some(event => {
        const startTime = event.startTime;
        const endTime = new Date(startTime.getTime() + event.duration * 60000);
        return now >= startTime && now <= endTime;
    });
});

const getCurrentEvent = computed(() => {
    const now = new Date();
    const currentHour = now.getHours();
    const currentMinutes = now.getMinutes();
    const currentSeconds = now.getSeconds();
    const currentTimeInMinutes = currentHour * 60 + currentMinutes + (currentSeconds / 60);

    return props.events.find(event => {
        const [fromHours, fromMinutes] = event.from.split(':').map(Number);
        const [toHours, toMinutes] = event.to.split(':').map(Number);

        const eventStartMinutes = fromHours * 60 + fromMinutes;
        const eventEndMinutes = toHours * 60 + toMinutes;

        // Check if the event is currently active, not ended, and within its time range
        return currentTimeInMinutes >= eventStartMinutes &&
               currentTimeInMinutes < eventEndMinutes &&
               event.status !== 'completed';
    });
});

const getEventStatusColors = (status) => {
    const colors = {
        'pending': 'bg-blue-100 text-blue-800',
        'in-progress': 'bg-yellow-100 text-yellow-800',
        'completed': 'bg-green-100 text-green-800'
    };
    return colors[status] || 'bg-gray-100 text-gray-800';
};

const getEventProgressBarClass = (event) => {
    if (!event) return '';
    return {
        'bg-blue-100': event.status === 'pending',
        'bg-yellow-100': event.status === 'in-progress',
        'bg-green-100': event.status === 'completed'
    };
};

const getEventProgress = (event) => {
    if (!event) return 0;

    const now = new Date();
    const [fromHours, fromMinutes] = event.from.split(':').map(Number);
    const [toHours, toMinutes] = event.to.split(':').map(Number);

    // Convert all times to minutes for easier calculation
    const eventStartMinutes = fromHours * 60 + fromMinutes;
    const eventEndMinutes = toHours * 60 + toMinutes;
    const currentTimeMinutes = now.getHours() * 60 + now.getMinutes();

    // Calculate duration and elapsed time
    const duration = eventEndMinutes - eventStartMinutes;
    const elapsed = currentTimeMinutes - eventStartMinutes;

    // Calculate progress percentage
    const progress = Math.min(100, Math.max(0, (elapsed / duration) * 100));

    return progress;
};

const formatTime = (time) => {
    if (typeof time === 'string') {
        return time;
    }
    return new Date(time).toLocaleTimeString([], {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: false
    });
};

onMounted(() => {
    // Initial update
    updateTimeIndicators();
    if (props.events) {
        props.events.forEach(updateEventStatus);
    }

    // Update every 100ms for smooth transitions
    timeUpdateTimer = setInterval(() => {
        updateTimeIndicators();
        if (props.events) {
            props.events.forEach(updateEventStatus);
            emit('update:events', [...props.events]);
        }
    }, 100);

    scrollToNow();
});

onUnmounted(() => {
    if (timeUpdateTimer) clearInterval(timeUpdateTimer);
    if (statusUpdateTimer) clearInterval(statusUpdateTimer);
});

// Add automatic status update function
const updateEventStatus = (event) => {
    const now = new Date();
    const currentHour = now.getHours();
    const currentMinutes = now.getMinutes();
    const currentSeconds = now.getSeconds();
    const currentTimeInMinutes = currentHour * 60 + currentMinutes + (currentSeconds / 60);

    const [fromHours, fromMinutes] = event.from.split(':').map(Number);
    const [toHours, toMinutes] = event.to.split(':').map(Number);

    const eventStartMinutes = fromHours * 60 + fromMinutes;
    const eventEndMinutes = toHours * 60 + toMinutes;

    if (currentTimeInMinutes < eventStartMinutes) {
        event.status = 'pending';
    } else if (currentTimeInMinutes >= eventStartMinutes && currentTimeInMinutes < eventEndMinutes) {
        event.status = 'in-progress';
    } else {
        event.status = 'completed';
    }
};

// Add to your computed properties
const getUpcomingEvents = computed(() => {
    const now = new Date();
    return props.events
        .filter(event => {
            const [hours, minutes] = event.from.split(':').map(Number);
            const eventStart = new Date(now);
            eventStart.setHours(hours, minutes, 0, 0);
            // Only include events that haven't started yet
            return eventStart > now;
        })
        .sort((a, b) => {
            const [aHours, aMinutes] = a.from.split(':').map(Number);
            const [bHours, bMinutes] = b.from.split(':').map(Number);
            return (aHours * 60 + aMinutes) - (bHours * 60 + bMinutes);
        })
        .slice(0, 3);
});

// Add to your methods
const getMinutesToStart = (event) => {
    const now = new Date();
    const [hours, minutes] = event.from.split(':').map(Number);

    const eventStart = new Date(now);
    eventStart.setHours(hours, minutes, 0, 0);

    const diffMs = eventStart - now;
    const diffMinutes = Math.floor(diffMs / 60000);
    const diffSeconds = Math.floor((diffMs % 60000) / 1000);

    // If the event should have started, remove it from upcoming
    if (diffMs <= 0) {
        return '';
    }

    if (diffMinutes > 0) {
        return `${diffMinutes} minutes to start`;
    } else {
        return `${String(Math.max(0, diffSeconds)).padStart(2, '0')} seconds to start`;
    }
};

const isEventEnded = (event) => {
    const now = new Date();
    const currentHour = now.getHours();
    const currentMinutes = now.getMinutes();
    const currentTimeInMinutes = currentHour * 60 + currentMinutes;

    const [toHours, toMinutes] = event.to.split(':').map(Number);
    const eventEndMinutes = toHours * 60 + toMinutes;

    return currentTimeInMinutes >= eventEndMinutes;
};
</script>

<style scoped>
.timeline-outer-container {
    @apply relative;
    height: calc(100vh - 200px); /* Adjust based on your layout */
}

.timeline-container {
    @apply relative bg-white rounded-xl shadow-sm p-6 overflow-y-auto;
    height: 100%;
    background-image: linear-gradient(to right, transparent 0%, rgba(249, 250, 251, 0.5) 100%);
}

/* Enhanced grid lines */
.border-gray-150 {
    border-color: rgba(156, 163, 175, 0.25);
}

.border-dashed {
    border-style: dashed;
    border-width: 1px;
    border-color: rgba(0, 0, 0, 0.05);
}

/* Timeline grid backgrounds */
.bg-gray-50\/20 {
    background-color: rgba(249, 250, 251, 0.2);
}

.bg-gray-50\/30 {
    background-color: rgba(249, 250, 251, 0.3);
}

/* Custom scrollbar styling */
.timeline-container::-webkit-scrollbar {
    width: 8px;
}

.timeline-container::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

.timeline-container::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 4px;
}

.timeline-container::-webkit-scrollbar-thumb:hover {
    background: #555;
}

/* Event animations */
.event-enter-active,
.event-leave-active {
    transition: all 0.3s ease;
}

.event-enter-from,
.event-leave-to {
    opacity: 0;
    transform: translateY(30px);
}

/* Hover effects */
.event-move {
    transition: transform 0.5s ease;
}

/* Ensure content doesn't overflow */
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Add smooth transition for current time indicator */
.transition-all {
    transition-property: all;
    transition-timing-function: linear;
}
</style>























