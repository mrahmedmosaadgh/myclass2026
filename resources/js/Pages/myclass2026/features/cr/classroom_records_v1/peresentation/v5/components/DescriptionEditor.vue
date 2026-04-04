<script setup>
import { ref, nextTick, watch, computed } from 'vue';
import { renderMath as renderMathUtil } from '@/Utils/katex';
import './utils/mathAIHelper'; // Load AI helper

// Props
const props = defineProps({
  modelValue: {
    type: String,
    default: ''
  },
  placeholder: {
    type: String,
    default: 'Add description, notes, math formulas, or rich content...'
  },
  showPlaceholder: {
    type: Boolean,
    default: true
  },
  editable: {
    type: Boolean,
    default: true
  },
  maxHeight: {
    type: String,
    default: '200px'
  },
  minHeight: {
    type: String,
    default: '80px'
  },
  showPresentModeToggle: {
    type: Boolean,
    default: false
  },
  showInPresentMode: {
    type: Boolean,
    default: true
  }
});

// Emits
const emit = defineEmits(['update:modelValue', 'save', 'cancel', 'toggle-present-mode', 'clear']);

// State
const isEditing = ref(false);
const content = ref(props.modelValue || '');
const editor = ref(null);
const isRenderMode = ref(false);

// Computed property for rendered content with LaTeX
const renderedContent = computed(() => {
  if (!props.modelValue) return '';
  return renderMathUtil(props.modelValue);
});

// Functions
function startEditing() {
  content.value = props.modelValue || '';
  isEditing.value = true;
  isRenderMode.value = false;
  nextTick(() => {
    if (editor.value) {
      editor.value.innerHTML = typeof content.value === 'string' ? content.value : String(content.value || '');
      editor.value.focus();
    }
  });
}

function updateContent(event) {
  content.value = typeof event.target.innerHTML === 'string' ? event.target.innerHTML : String(event.target.innerHTML || '');
  emit('update:modelValue', content.value);
}

function save() {
  const finalContent = typeof content.value === 'string' ? content.value : String(content.value || '');
  emit('update:modelValue', finalContent);
  emit('save', finalContent);
  isEditing.value = false;
}

function cancel() {
  content.value = props.modelValue || '';
  emit('cancel');
  isEditing.value = false;
}

function togglePresentMode() {
  emit('toggle-present-mode');
}

function clear() {
  emit('clear');
  content.value = '';
  emit('update:modelValue', '');
}

// Enhanced markdown rendering with math support
function renderMarkdown() {
  if (!content.value) return;
  
  let markdownContent = content.value;
  
  // Basic markdown parsing
  markdownContent = markdownContent
    // Headers
    .replace(/^### (.*$)/gim, '<h3>$1</h3>')
    .replace(/^## (.*$)/gim, '<h2>$1</h2>')
    .replace(/^# (.*$)/gim, '<h1>$1</h1>')
    // Bold
    .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
    // Italic
    .replace(/\*(.+?)\*/g, '<em>$1</em>')
    // Code blocks
    .replace(/```([^`]+)```/g, '<pre><code>$1</code></pre>')
    // Inline code
    .replace(/`([^`]+)`/g, '<code>$1</code>')
    // Lists
    .replace(/^\* (.+)$/gim, '<li>$1</li>')
    .replace(/(<li>.*<\/li>)/s, '<ul>$1</ul>')
    // Line breaks
    .replace(/\n\n/g, '</p><p>')
    .replace(/\n/g, '<br>');
  
  // Wrap in paragraph if not already wrapped
  if (!markdownContent.startsWith('<')) {
    markdownContent = `<p>${markdownContent}</p>`;
  }
  
  // Apply math rendering
  markdownContent = renderMathUtil(markdownContent);
  
  // Update editor content
  if (editor.value) {
    editor.value.innerHTML = markdownContent;
    isRenderMode.value = true;
  }
}

// Math rendering function
function renderMath() {
  if (!content.value) return;
  
  const mathContent = renderMathUtil(content.value);
  
  if (editor.value) {
    editor.value.innerHTML = mathContent;
    isRenderMode.value = true;
  }
}

// Toggle between edit and render mode
function toggleRenderMode() {
  if (isRenderMode.value) {
    // Switch back to edit mode
    isRenderMode.value = false;
    if (editor.value) {
      editor.value.innerHTML = content.value;
    }
  } else {
    // Switch to render mode
    renderMarkdown();
  }
}

// AI-powered math assistance
async function assistWithMath() {
  if (!window.aiAssistant) {
    console.warn('AI Assistant not available');
    return;
  }
  
  const selection = window.getSelection();
  const selectedText = selection.toString().trim();
  
  if (selectedText) {
    try {
      const result = await window.aiAssistant.assistWithMath(selectedText);
      if (result && result.suggestion) {
        // Replace selected text with AI suggestion
        const range = selection.getRangeAt(0);
        range.deleteContents();
        
        // Create a text node with the suggestion
        const textNode = document.createTextNode(result.suggestion);
        range.insertNode(textNode);
        
        // Update the content
        updateContent({ target: editor.value });
        
        // Show visual feedback
        if (editor.value) {
          editor.value.style.backgroundColor = '#f0f9ff';
          setTimeout(() => {
            if (editor.value) {
              editor.value.style.backgroundColor = '';
            }
          }, 1000);
        }
        
        console.log('AI Assistant suggestion:', result.description);
      }
    } catch (err) {
      console.error('AI math assistance failed:', err);
    }
  } else {
    // If no text is selected, show a helpful hint
    const hint = '💡 Select text and click AI to get math assistance. Try: "quadratic", "pythagorean", or type LaTeX like $x^2 + y^2 = r^2$';
    
    // Create a temporary hint element
    const hintEl = document.createElement('div');
    hintEl.textContent = hint;
    hintEl.style.cssText = `
      position: absolute;
      top: -40px;
      left: 50%;
      transform: translateX(-50%);
      background: #1f2937;
      color: white;
      padding: 8px 12px;
      border-radius: 6px;
      font-size: 0.75rem;
      white-space: nowrap;
      z-index: 1000;
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    `;
    
    // Position hint near the AI button
    const aiBtn = editor.value?.parentElement?.querySelector('.ai-btn');
    if (aiBtn) {
      aiBtn.style.position = 'relative';
      aiBtn.appendChild(hintEl);
      
      // Remove hint after 3 seconds
      setTimeout(() => {
        if (hintEl.parentNode) {
          hintEl.parentNode.removeChild(hintEl);
        }
      }, 3000);
    }
  }
}

// Keyboard shortcuts
function handleKeyboardShortcuts(event) {
  if (isEditing.value && !isRenderMode.value) {
    // Ctrl/Cmd + Enter: Render markdown
    if ((event.ctrlKey || event.metaKey) && event.key === 'Enter') {
      event.preventDefault();
      renderMarkdown();
    }
    // Ctrl/Cmd + Shift + M: AI assistance
    if ((event.ctrlKey || event.metaKey) && event.shiftKey && event.key === 'M') {
      event.preventDefault();
      assistWithMath();
    }
    // Ctrl/Cmd + Shift + P: Toggle preview mode
    if ((event.ctrlKey || event.metaKey) && event.shiftKey && event.key === 'P') {
      event.preventDefault();
      toggleRenderMode();
    }
  }
}

// Watch for external model value changes
watch(() => props.modelValue, (newValue) => {
  if (!isEditing.value) {
    content.value = newValue || '';
  }
});
</script>

<template>
  <div class="description-editor-component">
    <!-- Placeholder state -->
    <div v-if="!isEditing && !modelValue && showPlaceholder" 
         class="description-placeholder" 
         @click="editable && startEditing()">
      <div class="placeholder-content">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
          <polyline points="14 2 14 8 20 8"></polyline>
          <line x1="16" y1="13" x2="8" y2="13"></line>
          <line x1="16" y1="17" x2="8" y2="17"></line>
          <polyline points="10 9 9 9 8 9"></polyline>
        </svg>
        <span>{{ placeholder }}</span>
      </div>
    </div>
    
    <!-- Display state -->
    <div v-else-if="!isEditing && modelValue" 
         class="description-display" 
         @click="editable && startEditing()"
         v-html="renderedContent">
    </div>
    
    <!-- Edit state -->
    <div v-else class="description-edit">
      <div class="description-editor-wrapper">
        <div 
          ref="editor"
          :contenteditable="editable && !isRenderMode"
          @input="!isRenderMode && updateContent($event)"
          @blur="!isRenderMode && save()"
          @keyup.escape="!isRenderMode && cancel()"
          @keydown="handleKeyboardShortcuts"
          :class="['description-editor', { 'render-mode': isRenderMode }]"
          :style="{ minHeight, maxHeight }"
          :placeholder="`${placeholder}

💡 Try: $x^2 + y^2 = r^2$ for inline math or $$\\frac{-b \\pm \\sqrt{b^2-4ac}}{2a}$$ for display math

📝 Markdown: # Header, **bold**, *italic*, \`code\`, * list items

🤖 Select text + click AI for math help

⌨️  Shortcuts: Ctrl+Enter (render), Ctrl+Shift+M (AI), Ctrl+Shift+P (preview)`"
        ></div>
        <div class="description-toolbar" v-if="editable">
          <button @click="toggleRenderMode" :class="['toolbar-btn', { 'active': isRenderMode }]" :title="isRenderMode ? 'Switch to Edit Mode' : 'Switch to Render Mode'">
            <svg v-if="!isRenderMode" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
              <polyline points="14 2 14 8 20 8"></polyline>
              <line x1="16" y1="13" x2="8" y2="13"></line>
              <line x1="16" y1="17" x2="8" y2="17"></line>
            </svg>
            <svg v-else xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
              <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
            </svg>
            {{ isRenderMode ? 'Edit' : 'Preview' }}
          </button>
          <button @click="renderMarkdown" class="toolbar-btn" title="Render Markdown & Math">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
              <polyline points="14 2 14 8 20 8"></polyline>
              <line x1="16" y1="13" x2="8" y2="13"></line>
              <line x1="16" y1="17" x2="8" y2="17"></line>
            </svg>
            Render
          </button>
          <button @click="assistWithMath" class="toolbar-btn ai-btn" title="AI Math Assistant">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
            </svg>
            AI
          </button>
          <div class="toolbar-separator"></div>
          <button @click="save" class="toolbar-btn btn-save" title="Save">
            ✓
          </button>
          <button @click="cancel" class="toolbar-btn btn-cancel" title="Cancel">
            ✕
          </button>
        </div>
      </div>
    </div>
    
    <!-- Control Panel -->
    <div v-if="showPresentModeToggle || modelValue" class="description-controls">
      <div v-if="showPresentModeToggle" class="control-group">
        <label class="control-label">
          <input 
            type="checkbox" 
            :checked="showInPresentMode"
            @change="togglePresentMode"
            class="control-checkbox"
          >
          Show in presentation mode
        </label>
      </div>
      <div v-if="modelValue" class="control-group">
        <button @click="clear" class="control-btn clear-btn" title="Clear description">
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M3 6h18"></path>
            <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"></path>
            <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"></path>
          </svg>
          Clear
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.description-editor-component {
  width: 100%;
  position: relative;
}

.description-placeholder {
  cursor: pointer;
  border: 2px dashed #d1d5db;
  border-radius: 8px;
  padding: 16px;
  background: #f9fafb;
  transition: all 0.2s;
  min-height: 60px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.description-placeholder:hover {
  border-color: #6366f1;
  background: #f0f9ff;
}

.placeholder-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  color: #9ca3af;
  font-size: 0.875rem;
  text-align: center;
}

.description-display {
  cursor: pointer;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  padding: 12px 16px;
  background: white;
  min-height: 60px;
  max-height: 120px;
  overflow-y: auto;
  transition: all 0.2s;
  line-height: 1.5;
}

.description-display:hover {
  border-color: #6366f1;
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

.description-edit {
  border: 2px solid #3b82f6;
  border-radius: 8px;
  background: white;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.description-editor-wrapper {
  position: relative;
}

.description-editor {
  padding: 12px;
  font-size: 0.875rem;
  line-height: 1.6;
  color: #374151;
  overflow-y: auto;
  outline: none;
  border: none;
  background: transparent;
}

.description-editor:empty:before {
  content: attr(placeholder);
  color: #9ca3af;
  font-style: italic;
}

.description-editor:focus {
  outline: none;
}

.description-toolbar {
  position: absolute;
  top: 8px;
  right: 8px;
  display: flex;
  gap: 4px;
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 6px;
  padding: 4px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.toolbar-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 28px;
  height: 28px;
  border: none;
  border-radius: 4px;
  background: transparent;
  color: #6b7280;
  cursor: pointer;
  font-size: 12px;
  font-weight: 600;
  transition: all 0.2s;
}

.toolbar-btn:hover {
  background: #f3f4f6;
  color: #374151;
}

.toolbar-btn.btn-save {
  background: #10b981;
  color: white;
}

.toolbar-btn.btn-save:hover {
  background: #059669;
}

.toolbar-btn.btn-cancel {
  background: #ef4444;
  color: white;
}

.toolbar-btn.btn-cancel:hover {
  background: #dc2626;
}

.toolbar-separator {
  width: 1px;
  height: 20px;
  background: #d1d5db;
  margin: 0 4px;
}

.toolbar-btn.active {
  background: #3b82f6;
  color: white;
}

.toolbar-btn.active:hover {
  background: #2563eb;
}

.ai-btn {
  position: relative;
  background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
  color: white;
  border: none;
}

.ai-btn:hover {
  background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%);
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(139, 92, 246, 0.3);
}

/* Math rendering styles */
.math-render {
  font-family: 'KaTeX_Main', 'Times New Roman', serif;
}

.math-render.display-mode {
  display: block;
  text-align: center;
  margin: 16px 0;
}

/* Markdown rendering styles */
.description-display h1, .description-display h2, .description-display h3,
.description-display h4, .description-display h5, .description-display h6 {
  margin: 16px 0 8px 0;
  font-weight: 600;
  color: #111827;
}

.description-display h1 { font-size: 1.5rem; }
.description-display h2 { font-size: 1.25rem; }
.description-display h3 { font-size: 1.125rem; }

.description-display p {
  margin: 8px 0;
}

.description-display ul, .description-display ol {
  margin: 8px 0;
  padding-left: 24px;
}

.description-display li {
  margin: 4px 0;
}

.description-display code {
  background: #f3f4f6;
  padding: 2px 6px;
  border-radius: 4px;
  font-family: 'Courier New', monospace;
  font-size: 0.875rem;
  color: #ef4444;
}

.description-display pre {
  background: #1f2937;
  color: #f9fafb;
  padding: 12px;
  border-radius: 6px;
  overflow-x: auto;
  font-family: 'Courier New', monospace;
  font-size: 0.875rem;
  margin: 8px 0;
}

.description-display blockquote {
  border-left: 4px solid #6366f1;
  padding-left: 16px;
  margin: 8px 0;
  color: #6b7280;
  font-style: italic;
}

/* Math and Markdown Content Styles */
.description-editor h1,
.description-editor h2,
.description-editor h3 {
  margin: 16px 0 8px 0;
  font-weight: 600;
  color: #111827;
}

.description-editor h1 { font-size: 1.5rem; }
.description-editor h2 { font-size: 1.25rem; }
.description-editor h3 { font-size: 1.125rem; }

.description-editor p {
  margin: 8px 0;
  line-height: 1.6;
}

.description-editor ul,
.description-editor ol {
  margin: 8px 0;
  padding-left: 24px;
}

.description-editor li {
  margin: 4px 0;
}

.description-editor pre {
  background: #f3f4f6;
  border: 1px solid #e5e7eb;
  border-radius: 6px;
  padding: 12px;
  margin: 8px 0;
  overflow-x: auto;
  font-family: 'Courier New', monospace;
  font-size: 0.875rem;
}

.description-editor code {
  background: #f3f4f6;
  border: 1px solid #e5e7eb;
  border-radius: 3px;
  padding: 2px 4px;
  font-family: 'Courier New', monospace;
  font-size: 0.875rem;
}

.description-editor pre code {
  background: transparent;
  border: none;
  padding: 0;
}

/* KaTeX Math Styles */
.description-editor .katex {
  font-size: 1em;
}

.description-editor .katex-display {
  margin: 16px 0;
  text-align: center;
}

.description-editor .katex-inline {
  margin: 0 2px;
}

/* Render Mode Styles */
.description-editor.render-mode {
  background: #f9fafb;
  border-color: #d1d5db;
  cursor: default;
}

.description-editor.render-mode:focus {
  outline: none;
  border-color: #d1d5db;
  box-shadow: none;
}

/* Control Panel Styles */
.description-controls {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 8px;
  padding: 8px 0;
  border-top: 1px solid #e5e7eb;
  gap: 16px;
}

.control-group {
  display: flex;
  align-items: center;
  gap: 8px;
}

.control-label {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 0.875rem;
  color: #374151;
  cursor: pointer;
  user-select: none;
}

.control-checkbox {
  width: 16px;
  height: 16px;
  border: 1px solid #d1d5db;
  border-radius: 3px;
  cursor: pointer;
  accent-color: #3b82f6;
}

.control-btn {
  display: flex;
  align-items: center;
  gap: 4px;
  padding: 4px 8px;
  border: 1px solid #d1d5db;
  border-radius: 4px;
  background: white;
  color: #6b7280;
  font-size: 0.75rem;
  cursor: pointer;
  transition: all 0.2s;
}

.control-btn:hover {
  background: #f9fafb;
  border-color: #9ca3af;
  color: #374151;
}

.clear-btn:hover {
  background: #fef2f2;
  border-color: #fca5a5;
  color: #dc2626;
}
</style>
