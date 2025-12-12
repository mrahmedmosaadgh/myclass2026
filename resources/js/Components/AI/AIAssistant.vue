<template>
  <q-dialog v-model="show" persistent>
    <q-card style="min-width: 600px; max-width: 90vw;">
      <q-card-section>
        <div class="flex items-center justify-between">
          <div>
            <div class="text-h6">AI Assistant</div>
            <div class="text-caption text-grey">Ask AI to help with your content</div>
          </div>
          <div class="flex gap-2">
            <q-btn flat dense icon="auto_awesome" color="purple" @click="showPromptBuilder = true">
              <q-tooltip>Quick Prompt Builder</q-tooltip>
            </q-btn>
            <q-select
              v-model="selectedModel"
              :options="modelOptions"
              label="AI Model"
              dense
              outlined
              emit-value
              map-options
              style="min-width: 150px"
            >
              <template v-slot:prepend>
                <q-icon name="settings" />
              </template>
            </q-select>
          </div>
        </div>
      </q-card-section>

      <q-card-section class="q-pt-none">
        <!-- Prompt Input -->
        <q-input
          v-model="prompt"
          filled
          type="textarea"
          rows="4"
          label="Your question or request"
          placeholder="e.g., Improve this text, Generate 3 similar problems, Explain this concept..."
          :disable="loading"
          autofocus
        />

        <!-- AI Response -->
        <div v-if="result" class="q-mt-md">
          <div class="text-subtitle2 text-grey-7 q-mb-xs">AI Response:</div>
          <q-card flat bordered class="q-pa-md bg-grey-1">
            <div v-html="formattedResult" class="markdown prose"></div>
          </q-card>
        </div>

        <!-- Error Display -->
        <q-banner v-if="error" class="bg-negative text-white q-mt-md" rounded>
          <template v-slot:avatar>
            <q-icon name="error" />
          </template>
          {{ error }}
        </q-banner>
      </q-card-section>

      <q-card-actions align="right">
        <q-btn flat label="Cancel" color="grey" @click="handleClose" :disable="loading" />
        <q-btn
          v-if="result"
          flat
          label="Copy"
          color="primary"
          icon="content_copy"
          @click="copyResult"
          :disable="loading"
        />
        <q-btn
          v-if="result"
          flat
          label="Fix Delimiters"
          color="secondary"
          icon="code"
          @click="fixDelimiters"
          :disable="loading"
        >
          <q-tooltip>Convert \(...\) to $...$</q-tooltip>
        </q-btn>
        <q-btn
          v-if="result"
          flat
          label="Render Math"
          color="accent"
          icon="functions"
          @click="renderMath"
          :disable="loading"
        >
          <q-tooltip>Render LaTeX with KaTeX</q-tooltip>
        </q-btn>
        <q-btn
          v-if="result && onInsert"
          unelevated
          label="Insert"
          color="primary"
          icon="add"
          @click="handleInsert"
          :disable="loading"
        />
        <q-btn
          unelevated
          label="Ask AI"
          color="accent"
          icon="psychology"
          @click="handleAsk"
          :loading="loading"
          :disable="!prompt.trim()"
        />
      </q-card-actions>
    </q-card>
  </q-dialog>

  <!-- Prompt Builder Dialog -->
  <q-dialog v-model="showPromptBuilder">
    <PromptBuilder @close="showPromptBuilder = false" @use-prompt="handlePromptBuilderUse" />
  </q-dialog>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useAI } from '@/composables/useAI';
import { useQuasar } from 'quasar';
import katex from 'katex';
import PromptBuilder from './PromptBuilder.vue';

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false,
  },
  initialPrompt: {
    type: String,
    default: '',
  },
  context: {
    type: String,
    default: '',
  },
  onInsert: {
    type: Function,
    default: null,
  },
});

const emit = defineEmits(['update:modelValue', 'insert']);

const $q = useQuasar();
const { ask, loading, error, clearError } = useAI();

const show = computed({
  get: () => props.modelValue,
  set: (val) => emit('update:modelValue', val),
});

const prompt = ref(props.initialPrompt || '');
const result = ref('');
const selectedModel = ref(localStorage.getItem('ai_model_preference') || 'github');
const showPromptBuilder = ref(false);

const modelOptions = [
  { label: 'GitHub (GPT-4o) - FREE', value: 'github' },
  { label: 'DeepSeek', value: 'deepseek' },
  { label: 'Gemini', value: 'gemini' },
];

// Format result with line breaks
const formattedResult = computed(() => {
  return result.value.replace(/\n/g, '<br>');
});

const handleAsk = async () => {
  clearError();
  
  // Save model preference
  localStorage.setItem('ai_model_preference', selectedModel.value);
  
  // Add context if provided
  let fullPrompt = prompt.value;
  if (props.context) {
    fullPrompt = `Context:\n${props.context}\n\nRequest:\n${prompt.value}`;
  }

  const response = await ask(fullPrompt, { model: selectedModel.value });
  if (response) {
    result.value = response;
  }
};

const copyResult = () => {
  navigator.clipboard.writeText(result.value).then(() => {
    $q.notify({
      type: 'positive',
      message: 'Copied to clipboard!',
      position: 'top',
      timeout: 1500,
    });
  });
};

const fixDelimiters = () => {
  let fixed = result.value;

  // Convert LaTeX inline math: \(...\) to $...$
  fixed = fixed.replace(/\\\((.*?)\\\)/g, '$$$1$$');
  
  // Convert LaTeX display math: \[...\] to $$...$$
  fixed = fixed.replace(/\\\[(.*?)\\\]/g, '$$$$$$1$$$$');
  
  // Convert markdown headers to HTML
  fixed = fixed.replace(/^### (.*?)$/gm, '<h3>$1</h3>');
  fixed = fixed.replace(/^## (.*?)$/gm, '<h2>$1</h2>');
  fixed = fixed.replace(/^# (.*?)$/gm, '<h1>$1</h1>');
  
  // Convert markdown bold
  fixed = fixed.replace(/\*\*(.*?)\*\*/g, '<b>$1</b>');
  
  // Convert markdown italic
  fixed = fixed.replace(/\*(.*?)\*/g, '<i>$1</i>');
  
  // Remove horizontal rules (---)
  fixed = fixed.replace(/^---+$/gm, '');
  
  // Clean up extra blank lines
  fixed = fixed.replace(/\n{3,}/g, '\n\n');
  
  result.value = fixed.trim();
  
  $q.notify({
    type: 'positive',
    message: 'Delimiters fixed!',
    position: 'top',
    timeout: 1500,
  });
};

const renderMath = () => {
  let text = result.value;

  // First convert \(...\) and \[...\] to $ delimiters
  text = text.replace(/\\\((.*?)\\\)/g, '$$$1$$');
  text = text.replace(/\\\[(.*?)\\\]/g, '$$$$$$1$$$$');

  // Convert markdown formatting
  text = text.replace(/^### (.*?)$/gm, '<h3>$1</h3>');
  text = text.replace(/^## (.*?)$/gm, '<h2>$1</h2>');
  text = text.replace(/^# (.*?)$/gm, '<h1>$1</h1>');
  text = text.replace(/\*\*(.*?)\*\*/g, '<b>$1</b>');
  text = text.replace(/\*(.*?)\*/g, '<i>$1</i>');
  text = text.replace(/^---+$/gm, '');

  // Render math with KaTeX
  const regex = /\$\$([\s\S]*?)\$\$|\$([\s\S]*?)\$/g;
  let lastIndex = 0;
  let html = '';
  let match;

  while ((match = regex.exec(text)) !== null) {
    // Add text before match
    html += text.slice(lastIndex, match.index).replace(/\n/g, '<br>');

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

  // Add remaining text
  html += text.slice(lastIndex).replace(/\n/g, '<br>');

  result.value = html;

  $q.notify({
    type: 'positive',
    message: 'Math rendered!',
    position: 'top',
    timeout: 1500,
  });
};

const handleInsert = () => {
  if (props.onInsert) {
    props.onInsert(result.value);
  }
  emit('insert', result.value);
  handleClose();
};

const handleClose = () => {
  show.value = false;
  // Reset after animation
  setTimeout(() => {
    prompt.value = '';
    result.value = '';
    clearError();
  }, 300);
};

const handlePromptBuilderUse = (generatedPrompt) => {
  prompt.value = generatedPrompt;
  showPromptBuilder.value = false;
  // Optionally auto-submit
  // handleAsk();
};
</script>

<style scoped>
.markdown {
  white-space: pre-wrap;
  word-wrap: break-word;
}
</style>
