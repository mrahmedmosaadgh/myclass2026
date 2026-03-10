<template>
  <div class="math-editor q-pa-md" style="min-width: 700px; max-width: 95vw;">
    <div class="text-h6 q-mb-md">Insert Math Equation</div>

    <div class="row q-col-gutter-md">
      <!-- Left Side: Controls & Symbols -->
      <div class="col-12 col-md-7">
        <!-- Tabs -->
        <q-tabs
          v-model="activeTab"
          dense
          class="text-grey"
          active-color="primary"
          indicator-color="primary"
          align="justify"
          narrow-indicator
        >
          <q-tab name="basic" label="Basic" />
          <q-tab name="advanced" label="Advanced" />
          <q-tab name="preview" label="Text Preview" />
        </q-tabs>

        <q-separator />

        <q-tab-panels v-model="activeTab" animated class="bg-grey-1" style="height: 250px; overflow-y: auto;">
          <!-- Basic Symbols -->
          <q-tab-panel name="basic" class="q-pa-sm">
            <div class="flex gap-2 flex-wrap justify-center">
              <q-btn
                v-for="symbol in basicSymbols"
                :key="symbol.name"
                outline
                color="primary"
                size="md"
                class="math-btn bg-white"
                @click="handleSymbolClick(symbol)"
              >
                <span v-html="renderStaticMath(symbol.label || symbol.latex)"></span>
                <q-tooltip>{{ symbol.name }}</q-tooltip>
              </q-btn>
            </div>
          </q-tab-panel>

          <!-- Advanced Symbols -->
          <q-tab-panel name="advanced" class="q-pa-sm">
             <div class="flex gap-2 flex-wrap justify-center">
              <q-btn
                v-for="symbol in advancedSymbols"
                :key="symbol.name"
                outline
                color="primary"
                size="md"
                class="math-btn bg-white"
                @click="handleSymbolClick(symbol)"
              >
                <span v-html="renderStaticMath(symbol.label || symbol.latex)"></span>
                <q-tooltip>{{ symbol.name }}</q-tooltip>
              </q-btn>
            </div>
          </q-tab-panel>

          <!-- Text Preview Tab -->
          <q-tab-panel name="preview" class="q-pa-sm">
            <div class="column q-gutter-sm">
              <div class="row justify-between items-center">
                <div class="text-caption text-grey">Paste text with LaTeX (e.g., Problem: $\frac{1}{2}$)</div>
                <q-btn
                  flat
                  dense
                  color="primary"
                  icon="content_paste"
                  label="Paste from Clipboard"
                  size="sm"
                  @click="pasteFromClipboard"
                />
                <q-btn
                  flat
                  dense
                  color="secondary"
                  icon="auto_fix_high"
                  label="Fix Markdown"
                  size="sm"
                  @click="fixMarkdown"
                >
                  <q-tooltip>Convert **bold** to \textbf{bold}, *italic* to \textit{italic}</q-tooltip>
                </q-btn>
                <q-btn
                  flat
                  dense
                  color="accent"
                  icon="content_paste_go"
                  label="Paste & Fix"
                  size="sm"
                  @click="pasteAndFix"
                >
                  <q-tooltip>Paste from clipboard and auto-format math and lists</q-tooltip>
                </q-btn>
              </div>
              <q-input
                v-model="pastedText"
                filled
                type="textarea"
                rows="3"
                placeholder="Paste your text here..."
                class="text-sm"
              />
              
              <!-- Find/Replace Controls -->
              <div class="row q-gutter-sm">
                <q-input
                  v-model="findText"
                  dense
                  outlined
                  placeholder="Find"
                  class="col"
                  style="font-size: 12px;"
                />
                <q-input
                  v-model="replaceText"
                  dense
                  outlined
                  placeholder="Replace with"
                  class="col"
                  ></q-input>
                </div>
                <!-- <div v-else class="text-grey-5 text-caption">Preview will appear here</div> -->

              <q-btn
                unelevated
                color="primary"
                label="Use This Text"
                size="sm"
                @click="usePastedText"
                :disable="!pastedText"
              />
            </div>
          </q-tab-panel>
        </q-tab-panels>

        <!-- Editing Controls -->
        <div class="row q-gutter-sm justify-center q-mt-md">
            <q-btn flat dense color="grey-8" icon="space_bar" label="Space" @click="insertSymbol('\\;')" />
            <q-btn flat dense color="grey-8" icon="backspace" label="Backspace" @click="backspace" />
            <q-btn flat dense color="negative" icon="delete" label="Clear" @click="clear" />
            <q-btn flat dense color="primary" label="Replace" @click="replaceSelection" />
            <q-btn flat dense color="primary" label="Add" @click="addLineBreak" />
        </div>
      </div>

      <!-- Right Side: Preview & Result -->
      <div class="col-12 col-md-5 column">
        <div class="text-subtitle2 q-mb-xs text-grey-7">LaTeX Code</div>
        <q-input
          v-model="latexInput"
          filled
          type="textarea"
          rows="3"
          ref="inputRef"
          class="q-mb-md"
          input-class="font-mono text-sm"
          hint="Use $...$ for inline math, e.g., Problem: $\frac{1}{2}$"
        />

        <div class="text-subtitle2 q-mb-xs text-grey-7">Preview</div>
        <div class="preview-box border rounded p-4 bg-grey-1 flex items-center justify-center col-grow relative-position" style="min-height: 150px;">
          <div v-if="latexInput" v-html="renderedMath" class="text-h5 text-center"></div>
          <div v-else class="text-grey-5">Preview</div>
        </div>
        
        <div class="row justify-end q-mt-md gap-2">
            <q-btn flat label="Cancel" color="grey" v-close-popup />
            <q-btn unelevated label="Insert Equation" color="primary" @click="insertEquation" :disable="!latexInput" />
        </div>
      </div>
    </div>

    <!-- Smart Input Dialog -->
    <q-dialog v-model="showSmartInput" persistent>
      <q-card style="min-width: 350px">
        <q-card-section>
          <div class="text-h6">{{ currentSymbol?.name }}</div>
          <div class="text-caption text-grey">Enter values for the variables</div>
        </q-card-section>

        <q-card-section class="q-pt-none">
          <div v-for="(variable, index) in currentSymbol?.vars" :key="index" class="q-mb-md">
            <q-input
              v-model="variableValues[index]"
              filled
              dense
              :autofocus="index === 0"
              @keyup.enter="index === currentSymbol.vars.length - 1 ? confirmSmartInput() : null"
            />
          </div>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Cancel" color="grey" v-close-popup />
          <q-btn flat label="Insert" color="primary" @click="confirmSmartInput" />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </div>
</template>

<script setup>
import { ref, computed, nextTick } from 'vue';
import katex from 'katex';

const emit = defineEmits(['insert']);

const latexInput = ref('');
const inputRef = ref(null);
const activeTab = ref('basic');
const showSmartInput = ref(false);
const currentSymbol = ref(null);
const variableValues = ref([]);
const pastedText = ref('');
const findText = ref('');
const replaceText = ref('');

const basicSymbols = [
  { name: 'Fraction', template: '\\frac{{0}}{{1}}', vars: ['Numerator', 'Denominator'], label: '\\frac{a}{b}' },
  { name: 'Square Root', template: '\\sqrt{{0}}', vars: ['Value'], label: '\\sqrt{x}' },
  { name: 'Power', template: '{{0}}^{{1}}', vars: ['Base', 'Exponent'], label: 'x^2' },
  { name: 'Subscript', template: '{{0}}_{{1}}', vars: ['Base', 'Subscript'], label: 'x_1' },
  { name: 'Mixed Number', template: '{{0}}\\:\\frac{{1}}{{2}}', vars: ['Whole', 'Numerator', 'Denominator'], label: '1\\:\\frac{1}{2}' },
  { name: 'Multiplication', latex: '\\times' },
  { name: 'Division', latex: '\\div' },
  { name: 'Plus/Minus', latex: '\\pm' },
  { name: 'Parentheses', template: '({{0}})', vars: ['Content'], label: '(x)' },
];

const advancedSymbols = [
  { name: 'Infinity', latex: '\\infty' },
  { name: 'Pi', latex: '\\pi' },
  { name: 'Alpha', latex: '\\alpha' },
  { name: 'Beta', latex: '\\beta' },
  { name: 'Theta', latex: '\\theta' },
  { name: 'Delta', latex: '\\Delta' },
  { name: 'Less/Equal', latex: '\\leq' },
  { name: 'Greater/Equal', latex: '\\geq' },
  { name: 'Not Equal', latex: '\\neq' },
  { name: 'Approx', latex: '\\approx' },
  { name: 'Sum', template: '\\sum_{{0}}^{{1}}', vars: ['From', 'To'], label: '\\sum' },
  { name: 'Integral', template: '\\int_{{0}}^{{1}}', vars: ['Lower', 'Upper'], label: '\\int' },
  { name: 'Square Root N', template: '\\sqrt[{0}]{{1}}', vars: ['Degree', 'Value'], label: '\\sqrt[n]{x}' },
];

// Reusing logic from MathRenderer for consistency
const parseAndRender = (content) => {
  if (!content) return '';
  
  // If content doesn't have delimiters but has latex-like chars, maybe wrap it?
  // For now, let's stick to explicit delimiters for mixed text, 
  // OR if it's purely math (no spaces, or just math symbols), treat as display math.
  // But the user wants "Problem: $...$"
  
  // Regex for $...$ or $$...$$
  const regex = /\$\$([\s\S]*?)\$\$|\$([\s\S]*?)\$/g;
  
  let lastIndex = 0;
  let html = '';
  let match;
  let hasMath = false;

  while ((match = regex.exec(content)) !== null) {
    hasMath = true;
    const textBefore = content.slice(lastIndex, match.index);
    html += escapeHtml(textBefore)
      .replace(/\n/g, '<br>')
      .replace(/&lt;(\/?(strong|em|b|i|u|code))&gt;/g, '<$1>');

    const displayMath = match[1];
    const inlineMath = match[2];

    try {
      if (displayMath) {
        html += katex.renderToString(displayMath, { throwOnError: false, displayMode: true });
      } else if (inlineMath) {
        html += katex.renderToString(inlineMath, { throwOnError: false, displayMode: false });
      }
    } catch (e) {
      html += `<span class="text-red-500">Error: ${e.message}</span>`;
    }

    lastIndex = regex.lastIndex;
  }

  html += escapeHtml(content.slice(lastIndex))
    .replace(/\n/g, '<br>')
    .replace(/&lt;(\/?(strong|em|b|i|u|code))&gt;/g, '<$1>');

  // Fallback: If no delimiters found, try to render as pure math if it looks like math
  // This preserves old behavior where user just types "\frac{1}{2}"
  if (!hasMath && content.trim().length > 0) {
      try {
          // Check if it renders without error
          return katex.renderToString(content, { throwOnError: false, displayMode: true });
      } catch (e) {
          // If error, just return text
          return escapeHtml(content)
            .replace(/\n/g, '<br>')
            .replace(/&lt;(\/?(strong|em|b|i|u|code))&gt;/g, '<$1>');
      }
  }

  return html;
};

const escapeHtml = (text) => {
  return text
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
};

const renderedMath = computed(() => {
  return parseAndRender(latexInput.value);
});

const renderStaticMath = (latex) => {
  try {
    return katex.renderToString(latex, {
      throwOnError: false,
      displayMode: false,
    });
  } catch (e) {
    console.error("Error rendering math:", e);
    return escapeHtml(latex);
  }
};

const insertEquation = () => {
  // If we insert pure HTML, it becomes part of the editor content.
  // The RichTextEditor will handle it.
  // We might want to wrap it in a container to mark it as "math-content" if we want to edit it later.
  // But for now, simple insertion.
  
  // Note: We need to be careful. If we insert "Problem: <span class='katex'>...</span>", 
  // the editor will display it.
  // But if we want it to be editable as a block later, we'd need more complex logic.
  // Given the user just wants to "convert text... to natural math look", inserting the rendered HTML is correct.
  
  const html = parseAndRender(latexInput.value);
  emit('insert', html + '&nbsp;');
  latexInput.value = '';
};

const performReplace = () => {
  if (!findText.value) return;
  
  // Replace all occurrences of findText with replaceText
  pastedText.value = pastedText.value.replaceAll(findText.value, replaceText.value);
  
  // Clear find/replace fields after operation
  findText.value = '';
  replaceText.value = '';
};

const usePastedText = () => {
  // Copy pasted text to the main input
  latexInput.value = pastedText.value;
  
  // Switch to basic tab to show the result
  activeTab.value = 'basic';
  
  // Clear pasted text
  pastedText.value = '';
};
  const pasteFromClipboard = async () => {
    try {
      const text = await navigator.clipboard.readText();
      pastedText.value = text;
    } catch (err) {
      console.error('Failed to read clipboard contents: ', err);
      // Fallback: focus the input so user can paste manually
      // We don't have a ref for the pastedText input, but we can try to find it or just notify
      // For now, simple error log is fine as this is an enhancement
    }
  };

  const fixMarkdown = () => {
    if (!pastedText.value) return;
    
    // Regex for $...$ or $$...$$
    const regex = /\$\$([\s\S]*?)\$\$|\$([\s\S]*?)\$/g;
    
    let lastIndex = 0;
    let newText = '';
    let match;
    const content = pastedText.value;

    while ((match = regex.exec(content)) !== null) {
      const textBefore = content.slice(lastIndex, match.index);
      // Apply markdown to textBefore
      newText += convertMarkdownToHtml(textBefore);

      // Append the math block unchanged
      newText += match[0];

      lastIndex = regex.lastIndex;
    }

    // Append remaining text
    newText += convertMarkdownToHtml(content.slice(lastIndex));

    pastedText.value = newText;
  };

  const pasteAndFix = async () => {
    try {
      let text = await navigator.clipboard.readText();
      
      // 1. Fix Markdown (Bold, Italic, Header, Code)
      text = convertMarkdownToHtml(text);
      
      // 2. Fix Math
      // Wrap parenthesized groups that look like math: ( ... \ ... ) or ( ... ^ ... )
      // We use a heuristic: if it contains \ or ^ or _ or { } and is inside ( ), wrap in $
      text = text.replace(/(\((?:[^)(]+|\((?:[^)(]+|\([^)(]*\))*\))*\))/g, (match) => {
        if (/[\^_{}\\]/.test(match) || /[+\-*/=].*[+\-*/=]/.test(match)) {
             return `$${match}$`;
        }
        return match;
      });

      // Wrap isolated LaTeX commands like \frac, \sqrt if not already wrapped
      text = text.replace(/(\\[a-zA-Z]+(?:\{.*?\})*)/g, (match) => {
          // Simple check if it's already wrapped is hard with regex replace callback without context
          // But we can assume if we are running this, the text is "clean" but missing delimiters
          // We'll skip if it looks like it's inside $...$ (naive check)
          return `$${match}$`;
      });
      
      // Cleanup double wrapping $$...$$ -> $...$ if we accidentally did it
      text = text.replace(/\$\$+/g, '$');
      // Restore display math $$...$$
      text = text.replace(/\$\$\$/g, '$$$'); // if we had $$$ -> $$

      pastedText.value = text;
    } catch (err) {
      console.error('Failed to paste and fix: ', err);
    }
  };

  const convertMarkdownToHtml = (text) => {
    let res = text;
    
    // List items: A. (math) -> A. $(math)$
    res = res.replace(/^([A-Z0-9]+\.)\s+([(\d\\].*)$/gm, '$1 $$$2$$');

    res = res
      .replace(/(\*\*|__)(.*?)\1/g, '<strong>$2</strong>')
      .replace(/(\*|_)(.*?)\1/g, '<em>$2</em>')
      .replace(/^#+\s+(.*)$/gm, '<strong>$1</strong>')
      .replace(/`([^`]+)`/g, '<code>$1</code>');
      
    return res;
  };

  // Replace selected text with a line break (\n)
  const replaceSelection = () => {
    const input = inputRef.value?.$el.querySelector('textarea');
    if (input) {
      const start = input.selectionStart;
      const end = input.selectionEnd;
      const text = latexInput.value;
      const before = text.substring(0, start);
      const after = text.substring(end);
      latexInput.value = before + '\n' + after;
      nextTick(() => {
        input.focus();
        const pos = start + 1; // after newline
        input.selectionStart = input.selectionEnd = pos;
      });
    } else {
      // No textarea reference, just append newline
      latexInput.value += '\n';
    }
  };

  // Add a line break after the current selection/cursor
  const addLineBreak = () => {
    const input = inputRef.value?.$el.querySelector('textarea');
    if (input) {
      const start = input.selectionStart;
      const end = input.selectionEnd;
      const text = latexInput.value;
      const before = text.substring(0, end);
      const after = text.substring(end);
      latexInput.value = before + '\n' + after;
      nextTick(() => {
        input.focus();
        const pos = end + 1; // after inserted newline
        input.selectionStart = input.selectionEnd = pos;
      });
    } else {
      latexInput.value += '\n';
    }
  };

  const handleSymbolClick = (symbol) => {
    if (symbol.vars) {
      currentSymbol.value = symbol;
      variableValues.value = new Array(symbol.vars.length).fill('');
      
      // Check for selected text to pre-fill the first variable
      const input = inputRef.value?.$el.querySelector('textarea');
      if (input) {
        const start = input.selectionStart;
        const end = input.selectionEnd;
        if (start !== end) {
          const selectedText = latexInput.value.substring(start, end);
          variableValues.value[0] = selectedText;
        }
      }

      showSmartInput.value = true;
    } else {
      insertSymbol(symbol.latex || symbol.template);
    }
  };

  const confirmSmartInput = () => {
    if (!currentSymbol.value) return;
    
    let result = currentSymbol.value.template;
    currentSymbol.value.vars.forEach((v, i) => {
      result = result.replace(`{{${i}}}`, variableValues.value[i] || '');
    });
    
    insertSymbol(result);
    showSmartInput.value = false;
  };

  const insertSymbol = (symbol) => {
    const input = inputRef.value?.$el.querySelector('textarea');
    if (input) {
      const start = input.selectionStart;
      const end = input.selectionEnd;
      const text = latexInput.value;
      const before = text.substring(0, start);
      const after = text.substring(end);
      latexInput.value = before + symbol + after;
      
      nextTick(() => {
        input.focus();
        // If we replaced a selection with a smart input result, we might want to place cursor at end
        // Or if it's a simple symbol, place after symbol
        input.selectionStart = input.selectionEnd = start + symbol.length;
      });
    } else {
      latexInput.value += symbol;
    }
  };
</script>

<style scoped>
.math-btn {
  min-width: 45px;
  min-height: 45px;
}
.preview-box {
  border: 1px solid #e0e0e0;
}
.preview-box-small {
  border: 1px solid #e0e0e0;
}
</style>
