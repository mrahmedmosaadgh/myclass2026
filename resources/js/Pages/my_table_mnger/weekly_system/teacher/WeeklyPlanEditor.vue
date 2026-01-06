<template>
  <q-dialog v-model="showDialog" persistent>
    <q-card style="min-width: 600px">
      <q-card-section>
        <div class="text-h6">Edit Weekly Plan</div>
      </q-card-section>

      <q-card-section>
        <div class="q-gutter-md">
          <!-- Classwork -->
          <div>
            <q-input
              v-model="planData.cw"
              label="Classwork (CW)"
              type="textarea"
              :rows="4"
            />
          </div>

          <!-- Homework -->
          <div>
            <q-input
              v-model="planData.hw"
              label="Homework (HW)"
              type="textarea"
              :rows="4"
            />
          </div>

          <!-- Notes -->
          <div>
            <q-input
              v-model="planData.notes"
              label="Notes"
              type="textarea"
              :rows="4"
            />
          </div>
        </div>
      </q-card-section>

      <q-card-section align="right">
        <q-btn flat label="Cancel" @click="$emit('close')" color="primary" v-close-popup />
        <q-btn flat label="Save" @click="$emit('submit', planData)" color="primary" :loading="saving" />
      </q-card-section>
    </q-card>
  </q-dialog>
</template>

<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
  modelValue: {
    type: Boolean,
    required: true
  },
  plan: {
    type: Object,
    required: true
  },
  saving: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['update:modelValue', 'submit', 'close']);

// Reactive reference for dialog visibility
const showDialog = ref(props.modelValue);

// Reactive reference for plan data
const planData = ref({ ...props.plan });

// Watch for changes in the modelValue prop to update dialog visibility
watch(
  () => props.modelValue,
  (newVal) => {
    showDialog.value = newVal;
  }
);

// Watch for changes in dialog visibility to emit update event
watch(
  () => showDialog.value,
  (newVal) => {
    emit('update:modelValue', newVal);
  }
);
</script>