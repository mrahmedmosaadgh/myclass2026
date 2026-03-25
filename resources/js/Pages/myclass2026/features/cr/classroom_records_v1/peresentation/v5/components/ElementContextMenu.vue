<script setup>
import { usePresentationStore } from '../stores/presentationStore';

const props = defineProps({
  element: Object
});

const store = usePresentationStore();

function update(changes) {
  store.updateElement({
    id: props.element.id,
    changes
  });
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
      
      <button @click="$emit('duplicate')">Duplicate</button>
      <button @click="$emit('delete')">Delete</button>
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
}
button:hover {
  background: #374151;
}
</style>
