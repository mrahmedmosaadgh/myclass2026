<template>
  <q-card style="width: 500px; max-width: 80vw;">
    <q-card-section>
      <div class="text-h6">{{ formData.id ? 'Edit Event' : 'New Event' }}</div>
    </q-card-section>

    <q-card-section>
      <q-form @submit.prevent="saveEvent" class="q-gutter-md">
        <q-input
          v-model="formData.title"
          label="Title *"
          :rules="[val => !!val || 'Title is required']"
          autofocus
        />

        <q-input
          v-model="formData.description"
          label="Description"
          type="textarea"
          rows="3"
        />
        
        <div class="row q-col-gutter-sm">
          <div class="col-12 col-sm-6">
            <q-input
              v-model="formData.date"
              label="Date *"
              type="date"
              :rules="[val => !!val || 'Date is required']"
            />
          </div>
          
          <div class="col-12 col-sm-6">
            <q-toggle
              v-model="formData.is_full_day"
              label="All day"
            />
          </div>
        </div>
        
        <div v-if="!formData.is_full_day" class="row q-col-gutter-sm">
          <div class="col-12 col-sm-6">
            <q-input
              v-model="formData.start_time"
              label="Start time"
              type="time"
            />
          </div>
          
          <div class="col-12 col-sm-6">
            <q-input
              v-model="formData.end_time"
              label="End time"
              type="time"
            />
          </div>
        </div>
        
        <q-input
          v-model="formData.location"
          label="Location"
        />
        
        <q-select
          v-model="formData.type"
          :options="eventTypeOptions"
          label="Event Type *"
          :rules="[val => !!val || 'Event type is required']"
        >
          <template v-slot:option="scope">
            <q-item v-bind="scope.itemProps">
              <q-item-section avatar>
                <q-icon :name="scope.opt.icon" :color="scope.opt.color" />
              </q-item-section>
              <q-item-section>
                <q-item-label>{{ scope.opt.label }}</q-item-label>
              </q-item-section>
            </q-item>
          </template>
        </q-select>

        <q-toggle
          v-model="formData.affects_all_schedules"
          label="Affects all schedules"
        />
      </q-form>
    </q-card-section>

    <q-card-actions align="right">
      <q-btn flat label="Cancel" color="grey" @click="$emit('close')" :disabled="isSubmitting" />
      <q-btn flat label="Delete" color="negative" @click="confirmDelete" v-if="formData.id" :disabled="isSubmitting" />
      <q-btn flat label="Save" color="primary" @click="saveEvent" :loading="isSubmitting" />
    </q-card-actions>
  </q-card>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useQuasar } from 'quasar';

const $q = useQuasar();

const props = defineProps({
  event: {
    type: Object,
    required: true
  },
  eventTypes: {
    type: Array,
    default: () => []
  },
  isSubmitting: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['save', 'delete']);

// Form data with default values
const formData = ref({
  id: null,
  title: '',
  description: '',
  date: new Date().toISOString().split('T')[0],
  is_full_day: true,
  start_time: '08:00',
  end_time: '09:00',
  location: '',
  type: 'other',
  affects_all_schedules: false,
  status: 'active'
});

// Event type options
const eventTypeOptions = computed(() => {
  if (props.eventTypes && props.eventTypes.length > 0) {
    return props.eventTypes;
  }
  
  // Default event types if none provided
  return [
    { value: 'holiday', label: 'Holiday' },
    { value: 'meeting', label: 'Meeting' },
    { value: 'exam', label: 'Exam' },
    { value: 'activity', label: 'Activity' },
    { value: 'other', label: 'Other' }
  ];
});

onMounted(() => {
  // Initialize form with event data
  Object.keys(formData.value).forEach(key => {
    if (props.event[key] !== undefined) {
      if (key === 'type') {
        // Ensure type is a string
        formData.value[key] = String(props.event[key]);
      } else if (key === 'date') {
        if (props.event.calendar && props.event.calendar.date) {
          // Extract date from calendar.date
          formData.value[key] = new Date(props.event.calendar.date).toISOString().split('T')[0];
        } else if (props.event.date) {
          // Extract date part if it's in ISO format
          if (typeof props.event.date === 'string' && props.event.date.includes('T')) {
            formData.value[key] = props.event.date.split('T')[0];
          } else {
            formData.value[key] = props.event.date;
          }
        } else if (props.event.start_time && typeof props.event.start_time === 'string' && props.event.start_time.includes('T')) {
          // Extract date from start_time if date is not provided
          formData.value[key] = props.event.start_time.split('T')[0];
        } else if (props.event.start_time) {
          // Try to parse start_time as a date
          const startDate = new Date(props.event.start_time);
          if (!isNaN(startDate.getTime())) {
            formData.value[key] = startDate.toISOString().split('T')[0];
          } else {
            // Default to today if we can't parse a date
            formData.value[key] = new Date().toISOString().split('T')[0];
          }
        } else {
          // Default to today if no date or start_time
          formData.value[key] = new Date().toISOString().split('T')[0];
        }
      } else if (key === 'start_time' && typeof props.event.start_time === 'string' && props.event.start_time.includes('T')) {
        // Extract time part from ISO format
        formData.value[key] = props.event.start_time.split('T')[1].substring(0, 5);
      } else if (key === 'end_time' && typeof props.event.end_time === 'string' && props.event.end_time.includes('T')) {
        // Extract time part from ISO format
        formData.value[key] = props.event.end_time.split('T')[1].substring(0, 5);
      } else {
        formData.value[key] = props.event[key];
      }
    }
  });
  
  // Ensure we always have a date
  if (!formData.value.date) {
    // Check if the event has a calendar with a date
    if (props.event.calendar && props.event.calendar.date) {
      formData.value.date = new Date(props.event.calendar.date).toISOString().split('T')[0];
    } else {
      formData.value.date = new Date().toISOString().split('T')[0];
    }
  }
  
  // Set ID from event
  formData.value.id = props.event.id || null;
});

const saveEvent = () => {
  // Validate required fields
  if (!formData.value.title) {
    $q.notify({
      color: 'negative',
      message: 'Please enter a title',
      icon: 'error'
    });
    return;
  }
  
  // Ensure we have a date
  if (!formData.value.date) {
    formData.value.date = new Date().toISOString().split('T')[0];
  }
  
  if (!formData.value.type) {
    $q.notify({
      color: 'negative',
      message: 'Please select an event type',
      icon: 'error'
    });
    return;
  }
  
  // Create a copy of formData with type as string
  const eventData = { ...formData.value };
  
  // Ensure type is a string
  if (typeof eventData.type === 'object' && eventData.type.value) {
    eventData.type = eventData.type.value;
  } else {
    eventData.type = String(eventData.type);
  }
  
  // Format start_time and end_time if not full day
  if (!eventData.is_full_day) {
    if (eventData.date && eventData.start_time) {
      // Combine date and time for start_time
      const startDate = new Date(eventData.date);
      const [startHours, startMinutes] = eventData.start_time.split(':').map(Number);
      startDate.setHours(startHours, startMinutes, 0, 0);
      
      // Keep the original format if needed, or use ISO string
      // eventData.start_time = startDate.toISOString();
    }
  }
  
  emit('save', eventData);
};

const confirmDelete = () => {
  $q.dialog({
    title: 'Confirm Deletion',
    message: 'Are you sure you want to delete this event?',
    cancel: true,
    persistent: true
  }).onOk(() => {
    emit('delete', formData.value.id);
  });
};
</script>




