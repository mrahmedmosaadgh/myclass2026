<template>
  <div
    class="text-element w-full h-full"
    :style="{
      color: element.color || '#000000',
      fontSize: (element.fontSize || 16) + 'px',
      fontFamily: element.fontFamily || 'Arial',
      fontWeight: element.fontWeight || 'normal',
      fontStyle: element.fontStyle || 'normal',
      textAlign: element.textAlign || 'left',
      lineHeight: element.lineHeight || 1.5,
      padding: '8px'
    }"
  >
    <div
      ref="textContent"
      class="outline-none break-words whitespace-pre-wrap w-full h-full"
      :contenteditable="isEditing"
      @dblclick="enableEdit"
      @blur="finishEdit"
      @keydown.stop
      v-html="element.content"
    ></div>
  </div>
</template>

<script setup>
import { ref, watch, nextTick } from 'vue';

const props = defineProps({
  element: {
    type: Object,
    required: true
  },
  isSelected: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['update', 'select', 'delete', 'move-to-front', 'move-to-back']);

const isEditing = ref(false);
const textContent = ref(null);

watch(() => props.isSelected, (newVal) => {
  if (!newVal && isEditing.value) {
    finishEdit();
  }
});

const enableEdit = () => {
  isEditing.value = true;
  nextTick(() => {
    if (textContent.value) {
      textContent.value.focus();
      // Optional: Select all text when editing starts
      // document.execCommand('selectAll', false, null);
    }
  });
};

const finishEdit = () => {
  if (!isEditing.value) return;
  isEditing.value = false;
  if (textContent.value) {
    emit('update', {
      content: textContent.value.innerHTML
    });
  }
};
</script>

<style scoped>
.text-element {
  user-select: text;
}
</style>
