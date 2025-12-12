<template>
  <div class="simple-rich-text-editor border border-gray-300 rounded-md shadow-sm overflow-hidden bg-white">
    <!-- Toolbar -->
    <div class="w-full border-b border-gray-200 p-2 flex gap-1 items-center bg-gray-50">
      <!-- Cut Button -->
      <q-btn
        flat
        dense
        size="sm"
        icon="content_cut"
        @click="cutContent"
        :disable="!hasSelection"
        color="orange-8"
      >
        <q-tooltip>Cut (Ctrl+X)</q-tooltip>
      </q-btn>

      <!-- Copy Button -->
      <q-btn
        flat
        dense
        size="sm"
        icon="content_copy"
        @click="copyContent"
        :disable="!hasSelection"
        color="blue-8"
      >
        <q-tooltip>Copy (Ctrl+C)</q-tooltip>
      </q-btn>

      <!-- Paste Button -->
      <q-btn
        flat
        dense
        size="sm"
        icon="content_paste"
        @click="pasteFromClipboard"
        :loading="isPasting"
        color="green-8"
      >
        <q-tooltip>Paste (Ctrl+V)</q-tooltip>
      </q-btn>

      <q-separator vertical class="mx-1" />

      <!-- Clear Button -->
      <q-btn
        flat
        dense
        size="sm"
        icon="clear_all"
        @click="clearContent"
        color="red-8"
      >
        <q-tooltip>Clear All</q-tooltip>
      </q-btn>

      <!-- Select All Button -->
      <q-btn
        flat
        dense
        size="sm"
        icon="select_all"
        @click="selectAll"
        color="purple-8"
      >
        <q-tooltip>Select All (Ctrl+A)</q-tooltip>
      </q-btn>
    </div>

    <!-- Editor Area -->
    <div
      ref="editor"
      class="p-4 min-h-[150px] max-h-96 overflow-y-auto prose max-w-none outline-none"
      contenteditable="true"
      @input="onInput"
      @blur="onInput"
      @paste="handlePaste"
      @keydown="handleKeydown"
      @mouseup="updateSelection"
      @keyup="updateSelection"
    ></div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import { useQuasar } from 'quasar';
import DOMPurify from 'dompurify';

const $q = useQuasar();

const props = defineProps({
  modelValue: {
    type: String,
    default: ''
  }
});

const emit = defineEmits(['update:modelValue']);

const editor = ref(null);
const isPasting = ref(false);
const hasSelection = ref(false);

// Configure DOMPurify for safety
const sanitizeConfig = {
  ALLOWED_TAGS: ['p', 'br', 'strong', 'b', 'em', 'i', 'u', 'span', 'div', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'ul', 'ol', 'li', 'img'],
  ALLOWED_ATTR: ['src', 'alt', 'style', 'class'],
  FORBID_TAGS: ['script', 'object', 'embed', 'form', 'input', 'button'],
  FORBID_ATTR: ['onclick', 'onload', 'onerror', 'onmouseover', 'onfocus', 'onblur', 'onchange', 'onsubmit'],
  ADD_TAGS: ['img'],
  ADD_ATTR: ['src', 'alt']
};

// Sync modelValue with editor content
watch(() => props.modelValue, (newValue) => {
  if (editor.value && newValue !== editor.value.innerHTML) {
    editor.value.innerHTML = sanitizeContent(newValue);
  }
});

onMounted(() => {
  if (editor.value) {
    editor.value.innerHTML = sanitizeContent(props.modelValue);
  }
});

const sanitizeContent = (content) => {
  return DOMPurify.sanitize(content, sanitizeConfig);
};

const onInput = () => {
  if (editor.value) {
    const sanitized = sanitizeContent(editor.value.innerHTML);
    emit('update:modelValue', sanitized);
  }
};

const updateSelection = () => {
  const selection = window.getSelection();
  hasSelection.value = selection && !selection.isCollapsed;
};

const getSelectedContent = () => {
  const selection = window.getSelection();
  if (selection.rangeCount > 0) {
    const range = selection.getRangeAt(0);
    const container = document.createElement('div');
    container.appendChild(range.cloneContents());
    return container.innerHTML;
  }
  return '';
};

const cutContent = async () => {
  if (!hasSelection.value) return;
  
  try {
    const selectedHtml = getSelectedContent();
    const selectedText = window.getSelection().toString();
    
    // Copy to clipboard
    await navigator.clipboard.write([
      new ClipboardItem({
        'text/html': new Blob([selectedHtml], { type: 'text/html' }),
        'text/plain': new Blob([selectedText], { type: 'text/plain' })
      })
    ]);
    
    // Delete selected content
    document.execCommand('delete');
    onInput();
    
    $q.notify({
      message: 'Content cut to clipboard',
      color: 'orange',
      icon: 'content_cut',
      timeout: 1000
    });
  } catch (error) {
    console.error('Cut failed:', error);
    $q.notify({
      message: 'Cut failed',
      color: 'negative',
      icon: 'error'
    });
  }
};

const copyContent = async () => {
  if (!hasSelection.value) return;
  
  try {
    const selectedHtml = getSelectedContent();
    const selectedText = window.getSelection().toString();
    
    await navigator.clipboard.write([
      new ClipboardItem({
        'text/html': new Blob([selectedHtml], { type: 'text/html' }),
        'text/plain': new Blob([selectedText], { type: 'text/plain' })
      })
    ]);
    
    $q.notify({
      message: 'Content copied to clipboard',
      color: 'blue',
      icon: 'content_copy',
      timeout: 1000
    });
  } catch (error) {
    console.error('Copy failed:', error);
    $q.notify({
      message: 'Copy failed',
      color: 'negative',
      icon: 'error'
    });
  }
};

const convertImageToBase64 = async (src) => {
  try {
    const response = await fetch(src);
    const blob = await response.blob();
    return new Promise((resolve) => {
      const reader = new FileReader();
      reader.onload = () => resolve(reader.result);
      reader.readAsDataURL(blob);
    });
  } catch (error) {
    console.warn('Failed to convert image to base64:', error);
    return null;
  }
};

const processImagesInContent = async (htmlContent) => {
  const parser = new DOMParser();
  const doc = parser.parseFromString(htmlContent, 'text/html');
  const images = doc.getElementsByTagName('img');
  
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
  return doc.body.innerHTML;
};

const handlePaste = async (event) => {
  event.preventDefault();
  isPasting.value = true;

  try {
    const clipboardData = event.clipboardData || window.clipboardData;
    
    // Handle HTML content
    let htmlContent = clipboardData.getData('text/html');
    if (htmlContent) {
      // Process images to base64
      htmlContent = await processImagesInContent(htmlContent);
      
      // Sanitize content
      const cleanHtml = sanitizeContent(htmlContent);
      
      // Insert at cursor
      document.execCommand('insertHTML', false, cleanHtml);
      onInput();
      return;
    }
    
    // Handle plain text
    const textContent = clipboardData.getData('text/plain');
    if (textContent) {
      document.execCommand('insertText', false, textContent);
      onInput();
    }
  } catch (error) {
    console.error('Paste failed:', error);
  } finally {
    isPasting.value = false;
  }
};

const pasteFromClipboard = async () => {
  isPasting.value = true;
  
  try {
    const items = await navigator.clipboard.read();
    
    for (const item of items) {
      // Handle HTML content
      if (item.types.includes('text/html')) {
        const blob = await item.getType('text/html');
        let html = await blob.text();
        
        // Process images to base64
        html = await processImagesInContent(html);
        
        // Sanitize content
        const cleanHtml = sanitizeContent(html);
        
        // Insert HTML
        document.execCommand('insertHTML', false, cleanHtml);
        onInput();
        
        $q.notify({
          message: 'Content pasted successfully',
          color: 'green',
          icon: 'content_paste',
          timeout: 1000
        });
        return;
      }
      
      // Handle images
      if (item.types.some(type => type.startsWith('image/'))) {
        const blob = await item.getType(item.types.find(type => type.startsWith('image/')));
        const reader = new FileReader();
        reader.onload = (e) => {
          const imgHtml = `<img src="${e.target.result}" style="max-width: 100%;" alt="Pasted image" />`;
          const cleanHtml = sanitizeContent(imgHtml);
          document.execCommand('insertHTML', false, cleanHtml);
          onInput();
          
          $q.notify({
            message: 'Image pasted successfully',
            color: 'green',
            icon: 'image',
            timeout: 1000
          });
        };
        reader.readAsDataURL(blob);
        return;
      }
      
      // Handle plain text
      if (item.types.includes('text/plain')) {
        const blob = await item.getType('text/plain');
        const text = await blob.text();
        document.execCommand('insertText', false, text);
        onInput();
        
        $q.notify({
          message: 'Text pasted successfully',
          color: 'green',
          icon: 'content_paste',
          timeout: 1000
        });
        return;
      }
    }
  } catch (err) {
    console.error('Failed to read clipboard:', err);
    $q.notify({
      message: 'Paste failed. Try using Ctrl+V',
      color: 'negative',
      icon: 'error'
    });
  } finally {
    isPasting.value = false;
  }
};

const clearContent = () => {
  if (editor.value) {
    editor.value.innerHTML = '';
    emit('update:modelValue', '');
    
    $q.notify({
      message: 'Content cleared',
      color: 'red',
      icon: 'clear_all',
      timeout: 1000
    });
  }
};

const selectAll = () => {
  if (editor.value) {
    const range = document.createRange();
    range.selectNodeContents(editor.value);
    const selection = window.getSelection();
    selection.removeAllRanges();
    selection.addRange(range);
    updateSelection();
  }
};

const handleKeydown = (event) => {
  // Handle keyboard shortcuts
  if (event.ctrlKey || event.metaKey) {
    switch (event.key) {
      case 'x':
        event.preventDefault();
        cutContent();
        break;
      case 'c':
        event.preventDefault();
        copyContent();
        break;
      case 'v':
        // Let the default paste handler work
        break;
      case 'a':
        event.preventDefault();
        selectAll();
        break;
    }
  }
};
</script>

<style scoped>
.simple-rich-text-editor {
  font-family: system-ui, -apple-system, sans-serif;
}

.prose :deep(img) {
  max-width: 100%;
  height: auto;
  border-radius: 4px;
}

.prose :deep(p) {
  margin: 0.5em 0;
}

.prose :deep(h1), 
.prose :deep(h2), 
.prose :deep(h3), 
.prose :deep(h4), 
.prose :deep(h5), 
.prose :deep(h6) {
  margin: 1em 0 0.5em 0;
  font-weight: bold;
}

.prose :deep(ul), 
.prose :deep(ol) {
  margin: 0.5em 0;
  padding-left: 1.5em;
}
</style>
