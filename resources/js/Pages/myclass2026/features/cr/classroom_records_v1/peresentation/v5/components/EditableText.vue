<script setup>
import { ref, watch, onMounted, nextTick } from 'vue';

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
const isEditing = ref(false);

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
  isEditing.value = false;
  emit('update', e.target.innerHTML);
}

function handleMousedown(e) {
  e.stopPropagation();
  emit('select');
}

function handleDblClick(e) {
  e.stopPropagation();
  if (props.isEditMode) {
    isEditing.value = true;
    nextTick(() => {
      if (editorRef.value) {
        editorRef.value.focus();
      }
    });
  }
}

let lastTap = 0;
function handleTouchstart(e) {
  e.stopPropagation();
  emit('select');
  
  const currentTime = new Date().getTime();
  const tapLength = currentTime - lastTap;
  if (tapLength < 300 && tapLength > 0) {
    if (props.isEditMode) {
      isEditing.value = true;
      nextTick(() => {
        if (editorRef.value) {
          editorRef.value.focus();
        }
      });
    }
  }
  lastTap = currentTime;
}
</script>

<template>
  <div
    ref="editorRef"
    class="editable-text"
    :contenteditable="isEditing"
    @blur="handleBlur"
    @mousedown="handleMousedown"
    @dblclick="handleDblClick"
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
  background: white;
  border: 1px dashed #cbd5e1;
}

.editable-text:not([contenteditable="true"]) {
  cursor: pointer;
}
</style>
