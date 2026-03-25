<script setup>
import { ref, watch, onMounted } from 'vue';

const props = defineProps({
  content: {
    type: String,
    default: ''
  },
  isEditMode: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['update', 'select']);
const editorRef = ref(null);

onMounted(() => {
  if (editorRef.value && props.content) {
    editorRef.value.innerHTML = props.content;
  }
});

watch(() => props.content, (newVal) => {
  if (editorRef.value && editorRef.value.innerHTML !== newVal) {
    editorRef.value.innerHTML = newVal || '';
  }
});

function handleBlur(e) {
  emit('update', e.target.innerHTML);
}

function handleMousedown(e) {
  e.stopPropagation();
  emit('select');
}

function handleTouchstart(e) {
  e.stopPropagation();
  emit('select');
}
</script>

<template>
  <div
    ref="editorRef"
    class="editable-text"
    :contenteditable="isEditMode"
    @blur="handleBlur"
    @mousedown="handleMousedown"
    @touchstart="handleTouchstart"
    @keydown.stop
  ></div>
</template>

<style scoped>
.editable-text {
  width: 100%;
  height: 100%;
  outline: none;
  min-height: 1.5em;
  word-wrap: break-word;
  white-space: pre-wrap;
}

.editable-text[contenteditable="true"] {
  cursor: text;
}
</style>
