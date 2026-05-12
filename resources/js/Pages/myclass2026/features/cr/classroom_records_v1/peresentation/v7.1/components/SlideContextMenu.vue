<script setup>
import { useClipboardStore } from '../stores/clipboardStore';
import { useUIStore } from '../stores/uiStore';
import { usePresentationStore } from '../stores/presentationStore';
import { usePaste } from '../composables/usePaste';

const clipboard = useClipboardStore();
const ui = useUIStore();
const presentation = usePresentationStore();
const { pasteElement } = usePaste();

const props = defineProps({
  x: Number,
  y: Number,
  show: Boolean
});

function pasteAtPosition() {
  if (!clipboard.hasClipboardContent()) return;
  
  // Create a custom paste function that uses the clicked position
  const pastedElement = clipboard.pasteElement(props.x, props.y);
  if (pastedElement) {
    // Add the element to the current slide
    presentation.addElement(pastedElement);
    ui.selectElement(pastedElement.id);
  }
  
  // Hide menu after paste
  emit('close');
}

const emit = defineEmits(['close']);
</script>

<template>
  <div v-if="show" class="slide-context-menu" :style="{ left: x + 'px', top: y + 'px' }">
    <div class="section">
      <p>Paste</p>
      
      <button 
        v-if="clipboard.hasClipboardContent()" 
        @click="pasteAtPosition" 
        class="icon-button"
      >
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 6px;">
          <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path>
          <rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect>
        </svg>
        Paste Element
      </button>
      
      <button v-else disabled class="icon-button disabled">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 6px;">
          <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path>
          <rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect>
        </svg>
        No Clipboard Content
      </button>
    </div>
  </div>
</template>

<style scoped>
.slide-context-menu {
  position: fixed;
  background: #111827;
  color: white;
  padding: 8px;
  border-radius: 8px;
  font-size: 12px;
  z-index: 10001;
  min-width: 180px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.section {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.section p {
  margin: 0 0 4px 0;
  font-weight: bold;
  border-bottom: 1px solid #374151;
  padding-bottom: 4px;
  font-size: 11px;
  text-transform: uppercase;
  color: #9ca3af;
}

button {
  background: transparent;
  color: #d1d5db;
  border: none;
  text-align: left;
  padding: 6px 8px;
  border-radius: 4px;
  cursor: pointer;
  width: 100%;
  display: flex;
  align-items: center;
  font-size: 12px;
}

button:hover:not(.disabled) {
  background: #374151;
}

button.disabled {
  color: #6b7280;
  cursor: not-allowed;
  opacity: 0.6;
}

.icon-button {
  justify-content: flex-start;
}
</style>
