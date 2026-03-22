<template>
  <div class="q-pa-md">
    <q-btn-dropdown
      :class="btnClass"
      :color="color"
      :label="label"
      :icon="icon"
      :loading="loading"
      :disable="disable"
      :outline="outline"
      :dense="dense"
      :no-caps="noCaps"
      content-style="min-width: 320px; max-width: 400px;"
    >
      <div class="row no-wrap q-pa-md">
        <div class="column message-column" style="min-width: 240px; max-width: 280px;">
          <div class="text-h6 q-mb-md">{{ title || 'Confirm Action' }}</div>
          <p class="text-body2 q-mb-md message-text">{{ message || 'Are you sure you want to proceed?' }}</p>
        </div>

        <q-separator vertical inset class="q-mx-lg" />

        <div class="column items-center justify-center action-column">
          <q-btn
            :color="confirmColor"
            :label="confirmLabel"
            :icon="confirmIcon"
            @click="handleConfirm"
            push
            size="sm"
            v-close-popup
            class="q-mb-sm"
          />
          <q-btn
            color="grey"
            label="Cancel"
            icon="close"
            outline
            size="sm"
            v-close-popup
          />
        </div>
      </div>
    </q-btn-dropdown>
  </div>
</template>

<script setup>
import { ref } from 'vue'

// Props
const props = defineProps({
  label: {
    type: String,
    required: true
  },
  icon: {
    type: String,
    default: ''
  },
  color: {
    type: String,
    default: 'primary'
  },
  title: {
    type: String,
    default: 'Confirm Action'
  },
  message: {
    type: String,
    default: 'Are you sure you want to proceed?'
  },
  confirmLabel: {
    type: String,
    default: 'Confirm'
  },
  confirmIcon: {
    type: String,
    default: 'check'
  },
  confirmColor: {
    type: String,
    default: 'primary'
  },
  loading: {
    type: Boolean,
    default: false
  },
  disable: {
    type: Boolean,
    default: false
  },
  outline: {
    type: Boolean,
    default: false
  },
  dense: {
    type: Boolean,
    default: false
  },
  noCaps: {
    type: Boolean,
    default: false
  },
  btnClass: {
    type: String,
    default: ''
  }
})

// Emits
const emit = defineEmits(['confirm'])

// Methods
const handleConfirm = () => {
  emit('confirm')
}
</script>

<style scoped>
.q-pa-md {
  padding: 0;
}

.message-column {
  flex: 1;
  min-width: 240px;
  max-width: 280px;
}

.message-text {
  line-height: 1.4;
  word-wrap: break-word;
  white-space: normal;
  margin: 0;
}

.action-column {
  min-width: 80px;
  flex-shrink: 0;
}
</style>
