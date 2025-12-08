<template>
  <div class="month-view-example">
    <h2 class="text-h4 q-mb-md">Enhanced Month View Example</h2>

    <!-- Month View Component -->
    <MonthView
      :date="currentDate"
      :events="sampleEvents"
      :initial-edit-mode="true"
      @date-click="handleDateClick"
      @event-edit="handleEventEdit"
      @event-delete="handleEventDelete"
      @add-event="handleAddEvent"
      @edit-mode-change="handleEditModeChange"
    />

    <!-- Event Details -->
    <q-card v-if="selectedEvent" class="q-mt-md">
      <q-card-section>
        <div class="text-h6">Selected Event</div>
        <div class="text-subtitle2">{{ selectedEvent.title }}</div>
        <div class="text-body2">{{ selectedEvent.description }}</div>
        <div class="text-caption">Type: {{ selectedEvent.type }}</div>
        <div class="text-caption">Date: {{ selectedEvent.date }}</div>
      </q-card-section>
    </q-card>

    <!-- Action Log -->
    <q-card class="q-mt-md">
      <q-card-section>
        <div class="text-h6">Action Log</div>
        <q-list>
          <q-item v-for="(action, index) in actionLog" :key="index">
            <q-item-section>
              <q-item-label>{{ action.action }}</q-item-label>
              <q-item-label caption>{{ action.timestamp }}</q-item-label>
            </q-item-section>
          </q-item>
        </q-list>
      </q-card-section>
    </q-card>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import MonthView from '../Views/MonthView.vue';

// Current date
const currentDate = ref(new Date());

// Selected event
const selectedEvent = ref(null);

// Action log
const actionLog = ref([]);

// Sample events
const sampleEvents = ref([
  {
    id: 1,
    title: 'Team Meeting',
    description: 'Weekly team sync',
    type: 'meeting',
    date: new Date().toISOString().split('T')[0],
    is_full_day: false,
    start_time: '09:00',
    end_time: '10:00',
    location: 'Conference Room A'
  },
  {
    id: 2,
    title: 'Project Deadline',
    description: 'Final submission',
    type: 'exam',
    date: new Date(Date.now() + 86400000).toISOString().split('T')[0], // Tomorrow
    is_full_day: true,
    location: 'Office'
  },
  {
    id: 3,
    title: 'Holiday',
    description: 'National Holiday',
    type: 'holiday',
    date: new Date(Date.now() + 172800000).toISOString().split('T')[0], // Day after tomorrow
    is_full_day: true
  },
  {
    id: 4,
    title: 'Workshop',
    description: 'Vue.js Workshop',
    type: 'activity',
    date: new Date(Date.now() + 259200000).toISOString().split('T')[0], // 3 days from now
    is_full_day: false,
    start_time: '14:00',
    end_time: '17:00',
    location: 'Training Room'
  }
]);

// Event handlers
const handleDateClick = (date) => {
  logAction(`Date clicked: ${date.toDateString()}`);
  selectedEvent.value = null;
};

const handleEventEdit = (event) => {
  logAction(`Edit event: ${event.title}`);
  selectedEvent.value = event;
  // Here you would typically open an edit modal
  // For demo purposes, we'll just update the title
  event.title = event.title + ' (Edited)';
};

const handleEventDelete = (event) => {
  logAction(`Delete event: ${event.title}`);
  // Remove event from the list
  const index = sampleEvents.value.findIndex(e => e.id === event.id);
  if (index !== -1) {
    sampleEvents.value.splice(index, 1);
  }
  selectedEvent.value = null;
};

const handleAddEvent = (date) => {
  logAction(`Add event on: ${date.toDateString()}`);
  // Here you would typically open an add event modal
  // For demo purposes, we'll create a simple event
  const newEvent = {
    id: Date.now(),
    title: `New Event ${sampleEvents.value.length + 1}`,
    description: 'Auto-generated event for demo',
    type: 'other',
    date: date.toISOString().split('T')[0],
    is_full_day: true,
    location: 'Demo Location'
  };
  sampleEvents.value.push(newEvent);
  selectedEvent.value = newEvent;
};

const handleEditModeChange = (editMode) => {
  logAction(`Edit mode ${editMode ? 'enabled' : 'disabled'}`);
};

// Helper function to log actions
const logAction = (action) => {
  actionLog.value.unshift({
    action,
    timestamp: new Date().toLocaleTimeString()
  });

  // Keep only last 10 actions
  if (actionLog.value.length > 10) {
    actionLog.value = actionLog.value.slice(0, 10);
  }
};
</script>

<style scoped>
.month-view-example {
  padding: 20px;
  max-width: 1200px;
  margin: 0 auto;
}
</style>
