<template>
  <div class="math-paste-preview flex flex-col h-full bg-gray-50 p-4 rounded-xl">
    <!-- Header -->
    <div class="flex justify-between items-center mb-4">
      <div class="flex items-center gap-2">
        <q-icon name="auto_fix_high" size="md" color="primary" />
        <div class="text-h6 text-primary font-bold">Smart Paste Studio</div>
      </div>
      <div class="flex gap-2 items-center">
        <!-- Copy AI Prompt Button -->
        <q-btn
          flat
          dense
          no-caps
          :icon="aiPromptCopied ? 'check_circle' : 'smart_toy'"
          :label="aiPromptCopied ? 'Copied!' : 'Copy AI Prompt'"
          :color="aiPromptCopied ? 'positive' : 'deep-purple'"
          class="ai-prompt-btn rounded-lg px-3 text-xs font-semibold"
          @click="copyAiPrompt"
        >
          <q-tooltip anchor="bottom middle" self="top middle" :offset="[0, 6]" class="text-xs">
            Copies a prompt to tell AI to fix the text for math rendering ($...$)
          </q-tooltip>
        </q-btn>
        <q-btn flat round icon="close" v-close-popup color="grey-7" />
      </div>
    </div>

    <div class="flex flex-1 gap-6 overflow-hidden min-h-0">
      <!-- Sidebar: Filters -->
      <div class="w-64 relative flex flex-col max-h-full  overflow-scroll gap-4 bg-white p-4 rounded-xl shadow-sm overflow-y-auto">
        <div class="text-subtitle2 absolute  font-bold text-gray-700 uppercase tracking-wide">Magic Filters</div>

        
        <div class="flex flex-col gap-2   ">
          <q-btn 
            unelevated 
            :label="allSelected ? 'Deselect All' : 'Select All'" 
            :color="allSelected ? 'grey-7' : 'primary'"
            size="sm"
            class="mb-2 rounded-lg"
            @click="toggleAll"
          />
          <q-btn 
            unelevated 
            label="Reset Defaults" 
            color="grey-5"
            size="sm"
            class="mb-2 rounded-lg"
            @click="resetDefaults"
          />

          <q-separator class="mb-2" />

          <div class="text-xs font-bold text-gray-500 uppercase mt-2 mb-1">Text Formatting</div>
          <q-toggle v-model="filters.markdown" label="Basic (Headers, Bold, Italic)" color="purple" dense class="text-sm" />
          <q-toggle v-model="filters.extended" label="Extended (Strike, Highlight)" color="deep-purple" dense class="text-sm" />
          
          <div class="text-xs font-bold text-gray-500 uppercase mt-2 mb-1">Structure</div>
          <q-toggle v-model="filters.lists" label="Lists & Blockquotes" color="teal" dense class="text-sm" />
          <q-toggle v-model="filters.tables" label="Tables (GitHub Style)" color="cyan" dense class="text-sm" />
          <q-toggle v-model="filters.separators" label="Horizontal Lines" color="cyan" dense class="text-sm" />
          <q-toggle v-model="filters.pageBreaks" label="Page Breaks (---page---)" color="red" dense class="text-sm" />
          <q-toggle v-model="filters.newlines" label="Preserve Newlines" color="indigo" dense class="text-sm" />
          <q-toggle v-model="filters.codeBlocks" label="Code Blocks (```)" color="blue-grey" dense class="text-sm" />
          <q-toggle v-model="filters.links" label="Clickable Links" color="light-blue" dense class="text-sm" />
          <q-toggle v-model="filters.images" label="Images (![alt](url))" color="teal-6" dense class="text-sm" />

          <div class="text-xs font-bold text-gray-500 uppercase mt-2 mb-1">Math & Data</div>
          <q-toggle v-model="filters.math" label="Render Math ($...$)" color="blue" dense class="text-sm" />
          <q-toggle v-model="filters.currency" label="Currency & Dollar Fixes" color="pink" dense class="text-sm" />
          
          <div class="text-xs font-bold text-gray-500 uppercase mt-2 mb-1">Fixes & Conversions</div>
          <q-toggle v-model="filters.blanks" label="Convert Blanks (___)" color="orange" dense class="text-sm" />
          <q-toggle v-model="filters.quotes" label="Smart Quotes" color="amber" dense class="text-sm" />
          <q-toggle v-model="filters.trimSpaces" label="Trim Extra Spaces" color="green" dense class="text-sm" />
          <q-toggle v-model="filters.fixArabicNumbers" label="Fix Arabic Numbers to Western" color="deep-orange" dense class="text-sm" />

          <q-separator class="my-2" />
          <div class="text-xs font-bold text-gray-500 uppercase mb-2">Custom Replace (Grade 4 Friendly)</div>
          <div class="flex flex-col gap-2 bg-yellow-50 p-2 rounded-lg border border-yellow-100">
            <q-input v-model="findText" dense outlined label="Find Text" bg-color="white" class="text-sm" placeholder="e.g. difficult word" />
            <q-input v-model="replaceText" dense outlined label="Replace With" bg-color="white" class="text-sm" placeholder="e.g. easy word" />
          </div>
        </div>

        <div class="mt-auto bg-blue-50 p-3 rounded-lg text-xs text-blue-800">
          <q-icon name="info" class="mr-1" />
          Tip: Toggle filters to see how they affect your text in real-time!
        </div>
      </div>

      <!-- Main Content: Input & Preview -->
      <div class="flex-1 flex gap-4  min-h-0 h-[calc(100vh-200px)] overflow-scroll ">
        <!-- Input Area -->
        <div class="flex-1 flex  flex-col bg-white rounded-xl shadow-sm overflow-hidden border border-gray-200 min-h-0">
          <div class="bg-gray-100 px-4 fixed top-0 z-50 py-2 border-b border-gray-200 flex justify-between items-center">
            <span class="font-bold bg-white px-4 py-2 text-gray-700 text-sm">Raw Text (Input)</span>
            <q-btn flat dense size="sm" icon="content_paste" label="Paste Clipboard" color="primary" @click="pasteFromClipboard" />
          </div>
          <textarea
            ref="textareaRef"
            v-model="rawText"
            @paste="handleRawPaste"
            class="flex-1 p-4 w-full resize-none outline-none font-mono text-sm text-gray-600 overflow-auto"
            placeholder="Paste your text or images here..."
          ></textarea>
        </div>

        <!-- Preview Area -->
        <div class="flex-1 flex flex-col bg-white rounded-xl shadow-sm overflow-hidden border border-gray-200 min-h-0">
          <div class="bg-gray-100 px-4 py-2 border-b border-gray-200 flex justify-between items-center">
            <span class="font-bold text-gray-700 text-sm">Live Preview (Output)</span>
            <q-badge color="green" v-if="rawText">Ready to Insert</q-badge>
          </div>
          <div 
            class="preview-content flex-1 p-4 overflow-y-auto prose max-w-none"
            v-html="previewHtml"
          ></div>
        </div>
      </div>
    </div>

    <!-- Footer Actions -->
    <div class="flex justify-end gap-3 mt-4 pt-2 border-t border-gray-200">
      <q-btn flat label="Cancel" v-close-popup color="grey-8" class="rounded-lg px-4" />
      <q-btn 
        unelevated 
        color="primary" 
        icon="check" 
        label="Insert Content" 
        class="rounded-lg px-6"
        @click="insert" 
      />
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import katex from 'katex';
import DOMPurify from 'dompurify';

const props = defineProps({
  initialText: {
    type: String,
    default: ''
  }
});

const emit = defineEmits(['insert']);

const aiPromptCopied = ref(false);

const AI_MATH_PROMPT = `You are a KaTeX math formatter. Rewrite last text so that every math expression is properly formatted for KaTeX rendering using $...$ (inline) or $$...$$ (display block).

Rules:
- Inline: $expression$ | Block: $$expression$$
- Fractions: \\frac{a}{b} | Powers: x^2, x^{10} | Roots: \\sqrt{x}
- Greek: \\pi, \\alpha, \\theta, \\beta | Dot: \\cdot | Sub: a_1
- DO NOT change non-math text. Return ONLY the formatted text.`;

const copyAiPrompt = async () => {
  try {
    await navigator.clipboard.writeText(AI_MATH_PROMPT);
    aiPromptCopied.value = true;
    setTimeout(() => { aiPromptCopied.value = false; }, 2500);
  } catch (e) {
    console.error('Failed to copy AI prompt:', e);
  }
};

const rawText = ref('');
const findText = ref('');
const replaceText = ref('');
const textareaRef = ref(null);

const filters = ref({
  // Text Formatting
  markdown: true,
  extended: true,
  
  // Structure
  lists: true,
  tables: true,
  separators: true,
  pageBreaks: true,
  newlines: true,
  codeBlocks: false,
  links: false,
  images: true,
  
  // Math & Data
  math: true,
  currency: true,
  
  // Fixes & Conversions
  blanks: true,
  quotes: false,
  trimSpaces: true,
  fixArabicNumbers: false
});

const defaultFilters = {
  // Text Formatting
  markdown: true,
  extended: true,
  
  // Structure
  lists: true,
  tables: true,
  separators: true,
  pageBreaks: true,
  newlines: true,
  codeBlocks: false,
  links: false,
  images: true,
  
  // Math & Data
  math: true,
  currency: true,
  
  // Fixes & Conversions
  blanks: true,
  quotes: false,
  trimSpaces: true,
  fixArabicNumbers: false
};

const allSelected = computed(() => {
  return Object.values(filters.value).every(v => v);
});

const toggleAll = () => {
  const targetState = !allSelected.value;
  for (const key in filters.value) {
    filters.value[key] = targetState;
  }
};

const resetDefaults = () => {
  filters.value = { ...defaultFilters };
  findText.value = '';
  replaceText.value = '';
};

const pasteFromClipboard = async () => {
  try {
    const text = await navigator.clipboard.readText();
    rawText.value += text;
  } catch (e) {
    console.log('Clipboard access denied or empty');
  }
};

const handleRawPaste = async (e) => {
  const items = e.clipboardData?.items;
  if (!items) return;

  let hasImage = false;

  // We loop to see if there are any images
  for (let i = 0; i < items.length; i++) {
    if (items[i].type.indexOf('image') !== -1) {
      hasImage = true;
      const file = items[i].getAsFile();
      if (!file) continue;
      
      const reader = new FileReader();
      reader.onload = (event) => {
        const base64Url = event.target.result;
        const imgMarkdown = `\n![Pasted Image](${base64Url})\n`;
        // Insert image at cursor
        insertAtCursor(imgMarkdown);
      };
      reader.readAsDataURL(file);
    }
  }

  // If there was an image, we DO NOT prevent default if there's also text.
  // The browser will naturally paste the text, and our FileReader will insert the image markdown asynchronously.
};

const insertAtCursor = (text) => {
  const el = textareaRef.value;
  if (!el) {
    rawText.value += text;
    return;
  }
  const startPos = el.selectionStart;
  const endPos = el.selectionEnd;
  
  rawText.value = rawText.value.substring(0, startPos) + 
                  text + 
                  rawText.value.substring(endPos);
                  
  setTimeout(() => {
    el.selectionStart = el.selectionEnd = startPos + text.length;
    el.focus();
  }, 10);
};

onMounted(() => {
  if (props.initialText) {
    rawText.value = props.initialText;
  } else {
    pasteFromClipboard();
  }
});

const applyFilters = (text) => {
  let res = text;

  // 1. Basic Markdown (Headers, Bold, Italic)
  if (filters.value.markdown) {
    res = res.replace(/^####\s+(.*)$/gm, '<h4>$1</h4>');
    res = res.replace(/^###\s+(.*)$/gm, '<h3>$1</h3>');
    res = res.replace(/^##\s+(.*)$/gm, '<h2>$1</h2>');
    res = res.replace(/^#\s+(.*)$/gm, '<h1>$1</h1>');
    res = res.replace(/\*\*(.*?)\*\*/g, '<b>$1</b>');
    res = res.replace(/\*(.*?)\*/g, '<i>$1</i>'); // Italic
    // res = res.replace(/_(.*?)_/g, '<i>$1</i>'); // Italic underscore
  }

  // 2. Extended Markdown (Strike, Highlight)
  if (filters.value.extended) {
    res = res.replace(/~~(.*?)~~/g, '<s>$1</s>'); // Strikethrough
    res = res.replace(/==(.*?)==/g, '<mark>$1</mark>'); // Highlight
  }

  // 3. Separators & Page Breaks
  if (filters.value.separators) {
    res = res.replace(/^(\*\*\*|---)$/gm, '<hr class="my-4 border-t border-gray-300">');
  }
  
  if (filters.value.pageBreaks) {
    res = res.replace(/^---page---$/gm, '<div class="page-break" style="page-break-before: always; border-top: 2px dashed #f44336; padding-top: 20px; margin-top: 40px; position: relative;"><span style="position: absolute; top: -12px; left: 50%; transform: translateX(-50%); background: white; padding: 0 10px; color: #f44336; font-size: 10px; font-weight: bold; letter-spacing: 2px;">PAGE BREAK</span></div>');
  }

  // 4. Blanks (Convert underscores to blank spaces)
  if (filters.value.blanks) {
    res = res.replace(/_{3,}/g, '<u class="px-4">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</u>');
  }
  // 5. Lists & Blockquotes
  if (filters.value.lists) {
    // Blockquotes
    res = res.replace(/^>\s+(.*)$/gm, '<blockquote class="border-l-4 border-gray-300 pl-4 italic my-2">$1</blockquote>');
    
    // Lists
    res = res.replace(/^(\s{4}|\t)\*\s+(.*)$/gm, '<div class="q-ml-md">&bull; $2</div>');
    res = res.replace(/^\*\s+(.*)$/gm, '<div>&bull; $1</div>');
    res = res.replace(/^(\d+\.)\s+(.*)$/gm, '<div><b>$1</b> $2</div>');
  }

  // 6. Tables (Basic)
  if (filters.value.tables) {
    const tableBlockRegex = /((?:^\|.*\|\r?\n?)+)/gm;
    res = res.replace(tableBlockRegex, (match) => {
      const rows = match.trim().split('\n');
      let tableHtml = '<table class="w-full border-collapse border border-gray-300 my-4">';
      rows.forEach((row, index) => {
        if (row.includes('---')) return;
        
        const cells = row.split('|').filter(c => c.trim() !== '');
        tableHtml += '<tr>';
        cells.forEach(cell => {
          const tag = index === 0 ? 'th' : 'td';
          const bg = index === 0 ? 'bg-gray-100' : '';
          tableHtml += `<${tag} class="border border-gray-300 p-2 ${bg}">${cell.trim()}</${tag}>`;
        });
        tableHtml += '</tr>';
      });
      tableHtml += '</table>';
      return tableHtml;
    });
  }

  // 7. Code Blocks
  if (filters.value.codeBlocks) {
    // Inline code: `code`
    res = res.replace(/`([^`]+)`/g, '<code class="bg-gray-100 px-2 py-1 rounded text-sm font-mono">$1</code>');
    
    // Multi-line code blocks: ```code```
    res = res.replace(/```([\s\S]*?)```/g, '<pre class="bg-gray-100 p-3 rounded-lg overflow-x-auto my-2"><code class="font-mono text-sm">$1</code></pre>');
  }

  // 8. Links & Images
  if (filters.value.images) {
    // Markdown images: ![alt](url)
    res = res.replace(/!\[([^\]]*)\]\(([^)]+)\)/g, '<img src="$2" alt="$1" style="max-width: 100%; height: auto; border-radius: 8px; margin: 10px 0; border: 1px solid #e2e8f0;" />');
  }
  
  if (filters.value.links) {
    // Markdown links: [text](url) - Note: Uses a negative lookbehind proxy so we don't match images again if both are enabled.
    res = res.replace(/(?<!!)\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2" class="text-blue-600 hover:underline" target="_blank" rel="noopener">$1</a>');
    
    // Auto-link URLs
    res = res.replace(/(?<!src=")(https?:\/\/[^\s<]+)/g, '<a href="$1" class="text-blue-600 hover:underline" target="_blank" rel="noopener">$1</a>');
  }

  // 9. Math Rendering (KaTeX) - Must run BEFORE currency so $...$ is preserved!
  if (filters.value.math) {
    // First, convert LaTeX \(...\) to $...$ for inline math
    res = res.replace(/\\\((.+?)\\\)/g, '$$$1$$');
    
    // Convert LaTeX \[...\] to $$...$$ for display math
    res = res.replace(/\\\[(.+?)\\\]/g, '$$$$$$1$$$$$$');
    
    // Render $$...$$ (display math) FIRST
    res = res.replace(/\$\$(.+?)\$\$/g, (match, mathContent) => {
      try {
        const html = katex.renderToString(mathContent, { throwOnError: false, displayMode: true });
        return html.replace(/\n/g, ' '); // Strip newlines to prevent `<br>` injection
      } catch (e) {
        return match;
      }
    });

    // Now render all $...$ (inline math)
    res = res.replace(/(?<!\\)\$((?:[^$]|\\.)+)(?<!\\)\$/g, (match, mathContent) => {
      try {
        const html = katex.renderToString(mathContent, { throwOnError: false, displayMode: false });
        return html.replace(/\n/g, ' '); // Strip newlines to prevent `<br>` injection
      } catch (e) {
        return match;
      }
    });
  }

  // 10. Currency & Dollar Fixes (Unified)
  if (filters.value.currency) {
    // Treat standalone currency: $40 or $38.50 -> &dollar;40 or &dollar;38.50
    // Only match $ when directly followed by a digit (no space)
    res = res.replace(/\$(\d+(?:[.,]\d+)?)/g, '&dollar;$1');
    
    // Fix trailing dollar: 40$ -> 40&dollar;
    res = res.replace(/(\d+(?:[.,]\d+)?)\$/g, '$1&dollar;');
  }

  // 10. Smart Quotes
  if (filters.value.quotes) {
    // Convert straight quotes to smart quotes
    res = res.replace(/"([^"]+)"/g, '\u201c$1\u201d');
    res = res.replace(/'([^']+)'/g, '\u2018$1\u2019');
  }

  // 11. Trim Extra Spaces
  if (filters.value.trimSpaces) {
    // Remove multiple spaces
    res = res.replace(/ {2,}/g, ' ');
    // Trim leading/trailing spaces on each line WITHOUT removing newlines
    res = res.replace(/^[ \t]+|[ \t]+$/gm, '');
  }

  // 12. Fix Arabic Numbers
  if (filters.value.fixArabicNumbers) {
    const arabicToWestern = {
      '٠': '0', '١': '1', '٢': '2', '٣': '3', '٤': '4',
      '٥': '5', '٦': '6', '٧': '7', '٨': '8', '٩': '9'
    };
    res = res.replace(/[٠-٩]/g, (match) => arabicToWestern[match]);
  }

  // 13. Custom Find & Replace (Grade 4 Friendly)
  if (findText.value) {
    try {
      // Global case-insensitive replace
      const regex = new RegExp(findText.value.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), 'gi');
      res = res.replace(regex, replaceText.value);
    } catch (e) {
      // Fallback
    }
  }



  // 14. Preserve Newlines
  if (filters.value.newlines) {
    res = res.replace(/\n/g, '<br>');
  }

  return res;
};

const previewHtml = computed(() => {
  const html = applyFilters(rawText.value);
  // Allow data: URIs for Base64 pasted images
  return DOMPurify.sanitize(html, {
    ALLOWED_URI_REGEXP: /^(?:(?:(?:f|ht)tps?|mailto|tel|callto|cid|xmpp|data):|[^a-z]|[a-z+.\-]+(?:[^a-z+.\-:]|$))/i
  });
});

const insert = () => {
  emit('insert', previewHtml.value);
};
</script>

<style scoped>
.preview-content {
  font-family: 'Inter', sans-serif;
}
/* Custom scrollbar for a cleaner look */
::-webkit-scrollbar {
  width: 8px;
  height: 8px;
}
::-webkit-scrollbar-track {
  background: #f1f1f1; 
  border-radius: 4px;
}
::-webkit-scrollbar-thumb {
  background: #c1c1c1; 
  border-radius: 4px;
}
::-webkit-scrollbar-thumb:hover {
  background: #a8a8a8; 
}
</style>
