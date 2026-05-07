<script setup>
import { computed } from 'vue'

const props = defineProps({
  label: {
    type: String,
    default: 'Confirm Action'
  },
  color: {
    type: String,
    default: 'primary'
  },
  icon: {
    type: String,
    default: ''
  },
  title: {
    type: String,
    default: 'Confirm Action'
  },
  message: {
    type: String,
    default: 'Are you sure you want to continue?'
  },
  confirmLabel: {
    type: String,
    default: 'Confirm'
  },
  cancelLabel: {
    type: String,
    default: 'Cancel'
  },
  confirmColor: {
    type: String,
    default: 'negative'
  },
  cancelColor: {
    type: String,
    default: 'grey-7'
  },
  disabled: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['confirm', 'cancel'])

// Handle confirmation
const handleConfirm = () => {
  emit('confirm')
}

// Handle cancellation
const handleCancel = () => {
  emit('cancel')
}
</script>

<template>
  <div class="q-pa-md">
    <q-btn-dropdown 
      class="glossy" 
      :color="color" 
      :label="label"
      :icon="icon"
      :disable="disabled"
      split
      @click="handleConfirm"
    >
      <div class="row no-wrap q-pa-md" style="min-width: 250px;">
        <div class="column">
          <div class="text-h6 q-mb-md">{{ title }}</div>
          <div class="text-body2 q-mb-md text-grey-8">{{ message }}</div>
          
          <div class="row q-gutter-sm q-mt-md">
            <q-btn 
              :color="confirmColor"
              :label="confirmLabel"
              push
              size="sm"
              v-close-popup
              @click="handleConfirm"
              class="col"
            />
            <q-btn 
              :color="cancelColor"
              :label="cancelLabel"
              push
              size="sm"
              v-close-popup
              @click="handleCancel"
              class="col"
            />
          </div>
        </div>
      </div>
    </q-btn-dropdown>
  </div>
</template>

<style scoped>
.q-btn-dropdown {
  border-radius: 8px;
}

.q-btn-dropdown:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.q-btn-dropdown:active {
  transform: translateY(0);
}
</style>
