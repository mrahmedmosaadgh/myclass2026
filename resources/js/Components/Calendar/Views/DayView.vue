<template>
  <div class="day-view">
    <!-- Time column -->
    <div class="time-column">
      <div v-for="hour in hours" :key="hour" class="time-slot">
        {{ formatHour(hour) }}
      </div>
    </div>
    
    <!-- Events column -->
    <div class="events-column">
      <!-- All-day events -->
      <div class="all-day-section" v-if="allDayEvents.length > 0">
        <div class="all-day-label">All day</div>
        <div class="all-day-events">
          <div 
            v-for="(event, index) in allDayEvents" 
            :key="index"
            class="event-item all-day-event"
            :style="{ backgroundColor: getEventColor(event.type) }"
            @click="handleEventClick(event)"
          >
            {{ event.title }}
          </div>
        </div>
      </div>
      
      <!-- Time slots -->
      <div 
        v-for="hour in hours" 
        :key="hour" 
        class="time-slot"
        @click="handleTimeSlotClick(hour)"
      >
        <!-- Events in this time slot -->
        <div 
          v-for="(event, index) in getEventsForHour(hour)" 
          :key="index"
          class="event-item"
          :style="{ 
            backgroundColor: getEventColor(event.type),
            height: `${calculateEventHeight(event)}px`
          }"
          @click.stop="handleEventClick(event)"
        >
          <div class="event-title">{{ event.title }}</div>
          <div class="event-time">{{ formatEventTime(event) }}</div>
          <div v-if="event.location" class="event-location">
            <i class="material-icons location-icon">place</i> {{ event.location }}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

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

const emit = defineEmits(['date-click', 'event-click']);

// Hours for the day (5 AM to 9 PM)
const hours = Array.from({ length: 17 }, (_, i) => i + 5);

// Format hour for display
const formatHour = (hour) => {
  if (hour === 0) return '12 AM';
  if (hour === 12) return '12 PM';
  return hour < 12 ? `${hour} AM` : `${hour - 12} PM`;
};

// Format event time
const formatEventTime = (event) => {
  if (event.is_full_day) return 'All day';
  
  let startTime = '';
  let endTime = '';
  
  if (event.start_time) {
    if (typeof event.start_time === 'string' && event.start_time.includes('T')) {
      // Extract time part from ISO format
      startTime = event.start_time.split('T')[1].substring(0, 5);
    } else {
      startTime = event.start_time;
    }
  }
  
  if (event.end_time) {
    if (typeof event.end_time === 'string' && event.end_time.includes('T')) {
      // Extract time part from ISO format
      endTime = event.end_time.split('T')[1].substring(0, 5);
    } else {
      endTime = event.end_time;
    }
  }
  
  // Convert to 12-hour format
  const formatTime = (timeStr) => {
    if (!timeStr) return '';
    
    const [hours, minutes] = timeStr.split(':').map(Number);
    const period = hours >= 12 ? 'PM' : 'AM';
    const hour12 = hours % 12 || 12;
    
    return `${hour12}:${minutes.toString().padStart(2, '0')} ${period}`;
  };
  
  if (startTime && endTime) {
    return `${formatTime(startTime)} - ${formatTime(endTime)}`;
  } else if (startTime) {
    return formatTime(startTime);
  }
  
  return '';
};

// Calculate event height based on duration
const calculateEventHeight = (event) => {
  if (event.is_full_day) return 40; // Default height for all-day events
  
  let startHour = 8; // Default start hour
  let endHour = 9; // Default end hour
  
  if (event.start_time) {
    if (typeof event.start_time === 'string') {
      const timeStr = event.start_time.includes('T') 
        ? event.start_time.split('T')[1].substring(0, 5) 
        : event.start_time;
      
      const [hours, minutes] = timeStr.split(':').map(Number);
      startHour = hours + (minutes / 60);
    }
  }
  
  if (event.end_time) {
    if (typeof event.end_time === 'string') {
      const timeStr = event.end_time.includes('T') 
        ? event.end_time.split('T')[1].substring(0, 5) 
        : event.end_time;
      
      const [hours, minutes] = timeStr.split(':').map(Number);
      endHour = hours + (minutes / 60);
    }
  }
  
  // Calculate duration in hours
  const duration = endHour - startHour;
  
  // Convert duration to pixels (1 hour = 60px)
  return Math.max(40, duration * 60);
};

// Get color for event type
const getEventColor = (type) => {
  const colors = {
    holiday: '#ef4444', // red
    meeting: '#3b82f6', // blue
    exam: '#8b5cf6', // purple
    activity: '#10b981', // green
    other: '#6b7280' // gray
  };
  
  return colors[type] || colors.other;
};

// Get events for the selected day
const dayEvents = computed(() => {
  if (!props.events || !Array.isArray(props.events)) {
    return [];
  }
  
  return props.events.filter(event => {
    // Get the date from calendar.date, date property, or from start_time
    let eventDate;
    
    if (event.calendar && event.calendar.date) {
      // Extract date from calendar.date
      eventDate = new Date(event.calendar.date);
    } else if (event.date) {
      eventDate = new Date(event.date);
    } else if (event.start_time && typeof event.start_time === 'string' && event.start_time.includes('T')) {
      // Extract date from ISO format start_time
      eventDate = new Date(event.start_time.split('T')[0]);
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
    
    return eventDate.getFullYear() === props.date.getFullYear() &&
           eventDate.getMonth() === props.date.getMonth() &&
           eventDate.getDate() === props.date.getDate();
  });
});

// All-day events
const allDayEvents = computed(() => {
  return dayEvents.value.filter(event => event.is_full_day);
});

// Get events for a specific hour
const getEventsForHour = (hour) => {
  return dayEvents.value.filter(event => {
    if (event.is_full_day) {
      // All-day events are displayed separately
      return false;
    }
    
    let startHour = 8; // Default start hour
    
    if (event.start_time) {
      if (typeof event.start_time === 'string') {
        const timeStr = event.start_time.includes('T') 
          ? event.start_time.split('T')[1].substring(0, 5) 
          : event.start_time;
        
        const [hours] = timeStr.split(':').map(Number);
        startHour = hours;
      }
    }
    
    return startHour === hour;
  });
};

// Handle time slot click
const handleTimeSlotClick = (hour) => {
  // Create a new date with the selected hour
  const selectedDate = new Date(props.date);
  selectedDate.setHours(hour, 0, 0, 0);
  
  emit('date-click', selectedDate);
};

// Handle event click
const handleEventClick = (event) => {
  emit('event-click', event);
};
</script>

<style scoped>
.day-view {
  display: flex;
  height: 100%;
  min-height: 600px;
  overflow-y: auto;
}

.time-column {
  width: 60px;
  flex-shrink: 0;
  border-right: 1px solid #e5e7eb;
}

.events-column {
  flex: 1;
  min-width: 300px;
}

.all-day-section {
  display: flex;
  border-bottom: 1px solid #e5e7eb;
  min-height: 60px;
  padding: 8px 0;
}

.all-day-label {
  width: 60px;
  padding: 8px;
  text-align: center;
  color: #6b7280;
  font-size: 0.75rem;
  font-weight: 500;
}

.all-day-events {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 4px;
  padding: 0 8px;
}

.time-slot {
  height: 60px;
  border-bottom: 1px solid #e5e7eb;
  padding: 4px;
  position: relative;
}

.time-column .time-slot {
  text-align: center;
  color: #6b7280;
  font-size: 0.75rem;
  padding-top: 8px;
}

.event-item {
  background-color: #3b82f6;
  color: white;
  border-radius: 4px;
  padding: 4px 8px;
  font-size: 0.875rem;
  cursor: pointer;
  position: absolute;
  width: calc(100% - 16px);
  overflow: hidden;
}

.all-day-event {
  position: relative;
  margin-bottom: 4px;
  height: auto;
  min-height: 28px;
}

.event-title {
  font-weight: 500;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.event-time {
  font-size: 0.75rem;
  opacity: 0.9;
}

.event-location {
  font-size: 0.75rem;
  display: flex;
  align-items: center;
  margin-top: 2px;
}

.location-icon {
  font-size: 12px;
  margin-right: 2px;
}
</style>






