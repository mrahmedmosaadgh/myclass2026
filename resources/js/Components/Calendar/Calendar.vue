<script setup>
import { ref, computed } from 'vue';
import CalendarHeader from './CalendarHeader.vue';
import MonthView from './Views/MonthView.vue';
import WeekView from './Views/WeekView.vue';
import DayView from './Views/DayView.vue';
import EventModal from './EventModal.vue';

const props = defineProps({
  events: {
    type: Array,
    default: () => []
  },
  eventTypes: {
    type: Array,
    default: () => []
  },
  isLoading: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['event-save', 'event-delete']);

// Current date and view
const currentDate = ref(new Date());
const currentView = ref('month');

// Event modal state
const showEventModal = ref(false);
const selectedEvent = ref({});
const isNewEvent = ref(true);

// Formatted date for header
const formattedDate = computed(() => {
  const options = { year: 'numeric', month: 'long' };
  
  if (currentView.value === 'week') {
    // For week view, show the date range
    const startOfWeek = new Date(currentDate.value);
    const dayOfWeek = startOfWeek.getDay();
    startOfWeek.setDate(startOfWeek.getDate() - dayOfWeek);
    
    const endOfWeek = new Date(startOfWeek);
    endOfWeek.setDate(endOfWeek.getDate() + 6);
    
    // Format the date range
    if (startOfWeek.getMonth() === endOfWeek.getMonth()) {
      // Same month
      return `${startOfWeek.getDate()} - ${endOfWeek.getDate()} ${endOfWeek.toLocaleDateString(undefined, { month: 'long', year: 'numeric' })}`;
    } else if (startOfWeek.getFullYear() === endOfWeek.getFullYear()) {
      // Different months, same year
      return `${startOfWeek.getDate()} ${startOfWeek.toLocaleDateString(undefined, { month: 'long' })} - ${endOfWeek.getDate()} ${endOfWeek.toLocaleDateString(undefined, { month: 'long', year: 'numeric' })}`;
    } else {
      // Different years
      return `${startOfWeek.toLocaleDateString(undefined, { day: 'numeric', month: 'long', year: 'numeric' })} - ${endOfWeek.toLocaleDateString(undefined, { day: 'numeric', month: 'long', year: 'numeric' })}`;
    }
  } else if (currentView.value === 'day') {
    // For day view, show the full date
    return currentDate.value.toLocaleDateString(undefined, { 
      weekday: 'long',
      day: 'numeric',
      month: 'long',
      year: 'numeric'
    });
  } else {
    // For month view, show month and year
    return currentDate.value.toLocaleDateString(undefined, options);
  }
});

// Process events to ensure they have all required properties
const processedEvents = computed(() => {
  if (!props.events || !Array.isArray(props.events)) {
    return [];
  }
  
  return props.events.map(event => {
    // Ensure event has all required properties
    return {
      id: event.id,
      title: event.title || 'Untitled Event',
      description: event.description || '',
      date: event.date || new Date().toISOString().split('T')[0],
      is_full_day: event.is_full_day !== undefined ? event.is_full_day : true,
      start_time: event.start_time || '',
      end_time: event.end_time || '',
      location: event.location || '',
      type: event.type || 'other',
      color: event.color || '#6b7280',
      calendar: event.calendar || null
    };
  });
});

// Navigation methods
const goToPrevious = () => {
  const newDate = new Date(currentDate.value);
  
  if (currentView.value === 'month') {
    newDate.setMonth(newDate.getMonth() - 1);
  } else if (currentView.value === 'week') {
    newDate.setDate(newDate.getDate() - 7);
  } else if (currentView.value === 'day') {
    newDate.setDate(newDate.getDate() - 1);
  }
  
  currentDate.value = newDate;
};

const goToNext = () => {
  const newDate = new Date(currentDate.value);
  
  if (currentView.value === 'month') {
    newDate.setMonth(newDate.getMonth() + 1);
  } else if (currentView.value === 'week') {
    newDate.setDate(newDate.getDate() + 7);
  } else if (currentView.value === 'day') {
    newDate.setDate(newDate.getDate() + 1);
  }
  
  currentDate.value = newDate;
};

const goToToday = () => {
  currentDate.value = new Date();
};

const changeView = (view) => {
  currentView.value = view;
};

// Event handlers
const handleDateClick = (date) => {
  // Create a new event on the selected date
  selectedEvent.value = {
    title: '',
    description: '',
    date: date.toISOString().split('T')[0],
    is_full_day: true,
    start_time: '08:00',
    end_time: '09:00',
    location: '',
    type: 'other'
  };
  
  isNewEvent.value = true;
  showEventModal.value = true;
};

const handleEventClick = (event) => {
  // Edit the selected event
  selectedEvent.value = { ...event };
  isNewEvent.value = false;
  showEventModal.value = true;
};

const handleEventSave = (eventData) => {
  emit('event-save', eventData);
  showEventModal.value = false;
};

const handleEventDelete = (eventId) => {
  emit('event-delete', eventId);
  showEventModal.value = false;
};
</script>

<template>
  <div class="calendar-container">
    <!-- Calendar header -->
    <CalendarHeader
      :formatted-date="formattedDate"
      :current-view="currentView"
      @previous="goToPrevious"
      @next="goToNext"
      @today="goToToday"
      @view-change="changeView"
      @add-event="handleDateClick(new Date())"
    />
    
    <!-- Loading indicator -->
    <div v-if="isLoading" class="loading-container">
      <q-spinner color="primary" size="3em" />
      <div class="loading-text">Loading calendar events...</div>
    </div>
    
    <!-- Calendar views -->
    <div v-else class="calendar-view-container">
      <!-- Month view -->
      <MonthView
        v-if="currentView === 'month'"
        :date="currentDate"
        :events="processedEvents"
        @date-click="handleDateClick"
        @event-click="handleEventClick"
      />
      
      <!-- Week view -->
      <WeekView
        v-else-if="currentView === 'week'"
        :date="currentDate"
        :events="processedEvents"
        @date-click="handleDateClick"
        @event-click="handleEventClick"
      />
      
      <!-- Day view -->
      <DayView
        v-else-if="currentView === 'day'"
        :date="currentDate"
        :events="processedEvents"
        @date-click="handleDateClick"
        @event-click="handleEventClick"
      />
    </div>
    
    <!-- Event modal -->
    <EventModal
      v-model="showEventModal"
      :event="selectedEvent"
      :is-new-event="isNewEvent"
      :event-types="props.eventTypes"
      @save="handleEventSave"
      @delete="handleEventDelete"
    />
  </div>
</template>

<style scoped>
.calendar-container {
  display: flex;
  flex-direction: column;
  height: 100%;
  background-color: white;
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.calendar-view-container {
  flex: 1;
  overflow: auto;
}

.loading-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 300px;
  color: #6b7280;
}

.loading-spinner {
  border: 4px solid #f3f4f6;
  border-top: 4px solid #3b82f6;
  border-radius: 50%;
  width: 30px;
  height: 30px;
  animation: spin 1s linear infinite;
  margin-bottom: 10px;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}
</style>

