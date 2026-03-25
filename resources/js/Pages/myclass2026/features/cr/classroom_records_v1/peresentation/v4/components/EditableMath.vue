<script setup>
import { ref, watch, onMounted, nextTick } from 'vue';
import katex from 'katex';
import 'katex/dist/katex.min.css';

const props = defineProps({
  content: {
    type: String,
    default: '1. $$ \\frac{2}{3} + \\frac{1}{3} $$'
  },
  isEditMode: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['update', 'select']);
const isEditing = ref(false);
const rawText = ref(props.content);
const viewerRef = ref(null);

function renderKatex() {
  if (!viewerRef.value) return;
  
  let processed = props.content;
  const mathTokens = [];
  
  // TOKENIZE MATH: Process formulas and replace with placeholders (so Markdown/BR rules don't break complex Math layout)
  processed = processed.replace(/\$\$([\s\S]*?)\$\$/g, (match, formula) => {
    try {
      const rendered = katex.renderToString(formula, { displayMode: true, throwOnError: false });
      mathTokens.push(rendered);
      return `__MATH_TOKEN_${mathTokens.length - 1}__`;
    } catch (e) {
      return `<strong style="color:red">Syntax Error: ${e.message}</strong>`;
    }
  });

  processed = processed.replace(/\$([^\$]+)\$/g, (match, formula) => {
    try {
      const rendered = katex.renderToString(formula, { displayMode: false, throwOnError: false });
      mathTokens.push(rendered);
      return `__MATH_TOKEN_${mathTokens.length - 1}__`;
    } catch (e) {
      return `<strong style="color:red">Syntax Error: ${e.message}</strong>`;
    }
  });
  
  // PROCESS MARKDOWN (Safe from Math)
  processed = processed.replace(/^### (.*$)/gim, '<h3 style="margin: 8px 0; font-size: 1.25em; font-weight: bold; color: inherit;">$1</h3>');
  processed = processed.replace(/^## (.*$)/gim, '<h2 style="margin: 8px 0; font-size: 1.5em; font-weight: bold; color: inherit;">$1</h2>');
  processed = processed.replace(/^# (.*$)/gim, '<h1 style="margin: 8px 0; font-size: 2em; font-weight: bold; color: inherit;">$1</h1>');
  processed = processed.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
  processed = processed.replace(/^---$/gim, '<hr style="border: 0; border-top: 2px dashed currentColor; opacity: 0.3; margin: 15px 0;">');

  // Collapse excess trailing newlines strictly following structural tags to prevent huge gaping holes
  processed = processed.replace(/(<h[1-3][^>]*>.*?<\/h[1-3]>)\n/gi, '$1');
  processed = processed.replace(/(<hr[^>]*>)\n/gi, '$1');

  // Convert standard newlines to HTML br for normal text structural layout
  processed = processed.replace(/\n/g, '<br>');

  // INJECT MATH HTML BACK IN
  mathTokens.forEach((token, index) => {
    processed = processed.replace(`__MATH_TOKEN_${index}__`, token);
  });

  viewerRef.value.innerHTML = processed;
}

onMounted(() => {
  renderKatex();
});

watch(() => props.content, (newVal) => {
  rawText.value = newVal;
  renderKatex();
});

function handleMousedown(e) {
  e.stopPropagation();
  emit('select');
}

function handleDblClick(e) {
  e.stopPropagation();
  if (props.isEditMode) {
    isEditing.value = true;
    rawText.value = props.content;
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
      rawText.value = props.content;
    }
  }
  lastTap = currentTime;
}

function handleBlur() {
  isEditing.value = false;
  emit('update', rawText.value);
  nextTick(() => {
    renderKatex();
  });
}
</script>

<template>
  <div 
    class="editable-math-container" 
    @mousedown="handleMousedown"
    @dblclick="handleDblClick"
    @touchstart="handleTouchstart"
  >
    <textarea
      v-if="isEditing"
      v-model="rawText"
      class="math-editor"
      @blur="handleBlur"
      @keydown.stop
      placeholder="Type Math Equations using $$ \frac{...} $$..."
      autofocus
    ></textarea>
    
    <div 
      v-else 
      ref="viewerRef" 
      class="math-viewer"
    ></div>
  </div>
</template>

<style scoped>
.editable-math-container {
  width: 100%;
  height: 100%;
}

.math-editor {
  width: 100%;
  height: 100%;
  resize: none;
  font-family: monospace;
  font-size: 14px;
  background: #f8fafc;
  border: 1px dashed #cbd5e1;
  padding: 8px;
  border-radius: 4px;
  outline: none;
}

.math-editor:focus {
  border-color: #6366f1;
  background: white;
}

.math-viewer {
  width: 100%;
  height: 100%;
  font-size: 1.5rem;
  overflow: hidden;
  color: inherit;
  pointer-events: none; /* So dragging is perfectly reliable */
}
</style>
