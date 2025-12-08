<template>
  <div class="month-view">
    <!-- Edit Mode Toggle -->
    <div class="edit-mode-controls q-mb-md">
      <q-toggle
        v-model="editMode"
        label="Edit Mode"
        color="primary"
        icon="edit"
        @update:model-value="$emit('edit-mode-change', editMode)"
      />
      <q-tooltip v-if="!editMode" class="bg-grey-8">
        Enable edit mode to add or modify events
      </q-tooltip>
    </div>

    <!-- Days of week header -->
    <q-card class="month-card" flat bordered>
      <div class="days-header">
        <div v-for="day in daysOfWeek" :key="day" class="day-name">
          <q-item-label class="text-weight-medium text-grey-2">
            {{ day }}
          </q-item-label>
        </div>
      </div>

      <!-- Calendar grid -->
      <div class="month-grid">
        <div
          v-for="(day, index) in days"
          :key="index"
          class="day-cell"
          :class="{
            'current-month': day.currentMonth,
            'today': day.isToday,
            'has-events': day.events.length > 0,
            'weekend': day.isWeekend,
            'edit-mode': editMode
          }"
          @click="handleDayClick(day.date)"
        >
          <!-- Day header with number and add button -->
          <div class="day-header">
            <div class="day-number" :class="{ 'today-number': day.isToday }">
              {{ day.number }}
            </div>

            <!-- Add event button (only in edit mode) -->
            <q-btn
              v-if="editMode && day.currentMonth"
              flat
              round
              dense
              size="sm"
              icon="add"
              color="primary"
              class="add-event-btn"
              @click.stop="handleAddEvent(day.date)"
            >
              <q-tooltip class="bg-primary">
                Add event on {{ formatDateTooltip(day.date) }}
              </q-tooltip>
            </q-btn>
          </div>

          <!-- Events -->
          <div class="events-container">
            <div
              v-for="(event, eventIndex) in day.events.slice(0, 3)"
              :key="eventIndex"
              class="event-pill"
              :style="{ backgroundColor: getEventColor(event.type) }"
              @click.stop="showMoreEvents(day)"
            >
              <div class="event-content">
                <span class="event-title">{{ event.title }}</span>
              </div>
            </div>

            <!-- More events indicator or show all events button -->
            <q-chip
              v-if="day.events.length > 3"
              dense
              color="grey-4"
              text-color="grey-8"
              class="more-events-chip"
              clickable
              @click.stop="showMoreEvents(day)"
            >
              <q-icon name="more_horiz" size="xs" class="q-mr-xs" />
              +{{ day.events.length - 3 }} more
            </q-chip>
            <q-chip
              v-else-if="day.events.length > 3"
              dense
              color="primary"
              text-color="white"
              class="view-events-chip"
              clickable
              @click.stop="showMoreEvents(day)"
            >
              <q-icon name="visibility" size="xs" class="q-mr-xs" />
              View {{ day.events.length }} event{{ day.events.length > 1 ? 's' : '' }}
            </q-chip>
          </div>
        </div>
      </div>
    </q-card>

    <!-- More Events Dialog -->
    <q-dialog v-model="showMoreEventsModal" persistent>
      <q-card style="min-width: 500px; max-width: 600px">
        <q-card-section class="row items-center">
          <q-icon name="event" color="primary" size="md" class="q-mr-sm" />
          <div class="text-h6">
            Events for {{ selectedDay ? formatDateTooltip(selectedDay.date) : '' }}
          </div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>

        <q-card-section>
          <!-- Add Event Button (only in edit mode) -->
          <div v-if="editMode" class="q-mb-md">
            <q-btn
              color="primary"
              icon="add"
              label="Add New Event"
              @click="handleAddEventFromDialog"
              class="full-width"
            />
          </div>

          <!-- Events List -->
          <div v-if="selectedDay?.events && selectedDay.events.length > 0">
            <q-list separator>
              <q-item
                v-for="(event, index) in selectedDay.events"
                :key="index"
                class="event-item-dialog"
              >
                <q-item-section avatar>
                  <q-avatar
                    :color="getEventColor(event.type)"
                    text-color="white"
                    size="md"
                  >
                    <q-icon name="event" />
                  </q-avatar>
                </q-item-section>

                <q-item-section>
                  <q-item-label class="text-weight-medium">{{ event.title }}</q-item-label>
                  <q-item-label caption class="text-grey-7">
                    {{ formatEventTime(event) }}
                    <span v-if="event.location"> • {{ event.location }}</span>
                  </q-item-label>
                  <q-item-label v-if="event.description" caption class="text-grey-6 q-mt-xs">
                    {{ event.description }}
                  </q-item-label>
                  <q-chip
                    :color="getEventColor(event.type)"
                    text-color="white"
                    size="sm"
                    class="q-mt-xs"
                  >
                    {{ event.type }}
                  </q-chip>
                </q-item-section>

                <q-item-section side v-if="editMode">
                  <div class="column q-gutter-xs">
                    <q-btn
                      flat
                      round
                      dense
                      size="sm"
                      icon="edit"
                      color="primary"
                      @click="handleEventEdit(event)"
                    >
                      <q-tooltip>Edit Event</q-tooltip>
                    </q-btn>
                    <q-btn
                      flat
                      round
                      dense
                      size="sm"
                      icon="delete"
                      color="negative"
                      @click="confirmDeleteEvent(event)"
                    >
                      <q-tooltip>Delete Event</q-tooltip>
                    </q-btn>
                  </div>
                </q-item-section>
              </q-item>
            </q-list>
          </div>

          <!-- No Events Message -->
          <div v-else class="text-center q-py-lg">
            <q-icon name="event_busy" size="3rem" color="grey-4" />
            <div class="text-h6 text-grey-6 q-mt-md">No events scheduled</div>
            <div class="text-body2 text-grey-5">
              {{ editMode ? 'Click "Add New Event" to create one' : 'Enable edit mode to add events' }}
            </div>
          </div>
        </q-card-section>

        <q-card-actions align="right" class="q-pa-md">
          <q-btn flat label="Close" color="primary" v-close-popup />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- Delete Confirmation Dialog -->
    <q-dialog v-model="showDeleteConfirmation" persistent>
      <q-card>
        <q-card-section class="row items-center">
          <q-avatar icon="warning" color="negative" text-color="white" />
          <span class="q-ml-sm text-h6">Confirm Deletion</span>
        </q-card-section>

        <q-card-section>
          Are you sure you want to delete the event "{{ eventToDelete?.title }}"?
          <br>
          <span class="text-caption text-grey-6">This action cannot be undone.</span>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Cancel" color="primary" v-close-popup />
          <q-btn
            flat
            label="Delete"
            color="negative"
            @click="executeDelete"
            :loading="isDeleting"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
  date: {
    type: Date,
    required: true
  },
  events: {
    type: Array,
    default: () => []
  },
  initialEditMode: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['date-click', 'event-click', 'event-edit', 'event-delete', 'add-event', 'edit-mode-change']);

// Edit mode state
const editMode = ref(props.initialEditMode);

// Days of week
const daysOfWeek = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

// More events modal
const showMoreEventsModal = ref(false);
const selectedDay = ref(null);

// Delete confirmation modal
const showDeleteConfirmation = ref(false);
const eventToDelete = ref(null);
const isDeleting = ref(false);

// Format event time
const formatEventTime = (event) => {
  if (event.is_full_day) return 'All day';

  let startTime = '';
  let endTime = '';

  if (event.start_time) {
    if (typeof event.start_time === 'string' && event.start_time.includes('T')) {
      startTime = new Date(event.start_time).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    } else {
      startTime = event.start_time;
    }
  }

  if (event.end_time) {
    if (typeof event.end_time === 'string' && event.end_time.includes('T')) {
      endTime = new Date(event.end_time).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    } else {
      endTime = event.end_time;
    }
  }

  if (startTime && endTime) {
    return `${startTime} - ${endTime}`;
  } else if (startTime) {
    return startTime;
  }

  return '';
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

    return eventDate.getFullYear() === date.getFullYear() &&
           eventDate.getMonth() === date.getMonth() &&
           eventDate.getDate() === date.getDate();
  });
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

// Generate days for the month
const days = computed(() => {
  const daysArray = [];

  // Get first day of the month
  const firstDay = new Date(props.date.getFullYear(), props.date.getMonth(), 1);

  // Get last day of the month
  const lastDay = new Date(props.date.getFullYear(), props.date.getMonth() + 1, 0);

  // Get the day of the week for the first day (0-6, where 0 is Sunday)
  const firstDayOfWeek = firstDay.getDay();

  // Get today's date for highlighting
  const today = new Date();
  today.setHours(0, 0, 0, 0);

  // Add days from previous month to fill the first week
  const prevMonthLastDay = new Date(props.date.getFullYear(), props.date.getMonth(), 0).getDate();

  for (let i = firstDayOfWeek - 1; i >= 0; i--) {
    const day = new Date(props.date.getFullYear(), props.date.getMonth() - 1, prevMonthLastDay - i);

    daysArray.push({
      date: day,
      number: day.getDate(),
      currentMonth: false,
      isToday: day.getTime() === today.getTime(),
      isWeekend: day.getDay() === 0 || day.getDay() === 6,
      events: getEventsForDay(day)
    });
  }

  // Add days of the current month
  for (let i = 1; i <= lastDay.getDate(); i++) {
    const day = new Date(props.date.getFullYear(), props.date.getMonth(), i);

    daysArray.push({
      date: day,
      number: i,
      currentMonth: true,
      isToday: day.getTime() === today.getTime(),
      isWeekend: day.getDay() === 0 || day.getDay() === 6,
      events: getEventsForDay(day)
    });
  }

  // Add days from next month to complete the grid (6 rows x 7 columns = 42 cells)
  const remainingDays = 42 - daysArray.length;

  for (let i = 1; i <= remainingDays; i++) {
    const day = new Date(props.date.getFullYear(), props.date.getMonth() + 1, i);

    daysArray.push({
      date: day,
      number: i,
      currentMonth: false,
      isToday: day.getTime() === today.getTime(),
      isWeekend: day.getDay() === 0 || day.getDay() === 6,
      events: getEventsForDay(day)
    });
  }

  return daysArray;
});

// Show more events modal
const showMoreEvents = (day) => {
  selectedDay.value = day;
  showMoreEventsModal.value = true;
};

// Format date for tooltip
const formatDateTooltip = (date) => {
  return date.toLocaleDateString('en-US', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
};

// Handle day click
const handleDayClick = (date) => {
  if (editMode.value) {
    emit('date-click', date);
  }
};

// Handle add event
const handleAddEvent = (date) => {
  if (editMode.value) {
    emit('add-event', date);
  }
};

// Handle add event from dialog
const handleAddEventFromDialog = () => {
  if (editMode.value && selectedDay.value) {
    emit('add-event', selectedDay.value.date);
    showMoreEventsModal.value = false;
  }
};

// Handle event edit
const handleEventEdit = (event) => {
  if (editMode.value) {
    emit('event-edit', event);
    showMoreEventsModal.value = false;
  }
};

// Confirm delete event
const confirmDeleteEvent = (event) => {
  if (editMode.value) {
    eventToDelete.value = event;
    showDeleteConfirmation.value = true;
  }
};

// Execute delete
const executeDelete = async () => {
  if (eventToDelete.value) {
    isDeleting.value = true;
    try {
      emit('event-delete', eventToDelete.value);
      showDeleteConfirmation.value = false;
      showMoreEventsModal.value = false;
      eventToDelete.value = null;
    } finally {
      isDeleting.value = false;
    }
  }
};
</script>

<style scoped>
.month-view {
  display: flex;
  flex-direction: column;
  height: 100%;
  padding: 16px;
}

.edit-mode-controls {
  display: flex;
  align-items: center;
  justify-content: flex-start;
  padding: 12px 16px;
  background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
  border-radius: 12px;
  border: 1px solid #e2e8f0;
}

.month-card {
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.days-header {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.day-name {
  padding: 16px 8px;
  text-align: center;
  font-weight: 600;
  font-size: 0.875rem;
  letter-spacing: 0.025em;
}

.month-grid {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  grid-template-rows: repeat(6, 1fr);
  background-color: white;
}

.day-cell {
  border-right: 1px solid #e5e7eb;
  border-bottom: 1px solid #e5e7eb;
  padding: 8px;
  min-height: 120px;
  display: flex;
  flex-direction: column;
  position: relative;
  transition: all 0.2s ease;
}

.day-cell:hover {
  background-color: #f8fafc;
  transform: translateY(-1px);
}

.day-cell.edit-mode:hover {
  background-color: #eff6ff;
  cursor: pointer;
}

.day-cell.current-month {
  background-color: white;
}

.day-cell:not(.current-month) {
  background-color: #f9fafb;
  color: #9ca3af;
}

.day-cell.today {
  background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
  border: 2px solid #3b82f6;
}

.day-cell.weekend {
  background-color: #fefce8;
}

.day-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 8px;
}

.day-number {
  font-weight: 600;
  width: 28px;
  height: 28px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  transition: all 0.2s ease;
}

.today-number {
  background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
  color: white;
  box-shadow: 0 2px 4px rgba(59, 130, 246, 0.3);
}

.add-event-btn {
  opacity: 0;
  transition: all 0.2s ease;
}

.day-cell.edit-mode:hover .add-event-btn {
  opacity: 1;
}

.events-container {
  display: flex;
  flex-direction: column;
  gap: 4px;
  overflow: hidden;
  flex: 1;
}

.event-pill {
  font-size: 0.75rem;
  padding: 6px 8px;
  border-radius: 6px;
  color: white;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  cursor: pointer;
  position: relative;
  transition: all 0.2s ease;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.event-pill:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.15);
}

.event-content {
  display: flex;
  justify-content: center;
  align-items: center;
  width: 100%;
}

.event-title {
  flex: 1;
  overflow: hidden;
  text-overflow: ellipsis;
  text-align: center;
}

.more-events-chip,
.view-events-chip {
  margin-top: 4px;
  font-size: 0.7rem;
  border-radius: 12px;
}

.view-events-chip {
  background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%) !important;
}

.event-item-dialog {
  padding: 12px 16px;
  border-radius: 8px;
  margin-bottom: 8px;
  background-color: #f8fafc;
  transition: all 0.2s ease;
}

.event-item-dialog:hover {
  background-color: #f1f5f9;
  transform: translateX(4px);
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .month-view {
    padding: 8px;
  }

  .day-cell {
    min-height: 80px;
    padding: 4px;
  }

  .day-name {
    padding: 8px 4px;
    font-size: 0.75rem;
  }

  .event-pill {
    font-size: 0.7rem;
    padding: 4px 6px;
  }
}

/* Animation for edit mode toggle */
.edit-mode-controls .q-toggle {
  transition: all 0.3s ease;
}

.edit-mode-controls .q-toggle:hover {
  transform: scale(1.05);
}
</style>



