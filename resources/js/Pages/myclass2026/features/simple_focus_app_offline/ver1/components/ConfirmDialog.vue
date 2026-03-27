<script setup>
import { computed, onBeforeUnmount, watch } from 'vue';

const props = defineProps({
  open: {
    type: Boolean,
    default: false,
  },
  title: {
    type: String,
    default: 'CONFIRM ACTION',
  },
  message: {
    type: String,
    default: '',
  },
  confirmLabel: {
    type: String,
    default: 'CONFIRM',
  },
  cancelLabel: {
    type: String,
    default: 'CANCEL',
  },
  tone: {
    type: String,
    default: 'warning',
  },
});

const emit = defineEmits(['confirm', 'cancel']);

const toneClass = computed(() => {
  switch (props.tone) {
    case 'danger':
      return 'dialog-danger';
    case 'success':
      return 'dialog-success';
    default:
      return 'dialog-warning';
  }
});

function handleKeydown(event) {
  if (!props.open) {
    return;
  }

  if (event.key === 'Escape') {
    emit('cancel');
  }

  if (event.key === 'Enter') {
    emit('confirm');
  }
}

watch(
  () => props.open,
  (open) => {
    if (open) {
      window.addEventListener('keydown', handleKeydown);
    } else {
      window.removeEventListener('keydown', handleKeydown);
    }
  },
  { immediate: true }
);

onBeforeUnmount(() => {
  window.removeEventListener('keydown', handleKeydown);
});
</script>

<template>
  <Teleport to="body">
    <div v-if="open" class="confirm-overlay">
      <div class="confirm-panel" :class="toneClass">
        <div class="confirm-header">
          <span class="confirm-cursor">▌</span>
          <h2>{{ title }}</h2>
        </div>

        <p class="confirm-message">{{ message }}</p>

        <div class="confirm-actions">
          <button class="confirm-button ghost" @click="emit('cancel')">
            {{ cancelLabel }}
          </button>
          <button class="confirm-button solid" @click="emit('confirm')">
            {{ confirmLabel }}
          </button>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<style scoped>
.confirm-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.82);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 1rem;
}

.confirm-panel {
  width: min(560px, 100%);
  border: 1px solid #22c55e;
  background: linear-gradient(180deg, rgba(3, 7, 18, 0.98), rgba(0, 0, 0, 0.98));
  color: #d1fae5;
  box-shadow: 0 0 0 1px rgba(34, 197, 94, 0.15), 0 24px 80px rgba(0, 0, 0, 0.6);
  padding: 1rem;
  font-family: 'Courier New', Courier, monospace;
}

.confirm-warning {
  border-color: #fbbf24;
}

.confirm-danger {
  border-color: #ef4444;
}

.confirm-success {
  border-color: #22c55e;
}

.confirm-header {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 0.85rem;
}

.confirm-header h2 {
  margin: 0;
  font-size: 1rem;
  letter-spacing: 0.18em;
  text-transform: uppercase;
}

.confirm-cursor {
  color: #22c55e;
}

.confirm-message {
  margin: 0;
  line-height: 1.7;
  color: #86efac;
  white-space: pre-line;
}

.confirm-actions {
  margin-top: 1.2rem;
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
  flex-wrap: wrap;
}

.confirm-button {
  border: 1px solid #22c55e;
  background: transparent;
  color: #d1fae5;
  padding: 0.75rem 1rem;
  font-family: inherit;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  cursor: pointer;
  transition: transform 0.18s ease, background 0.18s ease, color 0.18s ease;
}

.confirm-button:hover {
  transform: translateY(-1px);
}

.confirm-button.solid {
  background: #22c55e;
  color: #000;
}

.confirm-button.ghost:hover {
  background: rgba(34, 197, 94, 0.1);
}
</style>
