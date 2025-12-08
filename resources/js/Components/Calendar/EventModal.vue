<template>
  <q-dialog v-model="showModal" persistent>
    <q-card style="min-width: 500px">
      <q-card-section class="row items-center">
        <div class="text-h6">{{ isNewEvent ? 'Add Event' : 'Edit Event' }}</div>
        <q-space />
        <q-btn icon="close" flat round dense v-close-popup />
      </q-card-section>

      <q-card-section>
        <EventForm 
          :event="event" 
          :event-types="eventTypes"
          :is-submitting="isSubmitting"
          @save="handleSave"
          @delete="handleDelete"
        />
      </q-card-section>

      <q-card-actions align="right">
        <q-btn 
          v-if="!isNewEvent" 
          flat 
          color="negative" 
          label="Delete" 
          @click="confirmDelete" 
          :disable="isSubmitting"
        />
        <q-space />
        <q-btn flat label="Cancel" v-close-popup :disable="isSubmitting" />
        <q-btn 
          color="primary" 
          label="Save" 
          @click="handleSave(event)" 
          :loading="isSubmitting"
        />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<script setup>
import { ref, watch } from 'vue';
import { useQuasar } from 'quasar';
import EventForm from './EventForm.vue';

const $q = useQuasar();

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  },
  event: {
    type: Object,
    required: true
  },
  isNewEvent: {
    type: Boolean,
    default: true
  },
  eventTypes: {
    type: Array,
    default: () => []
  }
});

const emit = defineEmits(['update:modelValue', 'save', 'delete']);

const showModal = ref(props.modelValue);
const isSubmitting = ref(false);

// Watch for changes in modelValue prop
watch(() => props.modelValue, (newValue) => {
  showModal.value = newValue;
});

// Watch for changes in showModal and emit update:modelValue
watch(showModal, (newValue) => {
  emit('update:modelValue', newValue);
});

const handleSave = async (eventData) => {
  isSubmitting.value = true;
  
  try {
    await emit('save', eventData);
    showModal.value = false;
  } catch (error) {
    console.error('Error saving event:', error);
    $q.notify({
      color: 'negative',
      message: 'Failed to save event',
      icon: 'error'
    });
  } finally {
    isSubmitting.value = false;
  }
};

const confirmDelete = () => {
  $q.dialog({
    title: 'Confirm Deletion',
    message: 'Are you sure you want to delete this event?',
    cancel: true,
    persistent: true
  }).onOk(() => {
    handleDelete(props.event.id);
  });
};

const handleDelete = async (eventId) => {
  isSubmitting.value = true;
  
  try {
    await emit('delete', eventId);
    showModal.value = false;
  } catch (error) {
    console.error('Error deleting event:', error);
    $q.notify({
      color: 'negative',
      message: 'Failed to delete event',
      icon: 'error'
    });
  } finally {
    isSubmitting.value = false;
  }
};
</script>

