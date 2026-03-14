<template>
  <q-dialog v-model="isOpen" persistent maximized transition-show="slide-up" transition-hide="slide-down">
    <q-card class="bg-grey-1">
      <!-- Header -->
      <div class="q-pa-md" style="background: linear-gradient(135deg, #1976D2 0%, #2196F3 100%);">
        <div class="row items-center text-white">
          <q-avatar size="36px" color="white" text-color="primary" class="q-mr-md">
            <q-icon name="auto_awesome" size="24px" />
          </q-avatar>
          <div class="col">
            <div class="text-h6">AI Table of Contents Parser</div>
            <div class="text-subtitle2" style="opacity: 0.9;">Extract lessons and topics automatically from book text</div>
          </div>
          <q-btn flat round dense icon="close" color="white" @click="isOpen = false" />
        </div>
      </div>

      <q-card-section class="q-pa-lg">
        <div class="max-w-4xl mx-auto q-gutter-y-md">
          <!-- Step Indicators -->
          <q-stepper v-model="step" flat bordered class="rounded-borders bg-white">
            <q-step :name="1" title="Paste Text" icon="edit_note" :done="step > 1">
              <div class="q-pa-md">
                <p class="text-body2 text-grey-8 q-mb-md">
                  Paste the raw text of the book's Table of Contents below. You can copy this from a PDF or any document.
                </p>
                <q-input
                  v-model="rawText"
                  type="textarea"
                  outlined
                  rows="12"
                  placeholder="e.g. Unit 1: Fractions\nLesson 1-1: What are fractions? Page 5\nLesson 1-2: Adding fractions Page 12..."
                  autofocus
                />
                <div class="row justify-end q-mt-md">
                  <q-btn
                    unelevated
                    color="primary"
                    label="Generate AI Prompt"
                    :disabled="!rawText.trim()"
                    @click="generatePrompt"
                    icon="bolt"
                  />
                </div>
              </div>
            </q-step>

            <q-step :name="2" title="Run AI" icon="smart_toy" :done="step > 2">
              <div class="q-pa-md">
                <div class="bg-blue-50 q-pa-md rounded-borders border-blue-200 q-mb-md">
                  <div class="text-weight-bold text-primary q-mb-xs">Instruction</div>
                  <p class="text-caption q-ma-none">
                    1. Copy the prompt below.<br>
                    2. Paste it into <strong>Gemini</strong> or <strong>ChatGPT</strong>.<br>
                    3. Copy the <strong>JSON response</strong> they provide and come back here.
                  </p>
                </div>

                <q-input
                  v-model="generatedPrompt"
                  type="textarea"
                  outlined
                  readonly
                  label="AI Prompt"
                  class="bg-grey-1"
                >
                  <template v-slot:append>
                    <q-btn flat round color="primary" icon="content_copy" @click="copyPrompt">
                      <q-tooltip>Copy to clipboard</q-tooltip>
                    </q-btn>
                  </template>
                </q-input>

                <div class="row items-center q-mt-lg q-gutter-md">
                  <q-btn outline color="primary" icon="open_in_new" label="Open Gemini" @click="openLink('https://gemini.google.com/')" />
                  <q-btn outline color="primary" icon="open_in_new" label="Open ChatGPT" @click="openLink('https://chatgpt.com/')" />
                  <q-separator vertical inset />
                  <q-btn unelevated color="primary" label="Paste AI Response" icon="content_paste" @click="step = 3" />
                </div>
                
                <div class="row q-mt-md">
                  <q-btn flat label="Back" icon="arrow_back" @click="step = 1" />
                </div>
              </div>
            </q-step>

            <q-step :name="3" title="Review & Save" icon="fact_check">
              <div class="q-pa-md">
                <p class="text-body2 text-grey-8 q-mb-md">Paste the JSON response from the AI tool here.</p>
                <q-input
                  v-model="aiResponse"
                  type="textarea"
                  outlined
                  rows="10"
                  placeholder='{ "lessons": [ ... ] }'
                  @update:model-value="validateResponse"
                />

                <div v-if="parseError" class="bg-red-1 text-negative q-pa-sm q-mt-md rounded-borders text-caption">
                  {{ parseError }}
                </div>

                <div v-if="parsedLessons.length > 0" class="q-mt-lg">
                  <div class="text-weight-bold q-mb-sm">Preview ({{ parsedLessons.length }} lessons found)</div>
                  <q-list bordered separator class="rounded-borders overflow-hidden">
                    <q-item v-for="(lesson, idx) in parsedLessons.slice(0, 5)" :key="idx">
                      <q-item-section side>{{ lesson.lesson_number }}</q-item-section>
                      <q-item-section>
                        <q-item-label>{{ lesson.lesson_title }}</q-item-label>
                        <q-item-label caption v-if="lesson.page_number">Page {{ lesson.page_number }}</q-item-label>
                      </q-item-section>
                      <q-item-section side>
                        <q-chip size="xs" :label="lesson.type" dense />
                      </q-item-section>
                    </q-item>
                    <q-item v-if="parsedLessons.length > 5" class="text-center bg-grey-1">
                      <q-item-section class="text-caption text-grey-7">... and {{ parsedLessons.length - 5 }} more</q-item-section>
                    </q-item>
                  </q-list>
                </div>

                <div class="row justify-between q-mt-lg">
                  <q-btn flat label="Back" icon="arrow_back" @click="step = 2" />
                  <q-btn 
                    unelevated 
                    color="positive" 
                    label="Import Lessons" 
                    icon="check" 
                    :disabled="parsedLessons.length === 0"
                    @click="handleImport"
                  />
                </div>
              </div>
            </q-step>
          </q-stepper>
        </div>
      </q-card-section>
    </q-card>
  </q-dialog>
</template>

<script setup>
import { ref } from 'vue';
import { useQuasar, copyToClipboard } from 'quasar';
import { generateToCPrompt, parseToCResponse, validateToCResponse } from '@/Utils/tocPrompts';

const $q = useQuasar();
const emit = defineEmits(['lessons-parsed']);

const props = defineProps({
  bookInfo: {
    type: Object,
    required: true // { name, grade, subject }
  }
});

const isOpen = ref(false);
const step = ref(1);
const rawText = ref('');
const generatedPrompt = ref('');
const aiResponse = ref('');
const parsedLessons = ref([]);
const parseError = ref(null);

const open = () => {
  isOpen.value = true;
  step.value = 1;
  rawText.value = '';
  generatedPrompt.value = '';
  aiResponse.value = '';
  parsedLessons.value = [];
  parseError.value = null;
};

const generatePrompt = () => {
  generatedPrompt.value = generateToCPrompt(rawText.value, props.bookInfo);
  step.value = 2;
};

const copyPrompt = () => {
  copyToClipboard(generatedPrompt.value).then(() => {
    $q.notify({ type: 'positive', message: 'Prompt copied to clipboard!' });
  });
};

const openLink = (url) => {
  window.open(url, '_blank');
};

const validateResponse = (val) => {
  if (!val.trim()) {
    parsedLessons.value = [];
    parseError.value = null;
    return;
  }

  const result = parseToCResponse(val);
  if (!result.success) {
    parseError.value = result.error;
    parsedLessons.value = [];
    return;
  }

  const validation = validateToCResponse(result.data);
  if (!validation.valid) {
    parseError.value = validation.errors.join(', ');
    parsedLessons.value = [];
    return;
  }

  parseError.value = null;
  parsedLessons.value = result.data.lessons;
};

const handleImport = () => {
  emit('lessons-parsed', parsedLessons.value);
  isOpen.value = false;
};

defineExpose({ open });
</script>

<style scoped>
.rounded-borders {
  border-radius: 12px;
}
.border-blue-200 {
  border: 1px solid #BBDEFB;
}
</style>
