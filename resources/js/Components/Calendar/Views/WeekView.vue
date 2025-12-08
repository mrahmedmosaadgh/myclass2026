<template>
    <div class="week-view flex flex-col h-full">
        <!-- Week header -->
        <div class="week-header grid grid-cols-8 border-b">
            <!-- Empty cell for time column -->
            <div class="p-2 border-r"></div>
            
            <!-- Day headers -->
            <div 
                v-for="day in weekDays" 
                :key="day.date.toISOString()"
                class="p-2 text-center border-r"
                :class="{ 'bg-blue-50': day.isToday }"
            >
                <div class="font-medium">{{ formatDayName(day.date) }}</div>
                <div :class="{ 'font-bold text-blue-600': day.isToday }">
                    {{ day.date.getDate() }}
                </div>
            </div>
        </div>
        
        <!-- Time grid -->
        <div class="time-grid flex-grow overflow-y-auto">
            <div class="relative">
                <!-- Hours -->
                <div 
                    v-for="hour in hours" 
                    :key="hour"
                    class="hour-row grid grid-cols-8 border-b"
                    :class="{ 'bg-gray-50': hour % 2 === 0 }"
                >
                    <!-- Hour label -->
                    <div class="hour-label p-1 text-xs text-gray-500 text-right pr-2 border-r">
                        {{ formatHour(hour) }}
                    </div>
                    
                    <!-- Day columns -->
                    <div 
                        v-for="day in weekDays" 
                        :key="`${day.date.toISOString()}-${hour}`"
                        class="day-hour-cell relative h-[60px] border-r"
                        :class="{ 'bg-blue-50': day.isToday }"
                        @click="$emit('date-click', getDateWithHour(day.date, hour))"
                    ></div>
                </div>
                
                <!-- Current time indicator -->
                <div 
                    v-for="day in weekDays.filter(d => d.isToday)" 
                    :key="`time-indicator-${day.date.toISOString()}`"
                    class="current-time-indicator absolute border-t border-red-500 z-10"
                    :style="{ 
                        top: `${currentTimePosition}px`,
                        left: `calc(${weekDays.findIndex(d => d.isToday) + 1} * 12.5% + 1px)`,
                        width: 'calc(12.5% - 2px)'
                    }"
                >
                    <div class="w-2 h-2 rounded-full bg-red-500 -mt-1"></div>
                </div>
                
                <!-- Events -->
                <div 
                    v-for="(day, dayIndex) in weekDays" 
                    :key="`events-${day.date.toISOString()}`"
                    class="day-events absolute top-0 bottom-0"
                    :style="{ 
                        left: `calc(${dayIndex + 1} * 12.5% + 1px)`,
                        width: 'calc(12.5% - 2px)'
                    }"
                >
                    <!-- All-day events -->
                    <div 
                        v-for="(event, eventIndex) in getEventsForDay(day.date).filter(e => e.is_full_day)"
                        :key="`all-day-${eventIndex}`"
                        class="all-day-event absolute p-1 text-xs rounded cursor-pointer truncate"
                        :style="{
                            top: '0',
                            left: '2px',
                            right: '2px',
                            backgroundColor: event.color || '#3B82F6',
                            color: '#FFFFFF',
                            zIndex: 5
                        }"
                        @click.stop="$emit('event-click', event)"
                    >
                        {{ event.title }}
                    </div>
                    
                    <!-- Timed events -->
                    <div 
                        v-for="(event, eventIndex) in getEventsForDay(day.date).filter(e => !e.is_full_day)"
                        :key="`timed-${eventIndex}`"
                        class="timed-event absolute p-1 text-xs rounded cursor-pointer overflow-hidden"
                        :style="{
                            top: `${calculateEventTop(event)}px`,
                            height: `${calculateEventHeight(event)}px`,
                            left: '2px',
                            right: '2px',
                            backgroundColor: event.color || '#3B82F6',
                            color: '#FFFFFF',
                            zIndex: 5
                        }"
                        @click.stop="$emit('event-click', event)"
                    >
                        <div class="font-medium truncate">{{ event.title }}</div>
                        <div class="text-xs truncate">
                            {{ formatTime(event.start_time) }} - {{ formatTime(event.end_time) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, ref, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    date: {
        type: Date,
        required: true
    },
    events: {
        type: Array,
        default: () => []
    }
});

// Hours to display (24-hour format)
const hours = Array.from({ length: 24 }, (_, i) => i);

// Current time position for the time indicator
const currentTimePosition = ref(0);
let timeUpdateInterval = null;

// Generate days for the week
const weekDays = computed(() => {
    const days = [];
    const startOfWeek = new Date(props.date);
    
    // Adjust to the start of the week (Sunday)
    startOfWeek.setDate(startOfWeek.getDate() - startOfWeek.getDay());
    
    // Get today's date for highlighting
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    
    // Generate 7 days for the week
    for (let i = 0; i < 7; i++) {
        const currentDate = new Date(startOfWeek);
        currentDate.setDate(currentDate.getDate() + i);
        
        const isToday = currentDate.getTime() === today.getTime();
        
        // Get events for this specific day
        const dayEvents = getEventsForDay(currentDate);
        
        days.push({
            date: currentDate,
            isToday,
            events: dayEvents,
            dayName: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'][currentDate.getDay()],
            dayNumber: currentDate.getDate()
        });
    }
    
    return days;
});

// Format day name
const formatDayName = (date) => {
    return new Intl.DateTimeFormat('en-US', { weekday: 'short' }).format(date);
};

// Format hour for display
const formatHour = (hour) => {
    const ampm = hour >= 12 ? 'PM' : 'AM';
    const hour12 = hour % 12 || 12;
    return `${hour12} ${ampm}`;
};

// Format time for display
const formatTime = (timeStr) => {
    if (!timeStr) return '';
    
    // Convert 24-hour format to 12-hour format
    const [hours, minutes] = timeStr.split(':');
    const hour = parseInt(hours, 10);
    const ampm = hour >= 12 ? 'PM' : 'AM';
    const hour12 = hour % 12 || 12;
    
    return `${hour12}:${minutes} ${ampm}`;
};

// Get events for a specific day
const getEventsForDay = (date) => {
    if (!props.events || !Array.isArray(props.events)) {
        return [];
    }
    
    return props.events.filter(event => {
        // Get the date from calendar.date, date property, or from start_time
        let eventDate;
        
        if (event.calendar && event.calendar.date) {
            // Extract date from calendar.date
            eventDate = new Date(event.calendar.date);
        } else if (event.start_time && event.start_time.includes('T')) {
            // Extract date from ISO format start_time
            eventDate = new Date(event.start_time.split('T')[0]);
        } else if (event.date) {
            eventDate = new Date(event.date);
        } else if (event.start_time) {
            // Try to parse start_time as a date if it doesn't include 'T'
            eventDate = new Date(event.start_time);
        } else {
            return false;
        }
        
        // Check if the date is valid
        if (isNaN(eventDate.getTime())) {
            console.warn('Invalid date for event:', event);
            return false;
        }
        
        return eventDate.getFullYear() === date.getFullYear() &&
               eventDate.getMonth() === date.getMonth() &&
               eventDate.getDate() === date.getDate();
    });
};

// Calculate event position and height
const calculateEventTop = (event) => {
    if (!event.start_time) return 0;
    
    // Parse hours and minutes
    let hours = 0;
    let minutes = 0;
    
    if (typeof event.start_time === 'string') {
        // Handle both HH:MM format and ISO format
        if (event.start_time.includes('T')) {
            const timePart = event.start_time.split('T')[1];
            [hours, minutes] = timePart.substring(0, 5).split(':').map(Number);
        } else {
            [hours, minutes] = event.start_time.split(':').map(Number);
        }
    }
    
    // Calculate position based on time (1 hour = 60px)
    return (hours * 60 + minutes) * (60 / 15); // 60px per hour, 15 minutes per step
};

const calculateEventHeight = (event) => {
    if (!event.start_time || !event.end_time) return 60; // Default 1 hour
    
    let startHours = 0;
    let startMinutes = 0;
    let endHours = 0;
    let endMinutes = 0;
    
    // Parse start time
    if (typeof event.start_time === 'string') {
        if (event.start_time.includes('T')) {
            const timePart = event.start_time.split('T')[1];
            [startHours, startMinutes] = timePart.substring(0, 5).split(':').map(Number);
        } else {
            [startHours, startMinutes] = event.start_time.split(':').map(Number);
        }
    }
    
    // Parse end time
    if (typeof event.end_time === 'string') {
        if (event.end_time.includes('T')) {
            const timePart = event.end_time.split('T')[1];
            [endHours, endMinutes] = timePart.substring(0, 5).split(':').map(Number);
        } else {
            [endHours, endMinutes] = event.end_time.split(':').map(Number);
        }
    }
    
    // Calculate duration in minutes
    const startInMinutes = startHours * 60 + startMinutes;
    const endInMinutes = endHours * 60 + endMinutes;
    const durationInMinutes = endInMinutes - startInMinutes;
    
    // Convert to pixels (60px per hour)
    return durationInMinutes * (60 / 60);
};

// Get date object with specific hour
const getDateWithHour = (date, hour) => {
    const newDate = new Date(date);
    newDate.setHours(hour, 0, 0, 0);
    return newDate;
};

// Get event style
const getEventStyle = (event, dayDate) => {
    const start = new Date(event.date);
    const end = new Date(event.end_date || event.date);
    const dayStart = new Date(dayDate);
    const dayEnd = new Date(dayDate);
    dayEnd.setDate(dayEnd.getDate() + 1);

    if (event.is_full_day) {
        return {
            top: '0',
            height: '100%',
            width: '100%',
            left: '0',
            right: '0'
        };
    }

    const startTop = calculateEventTop(event);
    const endTop = calculateEventTop({ start_time: event.end_time });
    const eventHeight = calculateEventHeight(event);

    const isMultiDay = end > dayEnd;
    const isStartDay = start < dayStart;
    const isEndDay = end > dayEnd;

    const styles = {
        top: `${startTop}px`,
        height: `${eventHeight}px`,
        width: '100%',
        left: '0',
        right: '0'
    };

    if (isMultiDay) {
        styles.height = '100%';
        styles.top = '0';
    } else if (isStartDay) {
        styles.left = '0';
    } else if (isEndDay) {
        styles.right = '0';
    }

    return styles;
};

// Update current time position
const updateCurrentTimePosition = () => {
    const now = new Date();
    const currentHour = now.getHours();
    const currentMinute = now.getMinutes();
    currentTimePosition.value = (currentHour * 60 + currentMinute) * 2;
};

onMounted(() => {
    updateCurrentTimePosition();
    timeUpdateInterval = setInterval(updateCurrentTimePosition, 60000); // Update every minute
});

onUnmounted(() => {
    clearInterval(timeUpdateInterval);
});
</script>

<style scoped>
.week-view {
    height: 100%;
    display: flex;
    flex-direction: column;
}

.time-grid {
    flex: 1;
    overflow-y: auto;
}

.time-gutter {
    width: 60px;
}

.time-labels {
    width: 60px;
}

.time-label {
    height: 60px;
    padding: 4px;
    text-align: right;
    font-size: 0.75rem;
    color: #6b7280;
    border-right: 1px solid #e5e7eb;
    border-bottom: 1px solid #e5e7eb;
}

.day-column {
    position: relative;
    border-right: 1px solid #e5e7eb;
}

.hour-cell {
    height: 60px;
    border-bottom: 1px solid #e5e7eb;
}

.event-item {
    position: absolute;
    left: 4px;
    right: 4px;
    padding: 4px;
    border-radius: 4px;
    color: white;
    font-size: 0.75rem;
    overflow: hidden;
    cursor: pointer;
    z-index: 1;
}

.event-title {
    font-weight: 500;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.event-time {
    font-size: 0.7rem;
    opacity: 0.9;
}

.current-time-indicator {
    position: absolute;
    left: 0;
    width: 100%;
    pointer-events: none;
}

.current-time-dot {
    position: absolute;
    left: 50%;
    transform: translateX(-50%);
    width: 10px;
    height: 10px;
    background-color: #3B82F6;
    border-radius: 50%;
}

.current-time-line {
    position: absolute;
    left: 50%;
    transform: translateX(-50%);
    height: 100%;
    width: 2px;
    background-color: #3B82F6;
}

.all-day-event {
    position: absolute;
    left: 0;
    right: 0;
    top: 0;
    height: 100%;
    background-color: #3B82F6;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
    border-radius: 4px;
}
</style>






