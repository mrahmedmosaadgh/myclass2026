<script setup>
import { ref, watch, onMounted, nextTick } from 'vue';
import katex from 'katex';
import { marked } from 'marked';
import DOMPurify from 'dompurify';
import 'katex/dist/katex.min.css';

const props = defineProps({
  content: {
    type: String,
    default: '1. $$ \\frac{2}{3} + \\frac{1}{3} $$'
  },
  placeholder: {
    type: String,
    default: 'Type Math Equations using $ \\frac{...} $ (inline) or $$ \\frac{...} $$ (display)...'
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

function renderContent() {
  if (!viewerRef.value) return;

  let processed = props.content || '';
  const mathTokens = [];

  // =========================
  // 1. EXTRACT LATEX
  // =========================
  const replaceMath = (regex, displayMode) => {
    processed = processed.replace(regex, (_, formula) => {
      try {
        const html = katex.renderToString(formula.trim(), {
          displayMode,
          throwOnError: false
        });
        mathTokens.push(html);
        return `@@MATH_${mathTokens.length - 1}@@`;
      } catch (e) {
        return `<span style="color:red">LaTeX Error</span>`;
      }
    });
  };

  replaceMath(/\$\$([\s\S]*?)\$\$/g, true);
  replaceMath(/\\\[([\s\S]*?)\\\]/g, true);
  replaceMath(/\$([^\$]+)\$/g, false);
  replaceMath(/\\\(([\s\S]*?)\\\)/g, false);

  // =========================
  // 2. MARKDOWN -> HTML
  // =========================
  marked.setOptions({
    breaks: true,
    gfm: true
  });

  let html = marked.parse(processed);

  // =========================
  // 3. SANITIZE HTML
  // =========================
  html = DOMPurify.sanitize(html, {
    ALLOWED_TAGS: [
      'b','i','em','strong','a','p','br','ul','ol','li',
      'code','pre','blockquote','h1','h2','h3','h4',
      'table','thead','tbody','tr','th','td',
      'span','div','img','hr'
    ],
    ALLOWED_ATTR: ['href','src','alt','title','style','class']
  });

  // =========================
  // 4. RESTORE LATEX
  // =========================
  mathTokens.forEach((token, i) => {
    html = html.replace(`@@MATH_${i}@@`, token);
  });

  viewerRef.value.innerHTML = html;
}

onMounted(() => {
  renderContent();
});

watch(() => props.content, (newVal) => {
  rawText.value = newVal;
  renderContent();
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
    renderContent();
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
      @contextmenu.prevent
      @blur="handleBlur"
      @keydown.stop
      :placeholder="props.placeholder"
      autofocus
    ></textarea>
    
    <div v-else>
      <div
        v-if="!props.content"
        class="math-placeholder"
      >
        {{ props.placeholder }}
      </div>
      <div 
        v-else
        ref="viewerRef" 
        class="math-viewer"
      ></div>
    </div>
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
  -webkit-touch-callout: none;
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

.math-placeholder {
  width: 100%;
  min-height: 60px;
  padding: 12px;
  border: 2px dashed #d1d5db;
  border-radius: 8px;
  background: #f9fafb;
  color: #9ca3af;
  font-size: 0.875rem;
  line-height: 1.5;
  user-select: none;
}
</style>
