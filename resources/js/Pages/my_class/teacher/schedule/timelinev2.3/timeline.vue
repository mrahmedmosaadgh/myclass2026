<template>
        <!-- Current Time Display -->
        <!-- <div class="time-display">
              <div class="text-sm font-semibold text-white">{{ currentTimeString }}</div>
        </div> -->
                <!-- Current Event Card -->
                <div class="current-event-wrapper">
                    <CurrentEventCard class="  fixed top-10 left-0  z-40"
                        v-if="currentEvent"
                        :event="currentEvent"
                        :current-time-string="currentTimeString"
                        :progress="getEventProgress(currentEvent)"
                    />
                </div>
   <!-- Upcoming Events -->
                <div class="upcoming-events-wrapper ">
                    <UpcomingEventsCard class=" fixed  right-0 md:top-10  top-40  z-40"
                        v-if="upcomingEvents.length"
                        :events="upcomingEvents"
                    />
                </div>


    <div class="timeline-wrapper">




            <!-- Event Cards Container -->
            <div class="event-cards-container scale-75  z-20">



            </div>














        <div class="timeline-outer-container">
            <!-- Loading Overlay -->
            <q-inner-loading :showing="loading">
                <q-spinner-dots color="primary" size="40px" />
            </q-inner-loading>

            <!-- Day Tabs -->
            <q-tabs
                v-model="activeDay"
                class="bg-white text-primary shadow-1 sticky top-0  "
                indicator-color="primary"
                :breakpoint="300"
                mobile-arrows
            >
                <q-tab
                    v-for="(day, index) in days"
                    :key="day.code"
                    :name="index"
                    @click="setActiveDay(index)"
                >
                    <div class="column  items-center text-gray-400">
                  <!-- <span class="md:hidden block text-gray-400">rrr{{ day.label }}</span> -->
                  <span class=" sm:hidden md:block text-gray-400">{{ day.label }}</span>
                        <span class="md:hidden text-gray-400">{{ day.label.slice(0,3) }}</span>
                        <q-badge
                            v-if="day.isToday"
                            color="primary"
                            text-color="white"
                            label="Today"
                            class="q-mt-xs"
                        />
                    </div>
                </q-tab>
            </q-tabs>


            <!-- Timeline Container -->
            <div class="timeline-container" ref="timelineRef">
                <div class="relative" style="height: 1320px;">
                    <!-- Time Markers -->
                    <div class="absolute left-0 w-20 h-full select-none border-r border-gray-200 bg-white">
                        <div v-for="hour in 11" :key="hour" class="relative h-[120px]">
                            <div class="absolute top-0 w-full flex items-center justify-end pr-2 h-6 bg-gray-50/40">
                                <span class="text-sm font-semibold text-gray-600">
                                    {{ (hour + 5).toString().padStart(2, '0') }}:00
                                </span>
                            </div>
                            <template v-for="minute in 11" :key="`${hour}-${minute}`">
                                <div v-if="minute > 0"
                                    class="absolute w-full flex items-center justify-end pr-2"
                                    :style="{ top: `${minute * 10}px` }"
                                >
                                    <span class="text-xs text-gray-400">
                                        {{ ((minute * 5) % 60).toString().padStart(2, '0') }}
                                    </span>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Events -->
                    <div class="absolute left-20 right-0 h-full">
                        <TransitionGroup name="event" tag="div" class="relative">
                            <div v-for="event in timelineEvents" :key="event.id"
                                class="absolute transition-all duration-300 group hover:z-30"
                                :style="getEventStyles(event)"
                                @click="openPeriodActivity(event)"
                            >
                                <div class="full-height my-0 py-1 relative" :style="getEventCardStyles(event)">
                                    <div class="px-2">
  <!-- <div class="q-pa-md absolute right-0 top-0">
    <q-btn color="purple" class=" " label="Account Settings">
      <q-menu>
   <div class="p-4">

    {{ event }}
   </div>
      </q-menu>
    </q-btn>
  </div> -->


  <div class="p-1 flex flex-row gap-4" v-if="event.classroom!==null">

      <!-- <div class="text-caption">{{ event  }}</div> -->
      <div class="text-caption">{{ event.label }}</div>
      <div class="text-subtitle  ">{{ event.title }} - {{ event.classroom }}</div>
      <div class=" text-caption">{{ event.from }} - {{ event.to }}</div>
  </div>



                                </div>
                            </div>
                        </div>
                    </TransitionGroup>

                    <!-- Current Time Indicator -->
                    <div v-if="currentTimePosition"
                        class="absolute left-0 right-0 z-30 transition-all duration-1000"
                        :style="{ top: `${currentTimePosition}px` }"
                    >
                        <div class="relative ">
                            <div class="absolute  left-0 border-2 border-red-500 rounded-full px-2 overflow-visible   flex-row   whitespace-nowrap  -top-2.5 flex items-center justify-end  ">

                               <div class="p-0   relative -mx-1 bg-red-100 flex flex-row        items-center justify-end ">

                                   <div class="text-sm   font-semibold text-red-500  ">{{ currentTimeString }}</div>
                                   <div class="w-2 h-2 absolute -left-6 rounded-full bg-red-500 ml-2"></div>
                                </div>
                            </div>
                            <div class="border-t-2 border-red-500 w-full"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Scroll to Now Button -->
            <q-btn
                @click="scrollToNow"
                round
                color="primary"
                icon="mdi-clock-outline"
                class="scroll-to-now-btn"
            />
        </div>
    </div>
    </div>

    <!-- Period Activity Drawer -->
    <PeriodActivityDrawer
        v-model="periodActivityDrawerOpen"
        :period-data="selectedPeriodData"
        :calendar-date="days[activeDay].date"
        @update:model-value="(val) => { if (!val) closePeriodActivityDrawer() }"
    />
</template>

<script setup>
import {   usePage  } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted, computed, watch } from 'vue';
import CurrentEventCard from './components/CurrentEventCard.vue';
import UpcomingEventsCard from './components/UpcomingEventsCard.vue';
import PeriodActivityDrawer from './components/PeriodActivityDrawer.vue';
import { useAppStore } from '@/Stores/AppStore';

const props = defineProps({
  events: {
    type: Array,
    default: () => []
  }
});

const appStore = useAppStore();
const timelineRef = ref(null);
const currentTimePosition = ref(getCurrentTimePosition());
const currentTimeString = ref(getCurrentTime());
const loading = ref(false);
const timelineEvents = ref(props.events || []);
let timeUpdateTimer = null;

// Watch for changes in props.events
watch(() => props.events, (newEvents) => {
  if (newEvents && newEvents.length) {
    timelineEvents.value = newEvents.map(validateAndFixEventTime).map(normalizeEvent);
  }
}, { immediate: true, deep: true });

// Period Activity Drawer
const periodActivityDrawerOpen = ref(false);
const selectedPeriodData = ref(null);

// Function to open the period activity drawer
const openPeriodActivity = (event) => {
  // Skip if it's a break period
  if (event.type === 'break') return;

  // Set the selected period data
  selectedPeriodData.value = {
    id: event.id,
    schedule_id: event.id,
    title: event.title,
    classroom: event.classroom,
    subject: event.title,
    from: event.from,
    to: event.to,
    day: days.value[activeDay.value].label,
    day_code: days.value[activeDay.value].code,
    teacher_id: event.teacher_id,
    classroom_id: event.classroom_id
  };

  // Open the drawer
  periodActivityDrawerOpen.value = true;
};

// Function to close the period activity drawer
const closePeriodActivityDrawer = () => {
  periodActivityDrawerOpen.value = false;
  selectedPeriodData.value = null;
};

const days = ref([
    { label: 'Sunday', code: 'd1', isToday: false, date: null },
    { label: 'Monday', code: 'd2', isToday: false, date: null },
    { label: 'Tuesday', code: 'd3', isToday: false, date: null },
    { label: 'Wednesday', code: 'd4', isToday: false, date: null },
    { label: 'Thursday', code: 'd5', isToday: false, date: null },
    { label: 'Friday', code: 'd6', isToday: false, date: null },
    { label: 'Saturday', code: 'd7', isToday: false, date: null }
]);

const activeDay = ref(new Date().getDay());

// Event styling and positioning
const getEventStyles = (event) => {
    if (!event?.from || !event?.to) return {};

    const [fromHours, fromMinutes] = event.from.split(':').map(Number);
    const [toHours, toMinutes] = event.to.split(':').map(Number);
    const startMinutes = ((fromHours - 6) * 60 + fromMinutes);
    const duration = ((toHours - fromHours) * 60 + (toMinutes - fromMinutes));

    return {
        position: 'absolute',
        top: `${startMinutes * 2}px`,
        height: `${duration * 2}px`,
        left: '2.5%',
        width: '95%',
        zIndex: event.type === 'break' ? 10 : 20
    };
};

const getEventCardStyles = (event) => ({
    backgroundColor: event.bgColor || '#fff',
    color: event.textColor || '#000',
    opacity: event.type === 'break' ? 0.8 : 1,
    border: '1px solid rgba(0,0,0,0.1)',
    borderRadius: '0.375rem'
});

// Time management functions
function getCurrentTimePosition() {
    const now = new Date();
    const hours = now.getHours();
    const minutes = now.getMinutes();
    return hours >= 6 && hours < 16 ? ((hours - 6) * 120 + (minutes * 2)) : null;
}

function getCurrentTime() {
    var time_now = new Date().toLocaleTimeString([], {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: false
    });
    return time_now;
}

function getCurrentDateTime() {
    var time_now = new Date().toLocaleTimeString([], {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: false
    });
    return time_now;
}

function updateTimeIndicators() {
    currentTimePosition.value = getCurrentTimePosition();
    currentTimeString.value = getCurrentTime();
}

// Add this function at the top of your script section
const getTestDate = () => {
    // Test date: Sunday 8:10 AM
    const testDate = new Date();
    testDate.setHours(8, 10, 0); // Set to 8:10 AM
    testDate.setDate(testDate.getDate() - testDate.getDay()); // Set to Sunday
    return testDate;
};

// Event calculations
const currentEvent = computed(() => {
    if (!timelineEvents.value?.length) return null;

    const now = new Date();
    //  const now = getTestDate();
    const currentTimeInMinutes = now.getHours() * 60 + now.getMinutes();

    return timelineEvents.value.find(event => {
        if (!event?.from || !event?.to || event.type === 'break') return false;
        const [fromHours, fromMinutes] = event.from.split(':').map(Number);
        const [toHours, toMinutes] = event.to.split(':').map(Number);
        const startMinutes = fromHours * 60 + fromMinutes;
        const endMinutes = toHours * 60 + toMinutes;
        return currentTimeInMinutes >= startMinutes && currentTimeInMinutes < endMinutes;
    });
});

const upcomingEvents = computed(() => {
    if (!timelineEvents.value?.length) return [];

    const now = new Date();
    const currentTimeInMinutes = now.getHours() * 60 + now.getMinutes();

    return timelineEvents.value
        .filter(event => {
            if (!event?.from  ) return false;
            // if (!event?.from || event.type === 'break') return false;
            const [fromHours, fromMinutes] = event.from.split(':').map(Number);
            const startMinutes = fromHours * 60 + fromMinutes;
            return startMinutes > currentTimeInMinutes;
        })
        .sort((a, b) => {
            const [aHours, aMinutes] = a.from.split(':').map(Number);
            const [bHours, bMinutes] = b.from.split(':').map(Number);
            return (aHours * 60 + aMinutes) - (bHours * 60 + bMinutes);
        })
        .slice(0, 2);
});

const getEventProgress = (event) => {
    if (!event) return 0;
    const now = new Date();
    const [fromHours, fromMinutes] = event.from.split(':').map(Number);
    const [toHours, toMinutes] = event.to.split(':').map(Number);
    const startMinutes = fromHours * 60 + fromMinutes;
    const endMinutes = toHours * 60 + toMinutes;
    const currentMinutes = now.getHours() * 60 + now.getMinutes();
    return Math.min(100, Math.max(0, ((currentMinutes - startMinutes) / (endMinutes - startMinutes)) * 100));
};

// Add this computed property to fix the period code issue
const normalizeEvent = (event) => {
  if (event.type === 'break') {
    return {
      ...event,
      periodCode: `break${event.label.match(/\d+/)?.[0] || ''}`,
      label: `Break ${event.label.match(/\d+/)?.[0] || ''}`
    };
  }
  return event;
};

// Add this validation function
const validateAndFixEventTime = (event) => {
    if (!event?.from || !event?.to) return event;

    // Fix incorrect times (like 22:05 should be 13:05)
    if (event.from.startsWith('22:')) {
        event.from = '13:05'; // Correct the time for Period 7
    }

    // Ensure times are in valid format
    const [fromHours, fromMinutes] = event.from.split(':').map(Number);
    const [toHours, toMinutes] = event.to.split(':').map(Number);

    // Validate and fix if needed
    if (fromHours > 23 || fromHours < 0 || fromMinutes > 59 || fromMinutes < 0) {
        console.warn(`Invalid time format found: ${event.from}`);
        event.from = '13:05'; // Set default if invalid
    }

    if (toHours > 23 || toHours < 0 || toMinutes > 59 || toMinutes < 0) {
        console.warn(`Invalid time format found: ${event.to}`);
        event.to = '13:50'; // Set default if invalid
    }

    return event;
};

// API and data management
const fetchSchedule = (dayCode) => {
    console.log('fetchSchedule')
     const school_id =   usePage().props.auth.user?.teacher?.school_id
    // const school_id = localStorage.getItem('selected_school');
    if (!school_id) {
        console.warn('No school ID found in localStorage');
        return;
    }

    loading.value = true;

    appStore.fetchData({
        endpoint: '/teacher/timeline/schedule',
        method: 'post',
        data: { school_id, day_code: dayCode }
    })
    .then(response => {
        if (!response.success) {
            throw new Error(response.message || 'Failed to fetch schedule');
        }

        if (!response.data?.schedule || !Array.isArray(response.data.schedule)) {
            throw new Error('Invalid schedule data received');
        }

        timelineEvents.value = response.data.schedule
            .map(validateAndFixEventTime)
            .map(normalizeEvent)
            .sort((a, b) => {
                const aTime = a.from.split(':').map(Number);
                const bTime = b.from.split(':').map(Number);
                return (aTime[0] * 60 + aTime[1]) - (bTime[0] * 60 + bTime[1]);
            });
    })
    .catch(error => {
        console.error('Failed to fetch schedule:', error);
        timelineEvents.value = [];
    })
    .finally(() => {
        loading.value = false;
    });
};

// UI interactions
const setActiveDay = (dayIndex) => {
    activeDay.value = dayIndex;
    fetchSchedule(days.value[dayIndex].code);
};

const scrollToNow = () => {
    const position = currentTimePosition.value;
    if (!position || !timelineRef.value) return;

    timelineRef.value.scrollTo({
        top: position - (timelineRef.value.clientHeight / 2),
        behavior: 'smooth'
    });
};

// Lifecycle hooks with cleanup
onMounted(() => {
    const today = new Date();
    const dayOfWeek = today.getDay();

    // Set today flag
    days.value[dayOfWeek].isToday = true;
    activeDay.value = dayOfWeek;

    // Set dates for each day of the week
    for (let i = 0; i < 7; i++) {
        const date = new Date(today);
        date.setDate(today.getDate() - dayOfWeek + i);
        days.value[i].date = date.toISOString().split('T')[0]; // Format as YYYY-MM-DD
    }

    fetchSchedule(days.value[dayOfWeek].code);
    updateTimeIndicators();
    timeUpdateTimer = setInterval(updateTimeIndicators, 1000);

    // Initial scroll to current time
    setTimeout(scrollToNow, 500);
});

onUnmounted(() => {
    if (timeUpdateTimer) clearInterval(timeUpdateTimer);
});
</script>

<style scoped>
.timeline-wrapper {
    @apply w-full min-h-screen bg-gray-50;
}

.timeline-outer-container {
    @apply relative h-[calc(100vh-100px)] overflow-hidden max-w-7xl mx-auto;
}

.event-cards-container {
    @apply grid gap-4 p-4 md:grid-cols-2 lg:grid-cols-3;
    @apply sticky top-[50px] z-30 bg-gray-50/80 backdrop-blur-sm;
}

.current-event-wrapper {
    @apply md:col-span-1 lg:col-span-2;
}

.upcoming-events-wrapper {
    @apply md:col-span-1;
}

.timeline-container {
    @apply relative h-[calc(100vh-280px)] md:h-[calc(100vh-220px)] overflow-y-auto;
    @apply bg-white rounded-lg shadow-sm mx-4;
    scroll-behavior: smooth;
    -ms-overflow-style: none;
    scrollbar-width: none;
    background: repeating-linear-gradient(
        to right,
        theme('colors.gray.50') 0px,
        theme('colors.gray.50') 1px,
        transparent 1px,
        transparent 60px
    );
}

.timeline-container::-webkit-scrollbar {
    display: none;
}

.scroll-to-now-btn {
    @apply fixed bottom-4 right-4 z-50;
    @apply md:bottom-8 md:right-8;
}

/* Responsive time markers */
.time-marker {
    @apply text-xs md:text-sm;
}

/* Event card responsive styles */
.event-card {
    @apply text-xs md:text-sm lg:text-base;
    @apply p-2 md:p-3 lg:p-4;
}

/* Transitions */
.event-enter-active,
.event-leave-active {
    transition: all 0.3s ease;
}

.event-enter-from,
.event-leave-to {
    opacity: 0;
    transform: translateY(30px);
}

@media (max-width: 640px) {
    .timeline-container {
        margin: 0.5rem;
    }

    .event-cards-container {
        padding: 0.5rem;
    }
}
</style>

