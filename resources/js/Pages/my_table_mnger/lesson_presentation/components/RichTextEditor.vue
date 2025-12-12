<template>
  <div class="rich-text-editor border border-gray-300 rounded-md shadow-sm overflow-hidden bg-white">
    <!-- Toolbar -->
    <div class="w-full border-b border-grey-4 q-pa-xs flex gap-2 items-center">
      <!-- Paste Options -->
      <q-btn-dropdown
        split
        @click="pasteFromClipboard('cursor')"
        :loading="isPasting"
        flat
        dense
        size="sm"
        color="grey-8"
        icon="content_paste"
        label="Paste"
        no-caps
      >
        <q-list>
          <q-item clickable v-close-popup @click="pasteFromClipboard('cursor')">
            <q-item-section>
              <q-item-label>Paste at Cursor</q-item-label>
            </q-item-section>
          </q-item>

          <q-item clickable v-close-popup @click="showSmartPaste = true">
            <q-item-section class="text-primary">
              <q-item-label>Smart Paste (Math/AI)...</q-item-label>
            </q-item-section>
          </q-item>

          <q-item clickable v-close-popup @click="pasteFromClipboard('replace')">
            <q-item-section class="text-red">
              <q-item-label>Replace All</q-item-label>
            </q-item-section>
          </q-item>
        </q-list>
        <q-tooltip>Paste Options</q-tooltip>
      </q-btn-dropdown>
      
      <q-separator vertical inset />
      
      <!-- Text Color -->
      <q-btn flat dense size="sm" color="grey-8" icon="format_color_text">
        <q-tooltip>Text Color</q-tooltip>
        <q-menu>
          <q-color
            no-header
            no-footer
            default-view="palette"
            @update:model-value="val => execCmd('foreColor', val)"
          />
        </q-menu>
      </q-btn>

      <!-- Background Color -->
      <q-btn flat dense size="sm" color="grey-8" icon="format_color_fill">
        <q-tooltip>Background Color</q-tooltip>
        <q-menu>
          <q-color
            no-header
            no-footer
            default-view="palette"
            @update:model-value="val => execCmd('hiliteColor', val)"
          />
        </q-menu>
      </q-btn>

      <q-separator vertical inset />

      <!-- Font Size -->
      <q-btn-dropdown flat dense size="sm" color="grey-8" icon="format_size" no-caps>
        <q-list>
          <q-item clickable v-close-popup @click="execCmd('fontSize', '1')">
            <q-item-section>Small</q-item-section>
          </q-item>
          <q-item clickable v-close-popup @click="execCmd('fontSize', '3')">
            <q-item-section>Normal</q-item-section>
          </q-item>
          <q-item clickable v-close-popup @click="execCmd('fontSize', '5')">
            <q-item-section>Large</q-item-section>
          </q-item>
          <q-item clickable v-close-popup @click="execCmd('fontSize', '7')">
            <q-item-section>Huge</q-item-section>
          </q-item>
        </q-list>
        <q-tooltip>Font Size</q-tooltip>
      </q-btn-dropdown>

      <q-separator vertical inset />
      
      <!-- Basic Formatting Buttons -->
      <q-btn @click="execCmd('bold')" flat dense size="sm" color="grey-8" icon="format_bold">
        <q-tooltip>Bold</q-tooltip>
      </q-btn>
      <q-btn @click="execCmd('italic')" flat dense size="sm" color="grey-8" icon="format_italic">
        <q-tooltip>Italic</q-tooltip>
      </q-btn>
      <q-btn @click="execCmd('underline')" flat dense size="sm" color="grey-8" icon="format_underlined">
        <q-tooltip>Underline</q-tooltip>
      </q-btn>

      <!-- Image Resizing Controls -->
      <template v-if="selectedImage">
        <q-separator vertical inset />
        <div class="flex items-center gap-1">
          <span class="text-xs text-grey-7 q-ml-xs">Img:</span>
          <q-btn-group flat dense>
            <q-btn label="25%" size="sm" dense color="grey-8" @click="resizeImage('25%')" />
            <q-btn label="50%" size="sm" dense color="grey-8" @click="resizeImage('50%')" />
            <q-btn label="100%" size="sm" dense color="grey-8" @click="resizeImage('100%')" />
          </q-btn-group>
          <q-btn
            :flat="!imageIsInline"
            :unelevated="imageIsInline"
            :color="imageIsInline ? 'primary' : 'grey-8'"
            size="sm"
            dense
            label="Inline"
            @click="toggleImageDisplay"
            class="q-ml-xs"
          >
            <q-tooltip>Toggle Inline/Block</q-tooltip>
          </q-btn>
        </div>
      </template>

      <q-separator vertical inset />

      <!-- Math Button -->
      <q-btn @click="showMathEditor = true" flat dense size="sm" color="grey-8" icon="functions">
        <q-tooltip>Insert Math Equation</q-tooltip>
      </q-btn>

      <!-- AI Assistant Button -->
      <q-btn @click="showAIAssistant = true" flat dense size="sm" color="accent" icon="psychology">
        <q-tooltip>AI Assistant</q-tooltip>
      </q-btn>
    </div>

    <!-- class="p-4 min-h-[100px] outline-none prose max-w-none" -->
    <!-- Editor Area -->
    <div
      ref="editor"
      class="max-h-96 overflow-y-auto markdown prose dark:prose-invert w-full break-words markdown-new-styling"
      contenteditable="true"
      @input="onInput"
      @blur="onInput"
      @mouseup="saveSelection"
      @keyup="saveSelection"
      @mouseleave="saveSelection"
      @click="handleEditorClick"
    ></div>
 
    <!-- Math Editor Dialog -->
    <q-dialog v-model="showMathEditor">
      <q-card style="min-width: 600px; max-width: 90vw;">
        <MathEditor @insert="insertMath" />
      </q-card>
    </q-dialog>

    <!-- Smart Paste Dialog -->
    <q-dialog v-model="showSmartPaste" full-width full-height>
      <q-card class="flex flex-col h-full overflow-hidden">
        <MathPastePreview @insert="insertSmartPaste" />
      </q-card>
    </q-dialog>

    <!-- AI Assistant Dialog -->
    <AIAssistant
      v-model="showAIAssistant"
      :context="getSelectedText()"
      :on-insert="insertAI"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, watch, nextTick } from 'vue';
import DOMPurify from 'dompurify';
import axios from 'axios';
import katex from 'katex';
import MathEditor from './MathEditor.vue';
import MathPastePreview from './MathPastePreview.vue';
import AIAssistant from '@/Components/AI/AIAssistant.vue';

const props = defineProps({
  modelValue: {
    type: String,
    default: ''
  }
});

const emit = defineEmits(['update:modelValue']);
const editor = ref(null);
const isPasting = ref(false);
const currentSelection = ref(null);
const selectedImage = ref(null);
const imageWidth = ref('');
const imageIsInline = ref(true);
const showMathEditor = ref(false);
const showAIAssistant = ref(false);
const showSmartPaste = ref(false);

// Sync modelValue with editor content
watch(() => props.modelValue, (newValue) => {
  if (editor.value && newValue !== editor.value.innerHTML) {
    editor.value.innerHTML = newValue;
  }
});

onMounted(() => {
  if (editor.value) {
    editor.value.innerHTML = props.modelValue;
  }
});

const onInput = () => {
  if (editor.value) {
    emit('update:modelValue', editor.value.innerHTML);
  }
};

const saveSelection = () => {
  const sel = window.getSelection();
  if (sel.getRangeAt && sel.rangeCount) {
    const range = sel.getRangeAt(0);
    // Check if the selection is within our editor
    if (editor.value && editor.value.contains(range.commonAncestorContainer)) {
      currentSelection.value = range;
    }
  }
};

const restoreSelection = () => {
  if (currentSelection.value) {
    const sel = window.getSelection();
    sel.removeAllRanges();
    sel.addRange(currentSelection.value);
  } else if (editor.value) {
    // Fallback: focus editor if no selection saved
    editor.value.focus();
  }
};

const handleEditorClick = (e) => {
  if (e.target.tagName === 'IMG') {
    selectedImage.value = e.target;
    imageWidth.value = e.target.style.width || 'auto';
    imageIsInline.value = e.target.style.display !== 'block';
  } else {
    selectedImage.value = null;
    imageWidth.value = '';
  }
  saveSelection();
};

const resizeImage = (width) => {
  if (selectedImage.value) {
    selectedImage.value.style.width = width;
    selectedImage.value.style.height = 'auto'; // Maintain aspect ratio
    imageWidth.value = width;
    onInput(); // Save changes
  }
};

const toggleImageDisplay = () => {
  if (selectedImage.value) {
    imageIsInline.value = !imageIsInline.value;
    if (imageIsInline.value) {
      selectedImage.value.style.display = 'inline';
      selectedImage.value.style.margin = '';
    } else {
      selectedImage.value.style.display = 'block';
      selectedImage.value.style.margin = '0 auto'; // Center block images by default
    }
    onInput();
  }
};

const execCmd = (command, value = null) => {
  restoreSelection();
  document.execCommand(command, false, value);
  saveSelection(); // Update selection after command (e.g. if text changed)
  onInput(); // Update model after command
};

const insertMath = (html) => {
  restoreSelection();
  document.execCommand('insertHTML', false, html);
  showMathEditor.value = false;
  saveSelection();
  onInput();
};

const insertAI = (text) => {
  restoreSelection();
  const html = text.replace(/\n/g, '<br>');
  document.execCommand('insertHTML', false, html);
  showAIAssistant.value = false;
  saveSelection();
  onInput();
};

const insertSmartPaste = (html) => {
  restoreSelection();
  document.execCommand('insertHTML', false, html);
  showSmartPaste.value = false;
  saveSelection();
  onInput();
};

const getSelectedText = () => {
  const sel = window.getSelection();
  if (sel && sel.rangeCount > 0) {
    return sel.toString();
  }
  return '';
};

const convertImageToBase64 = async (url) => {
  try {
    // Use our backend proxy to avoid CORS issues
    const response = await axios.post(route('lesson-presentation.proxy-image'), { url });
    return response.data.base64;
  } catch (error) {
    console.error('Failed to convert image:', url, error);
    return null;
  }
};

const pasteFromClipboard = async (mode = 'cursor') => {
  isPasting.value = true;
  
  // Focus editor first
  if (editor.value) {
    editor.value.focus();
    
    if (mode === 'replace') {
      editor.value.innerHTML = '';
      // Reset cursor to start
      const range = document.createRange();
      const sel = window.getSelection();
      range.setStart(editor.value, 0);
      range.collapse(true);
      sel.removeAllRanges();
      sel.addRange(range);
      saveSelection();
    } else if (mode === 'end') {
      // Move cursor to end
      const range = document.createRange();
      const sel = window.getSelection();
      range.selectNodeContents(editor.value);
      range.collapse(false);
      sel.removeAllRanges();
      sel.addRange(range);
      saveSelection();
    } else {
      // For 'cursor' mode, try to restore selection if we have one
      restoreSelection();
    }
  }

  try {
    const items = await navigator.clipboard.read();
    
    for (const item of items) {
      // Handle HTML content
      if (item.types.includes('text/html')) {
        const blob = await item.getType('text/html');
        const html = await blob.text();
        
        // Parse HTML to manipulate images
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        const images = doc.getElementsByTagName('img');
        
        // Convert external images to Base64
        const conversionPromises = Array.from(images).map(async (img) => {
          const src = img.getAttribute('src');
          if (src && (src.startsWith('http://') || src.startsWith('https://'))) {
            const base64 = await convertImageToBase64(src);
            if (base64) {
              img.setAttribute('src', base64);
            }
          }
        });
        
        await Promise.all(conversionPromises);
        
        // Sanitize the processed HTML
        const cleanHtml = DOMPurify.sanitize(doc.body.innerHTML, {
          ADD_TAGS: ['img'],
          ADD_ATTR: ['src', 'style', 'width', 'height', 'alt', 'class']
        });
        
        // Insert HTML at cursor or append
        document.execCommand('insertHTML', false, cleanHtml);
        onInput();
        return;
      }
      
      // Handle Image content (direct image copy)
      if (item.types.some(type => type.startsWith('image/'))) {
        const blob = await item.getType(item.types.find(type => type.startsWith('image/')));
        const reader = new FileReader();
        reader.onload = (e) => {
          const imgHtml = `<img src="${e.target.result}" style="max-width: 100%;" />`;
          const cleanHtml = DOMPurify.sanitize(imgHtml, {
             ADD_TAGS: ['img'],
             ADD_ATTR: ['src', 'style']
          });
          document.execCommand('insertHTML', false, cleanHtml);
          onInput();
        };
        reader.readAsDataURL(blob);
        return;
      }
      
      // Fallback to text
      if (item.types.includes('text/plain')) {
        const blob = await item.getType('text/plain');
        const text = await blob.text();
        // Sanitize text just in case, though insertText is usually safe
        document.execCommand('insertText', false, text);
        onInput();
        return;
      }
    }
  } catch (err) {
    console.error('Failed to read clipboard contents: ', err);
    // Fallback for browsers that don't support Clipboard API fully or if permission denied
    try {
        const text = await navigator.clipboard.readText();
        document.execCommand('insertText', false, text);
        onInput();
    } catch (e) {
        alert('Could not paste from clipboard. Please use Ctrl+V.');
    }
  } finally {
    isPasting.value = false;
  }
};


</script>

<style scoped>
.prose:empty:before {
  content: 'Enter text here...';
  color: #9ca3af;
  pointer-events: none;
}
</style>
