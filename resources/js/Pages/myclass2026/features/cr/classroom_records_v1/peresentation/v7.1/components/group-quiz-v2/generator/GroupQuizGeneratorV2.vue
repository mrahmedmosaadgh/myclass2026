<script setup>
import { ref } from 'vue';
import { useQuasar } from 'quasar';
import { useAIPaste } from '../../../composables/useAIPaste';
import { usePresentationStore } from '../../../stores/presentationStore';

import PromptBuilder from './PromptBuilder.vue';
import JsonPasteArea from './JsonPasteArea.vue';
import QuestionPreviewList from './QuestionPreviewList.vue';

const props = defineProps({
  modelValue: { type: Boolean, default: false }
});

const emit = defineEmits(['update:modelValue']);

const $q = useQuasar();
const presentation = usePresentationStore();
const { generateQuestionElements } = useAIPaste();

const step = ref(1);
const topic = ref(presentation.title || '');
const qCount = ref(3);
const difficulty = ref('Medium');
const extraInfo = ref('');
const jsonInput = ref('');
const parsedQuestions = ref([]);
const errorMessage = ref('');

function close() {
  emit('update:modelValue', false);
  resetForm();
}

function resetForm() {
  step.value = 1;
  parsedQuestions.value = [];
  jsonInput.value = '';
  errorMessage.value = '';
}

function buildPrompt() {
  const prompt = [
    'You are an expert teacher. Generate a JSON Array containing ' + qCount.value + ' Multiple Choice questions about ' + (topic.value || 'General Knowledge') + '.',
    'Difficulty Level: ' + difficulty.value + '.',
    extraInfo.value ? 'Additional Requirements: ' + extraInfo.value : '',
    '',
    'Use Markdown bolding "**" or "###" if needed. If you write math formulas, strictly wrap them in "\\\\(" and "\\\\)"',
    'IMPORTANT: Because this is JSON, YOU MUST DOUBLE-ESCAPE ALL LATEX BACKSLASHES!',
    'Return ONLY valid JSON format exactly like this:',
    '[',
    '  {',
    '    "question": "What is 5 + 5?",',
    '    "options": ["7", "10", "12", "15"],',
    '    "answer": "B) 10"',
    '  }',
    ']'
  ].join('\n');

  return prompt;
}

function copyPrompt() {
  const prompt = buildPrompt();
  navigator.clipboard.writeText(prompt);
  $q.notify({ type: 'positive', message: 'Prompt copied!', position: 'top' });
}

function parsePreview() {
  errorMessage.value = '';
  parsedQuestions.value = [];
  if (!jsonInput.value.trim()) {
    errorMessage.value = 'Please paste JSON output to preview.';
    return;
  }
  try {
    let raw = jsonInput.value.trim();
    if (raw.startsWith('```')) {
      raw = raw.replace(/^```[a-z]*\n/i, '').replace(/\n```$/i, '');
    }
    let fixed = '';
    for (let i = 0; i < raw.length; i++) {
      if (raw[i] === '\\') {
        if (i + 1 < raw.length) {
          const next = raw[i + 1];
          if (['"', '\\', 'n', 't', 'r'].includes(next)) {
            fixed += '\\' + next;
            i++;
          } else {
            fixed += '\\\\';
          }
        } else {
          fixed += '\\\\';
        }
      } else {
        fixed += raw[i];
      }
    }
    const data = JSON.parse(fixed);
    if (!Array.isArray(data)) throw new Error('Invalid structure. Must be a JSON Array.');
    parsedQuestions.value = data;
  } catch (err) {
    errorMessage.value = 'Invalid JSON: ' + err.message;
  }
}

async function pasteFromClipboard() {
  try {
    const text = await navigator.clipboard.readText();
    if (text) {
      jsonInput.value = text;
      parsePreview();
    }
  } catch (err) {
    $q.notify({ type: 'negative', message: 'Could not access clipboard', position: 'top' });
  }
}

function generateEmptyQuestions() {
  const empty = [];
  for (let i = 0; i < qCount.value; i++) {
    empty.push({
      question: 'New Question ' + (i + 1),
      options: ['Option A', 'Option B', 'Option C', 'Option D'],
      answer: 'A) Option A'
    });
  }
  generateQuestionElements(empty, 'new', 'v3');
  appendLeaderboard();
  close();
}

function appendLeaderboard() {
  presentation.addSlide();
  presentation.addElement({
    id: 'el-' + Date.now() + Math.random().toString(36).substr(2, 5),
    type: 'leaderboard',
    x: 60, y: 50, width: 900, height: 650, zIndex: 1,
    visibilityOption: 'always-visible', isVisible: true
  });
}

function submitToPresentation() {
  if (parsedQuestions.value.length === 0) return;
  generateQuestionElements(parsedQuestions.value, 'new', 'v3');
  appendLeaderboard();
  close();
}

function removeQuestion(index) {
  parsedQuestions.value.splice(index, 1);
}
</script>

<template>
  <q-dialog :model-value="modelValue" @update:model-value="$emit('update:modelValue', $event)" persistent maximized>
    <q-card class="generator-dialog">
      <q-bar class="bg-primary text-white">
        <q-icon name="emoji_events" />
        <div>Group Quiz Generator</div>
        <q-space />
        <q-btn dense flat icon="close" @click="close" />
      </q-bar>

      <q-stepper v-model="step" flat animated class="generator-stepper">
        <!-- Step 1: Configure -->
        <q-step :name="1" title="Configure" icon="settings" :done="step > 1">
          <PromptBuilder
            v-model:topic="topic"
            v-model:qCount="qCount"
            v-model:difficulty="difficulty"
            v-model:extraInfo="extraInfo"
            @copyPrompt="copyPrompt"
            @generateEmpty="generateEmptyQuestions"
          />
          <q-stepper-navigation class="q-mt-md">
            <q-btn color="primary" label="Next" @click="step = 2" unelevated />
          </q-stepper-navigation>
        </q-step>

        <!-- Step 2: Paste JSON -->
        <q-step :name="2" title="Paste AI Output" icon="content_paste" :done="step > 2">
          <JsonPasteArea
            v-model="jsonInput"
            :errorMessage="errorMessage"
            @preview="parsePreview"
            @pasteFromClipboard="pasteFromClipboard"
          />
          <q-stepper-navigation class="q-mt-md row q-gutter-sm">
            <q-btn flat color="grey-7" label="Back" @click="step = 1" />
            <q-btn color="primary" label="Next" @click="step = 3" :disable="parsedQuestions.length === 0" unelevated />
          </q-stepper-navigation>
        </q-step>

        <!-- Step 3: Preview & Inject -->
        <q-step :name="3" title="Preview & Add" icon="preview">
          <QuestionPreviewList
            :questions="parsedQuestions"
            @submit="submitToPresentation"
            @remove="removeQuestion"
          />
          <q-stepper-navigation class="q-mt-md row q-gutter-sm">
            <q-btn flat color="grey-7" label="Back" @click="step = 2" />
          </q-stepper-navigation>
        </q-step>
      </q-stepper>
    </q-card>
  </q-dialog>
</template>

<style scoped>
.generator-dialog {
  width: 100%;
  max-width: 800px;
  height: 90vh;
  max-height: 800px;
  display: flex;
  flex-direction: column;
}
.generator-stepper {
  flex: 1;
  display: flex;
  flex-direction: column;
}
.generator-stepper :deep(.q-stepper__content) {
  flex: 1;
  overflow: auto;
}
</style>
