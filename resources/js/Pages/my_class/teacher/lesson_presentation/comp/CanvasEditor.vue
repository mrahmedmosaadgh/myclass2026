<template>
  <div
    class="canvas-editor"
    :class="{
      'presentation-mode': presentationMode,
      'readonly': readonly
    }"
  >
    <div class="elements-container">
      <DraggableWrapper
        v-for="element in modelValue.elements"
        :key="element.id"
        :modelValue="element"
        :readonly="presentationMode || readonly"
        @update:modelValue="updateElement(element.id, $event)"
        @toggle-visibility="toggleElementVisibility(element.id)"
        @open-settings="openElementSettings(element.id)"
        @delete-element="deleteElement(element.id)"
      >
        <ElementContent
          :element="element"
          :readonly="presentationMode || readonly"
          @update:element="updateElement(element.id, $event)"
        />
      </DraggableWrapper>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import DraggableWrapper from '@/Components/Common/DraggableWrapper.vue';
import ElementContent from './ElementContent.vue';

const props = defineProps({
  modelValue: {
    type: Object,
    required: true,
    default: () => ({
      elements: [],
      backgroundImage: ''
    })
  },
  presentationMode: {
    type: Boolean,
    default: false
  },
  readonly: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['update:modelValue']);

const updateElement = (id, updatedElement) => {
  const elements = [...props.modelValue.elements];
  const index = elements.findIndex(el => el.id === id);
  if (index !== -1) {
    elements[index] = updatedElement;
    emit('update:modelValue', {
      ...props.modelValue,
      elements
    });
  }
};

const toggleElementVisibility = (id) => {
  const elements = props.modelValue.elements.map(el =>
    el.id === id ? { ...el, visible: !el.visible } : el
  );
  emit('update:modelValue', {
    ...props.modelValue,
    elements
  });
};

const openElementSettings = (id) => {
  // Implement settings modal logic
};

const deleteElement = (id) => {
  const elements = props.modelValue.elements.filter(el => el.id !== id);
  emit('update:modelValue', {
    ...props.modelValue,
    elements
  });
};
</script>

<style scoped>
.canvas-editor {
  width: 100%;
  height: 100%;
  position: relative;
  overflow: hidden;
}

.presentation-mode {
  background-color: white;
}

.elements-container {
  position: relative;
  width: 100%;
  height: 100%;
}

.readonly .element-controls {
  display: none;
}
</style>


