<template>
  <div class="full-calendar">
    <!-- Calendar Header -->
    <div class="calendar-header flex justify-between items-center mb-4 px-4 py-2 shadow rounded bg-white">
      <div class="flex items-center gap-2">
        <button v-for="view in views" :key="view" @click="changeView(view)"
                class="view-btn" :class="{ active: currentView === view }">
          {{ capitalize(view) }}
        </button>
      </div>

      <div class="text-xl font-semibold text-gray-800">
        {{ formattedCurrentDate }}
      </div>

      <div class="flex items-center gap-2">
        <button @click="navigateToPrev" class="nav-btn">&lt;</button>
        <button @click="navigateToToday" class="nav-btn">Today</button>
        <button @click="navigateToNext" class="nav-btn">&gt;</button>
      </div>
    </div>
<div class="p-0 relative  ">

    <!-- Loading Indicator -->
    <div v-if="isLoading" class="flex absolute top-0   justify-center items-center p-8">
      <q-spinner-dots color="primary" size="40px" />
      <span class="ml-3 text-gray-600">Loading events...</span>
    </div>

    <!-- Calendar View -->
    <component
      v-else
      :is="currentViewComponent"
      :key="viewKey"
      :date="currentDate"
      :events="formattedEvents"
      :initial-edit-mode="initialEditMode"
      @event-click="handleEventClick"
      @date-click="handleDateClick"
      @event-edit="handleEventEdit"
      @event-delete="handleEventDelete"
      @add-event="handleAddEvent"
      @edit-mode-change="handleEditModeChange"
    />
</div>
  <!-- <div class="container mx-auto">
    <LaravelRouteList />
  </div> -->
  </div>
</template>

<script setup>
import { ref, computed, watch, markRaw, onMounted } from 'vue';
import { QSpinnerDots } from 'quasar';
import MonthView from './Views/MonthView.vue';
import WeekView from './Views/WeekView.vue';
import DayView from './Views/DayView.vue';
import LaravelRouteList from '@/Components/routs/LaravelRouteList.vue'

const MonthViewComponent = markRaw(MonthView);
const WeekViewComponent = markRaw(WeekView);
const DayViewComponent = markRaw(DayView);

const props = defineProps({
  view: { type: String, default: 'month' },
  date: { type: [Date, String], default: () => new Date() },
  events: { type: Array, default: () => [] },
  initialEditMode: { type: Boolean, default: false }
});

const emit = defineEmits(['event-click', 'date-click', 'view-change', 'date-change', 'event-edit', 'event-delete', 'add-event', 'edit-mode-change']);

const isLoading = ref(true);
const currentView = ref(props.view);
const currentDate = ref(typeof props.date === 'string' ? new Date(props.date) : new Date(props.date));

const views = ['month', 'week', 'day'];

const viewKey = computed(() => `${currentView.value}-${currentDate.value.toISOString()}`);
const formattedCurrentDate = computed(() => {
  const options = { month: 'long', year: 'numeric' };
  if (currentView.value === 'day') options.day = 'numeric';
  return new Intl.DateTimeFormat('en-US', options).format(currentDate.value);
});

const formattedEvents = computed(() => {
  return props.events.map(event => {
    const date = event.date instanceof Date ? event.date.toISOString().split('T')[0] : event.date;
    return {
      id: event.id,
      title: event.title,
      description: event.description || '',
      type: event.type || '',
      is_full_day: event.allDay || event.is_full_day || false,
      date: date,
      start_time: event.start_time || null,
      end_time: event.end_time || null,
      location: event.location || '',
      color: event.color || '#3B82F6',
      originalEvent: event
    };
  });
});

const currentViewComponent = computed(() => {
  switch (currentView.value) {
    case 'week': return WeekViewComponent;
    case 'day': return DayViewComponent;
    default: return MonthViewComponent;
  }
});

const handleEventClick = event => emit('event-click', event.originalEvent || event);
const handleDateClick = date => emit('date-click', date);
const handleEventEdit = event => emit('event-edit', event.originalEvent || event);
const handleEventDelete = event => emit('event-delete', event.originalEvent || event);
const handleAddEvent = date => emit('add-event', date);
const handleEditModeChange = editMode => emit('edit-mode-change', editMode);

const changeView = view => {
  currentView.value = view;
  emit('view-change', view);
};
const navigateToToday = () => {
  currentDate.value = new Date();
  emit('date-change', currentDate.value);
};
const navigateToPrev = () => {
  const date = new Date(currentDate.value);
  if (currentView.value === 'month') date.setMonth(date.getMonth() - 1);
  if (currentView.value === 'week') date.setDate(date.getDate() - 7);
  if (currentView.value === 'day') date.setDate(date.getDate() - 1);
  currentDate.value = date;
  emit('date-change', date);
};
const navigateToNext = () => {
  const date = new Date(currentDate.value);
  if (currentView.value === 'month') date.setMonth(date.getMonth() + 1);
  if (currentView.value === 'week') date.setDate(date.getDate() + 7);
  if (currentView.value === 'day') date.setDate(date.getDate() + 1);
  currentDate.value = date;
  emit('date-change', date);
};

watch(() => props.events, () => {
  isLoading.value = !props.events.length;
}, { immediate: true });

watch(() => props.view, newView => currentView.value = newView);
watch(() => props.date, newDate => currentDate.value = typeof newDate === 'string' ? new Date(newDate) : new Date(newDate));

onMounted(() => {
  isLoading.value = !props.events.length;
  if (props.events.length) setTimeout(() => isLoading.value = false, 300);
});

const capitalize = (str) => str.charAt(0).toUpperCase() + str.slice(1);
</script>

<style scoped>
.full-calendar {
  display: flex;
  flex-direction: column;
  height: 100%;
  background-color: #f9fafb;
}
.calendar-header {
  border-bottom: 1px solid #e5e7eb;
}
.view-btn,
.nav-btn {
  padding: 0.5rem 1rem;
  border-radius: 6px;
  background-color: #f3f4f6;
  font-weight: 500;
  transition: background-color 0.2s;
}
.view-btn.active {
  background-color: #3b82f6;
  color: white;
}
.view-btn:hover,
.nav-btn:hover {
  background-color: #e0e7ff;
}
</style>
