<script setup>
import { usePresentationStore } from '../stores/presentationStore';
import { useClipboardStore } from '../stores/clipboardStore';

const props = defineProps({
  element: Object
});

const store = usePresentationStore();
const clipboard = useClipboardStore();

function update(changes) {
  store.updateElement({
    id: props.element.id,
    changes
  });
}

function copyElement() {
  clipboard.copyElement(props.element, store.currentSlide.id);
}

function cutElement() {
  clipboard.cutElement(props.element, store.currentSlide.id);
  store.deleteElement(props.element.id);
}

function pasteElement() {
  if (!clipboard.hasClipboardContent()) return;
  
  const pastedElement = clipboard.pasteElement(props.element.x + 20, props.element.y + 20);
  if (pastedElement) {
    store.addElement(pastedElement);
  }
}
</script>

<template>
  <div class="menu">
    <!-- VISIBILITY -->
    <div class="section">
      <p>Visibility</p>

      <button @click="update({ visibilityOption: 'hidden-clickable', isVisible: false })">
        Start Hidden (click to show)
      </button>

      <button @click="update({ visibilityOption: 'shown-clickable', isVisible: true })">
        Start Visible (click to hide)
      </button>

      <button @click="update({ visibilityOption: 'moveable' })">
        Moveable
      </button>

      <button @click="update({ visibilityOption: 'no-interaction' })">
        No Interaction
      </button>
    </div>

    <!-- LAYERS -->
    <div class="section">
      <p>Layers</p>

      <button @click="update({ zIndex: props.element.zIndex + 1 })">Bring Forward</button>
      <button @click="update({ zIndex: Math.max(1, props.element.zIndex - 1) })">Send Backward</button>
    </div>

    <!-- ELEMENT -->
    <div class="section">
      <p>Element</p>
      
      <button @click="copyElement" class="icon-button">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 6px;">
          <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
          <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
        </svg>
        Copy
      </button>
      
      <button @click="cutElement" class="icon-button">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 6px;">
          <path d="M3 16v-2a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v2"></path>
          <path d="M3 8V6a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v2"></path>
          <path d="M8 21h8"></path>
          <line x1="12" y1="11" x2="12" y2="17"></line>
          <line x1="9" y1="14" x2="15" y2="14"></line>
        </svg>
        Cut
      </button>
      
      <button @click="pasteElement" class="icon-button" :disabled="!clipboard.hasClipboardContent()">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 6px;">
          <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path>
          <rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect>
        </svg>
        {{ clipboard.hasClipboardContent() ? 'Paste' : 'No Clipboard Content' }}
      </button>
      
      <button @click="$emit('duplicate')" class="icon-button">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 6px;">
          <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
          <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
        </svg>
        Duplicate
      </button>
      
      <button @click="$emit('delete')" class="icon-button">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 6px;">
          <polyline points="3,6 5,6 21,6"></polyline>
          <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
        </svg>
        Delete
      </button>
    </div>
  </div>
</template>

<style scoped>
.menu {
  position: absolute;
  top: -100px;
  right: -10px;
  background: #111827;
  color: white;
  padding: 10px;
  border-radius: 8px;
  font-size: 12px;
  z-index: 10000;
  width: 200px;
}

.section {
  margin-bottom: 8px;
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.section p {
  margin: 0 0 4px 0;
  font-weight: bold;
  border-bottom: 1px solid #374151;
  padding-bottom: 4px;
}

button {
  background: transparent;
  color: #d1d5db;
  border: none;
  text-align: left;
  padding: 4px 8px;
  border-radius: 4px;
  cursor: pointer;
  width: 100%;
  display: flex;
  align-items: center;
}
button:hover {
  background: #374151;
}

.icon-button {
  justify-content: flex-start;
}

button:disabled {
  color: #6b7280;
  cursor: not-allowed;
  opacity: 0.6;
}

button:disabled:hover {
  background: transparent;
}
</style>
