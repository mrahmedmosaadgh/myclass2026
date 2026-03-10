<template>
  <q-dialog v-model="internalValue" persistent maximized transition-show="slide-up" transition-hide="slide-down">
    <q-card class="column text-dark relative-position bg-grey-1">
      <!-- Header -->
      <q-toolbar class="bg-white text-dark shadow-2 q-py-sm sticky-top" style="z-index: 10;">
        <q-btn flat round dense icon="close" v-close-popup class="text-grey-8" />
        <q-toolbar-title class="text-weight-bold text-h6 q-ml-sm">
          <q-icon name="auto_awesome" color="primary" class="q-mr-sm" size="sm"/>
          AI Semester & Calendar Setup
          <div class="text-caption text-grey-6 text-weight-regular q-mt-xs">
            Generate dates, vacations, and academic events for {{ yearName }}
          </div>
        </q-toolbar-title>
      </q-toolbar>

      <q-card-section class="col q-px-md q-py-lg scroll">
        <div class="max-w-4xl mx-auto w-full">
          <q-stepper
            v-model="step"
            ref="stepper"
            color="primary"
            animated
            header-nav
            flat
            bordered
            class="rounded-borders bg-white shadow-sm"
          >
            <!-- STEP 1: Configure Prompt -->
            <q-step :name="1" title="Configure" icon="settings" :done="step > 1">
              <div class="text-h6 q-mb-md">Tell AI about your school</div>
              
              <div class="row q-col-gutter-lg">
                <div class="col-12 col-md-6">
                  <q-select
                    v-model="config.country"
                    :options="['Saudi Arabia', 'Egypt', 'UAE', 'USA', 'UK', 'Other']"
                    label="Country / Region *"
                    outlined
                    hint="Helps AI suggest correct national holidays / weekends"
                    class="q-mb-md"
                  />
                  <q-select
                    v-model="config.schoolType"
                    :options="['Primary', 'Middle', 'High', 'Mixed/All Grades', 'University']"
                    label="School Level *"
                    outlined
                    class="q-mb-md"
                  />
                  <q-select
                    v-model="config.language"
                    :options="['English', 'Arabic', 'Bilingual (Both)']"
                    label="Content Language *"
                    outlined
                    class="q-mb-md"
                  />
                  <q-input
                    v-model.number="config.semesterCount"
                    type="number"
                    label="Number of Semesters *"
                    outlined
                    min="1"
                    max="4"
                    hint="Usually 2 or 3"
                    class="q-mb-md"
                  />
                </div>
                
                <div class="col-12 col-md-6">
                  <q-input
                    v-model="config.customInstructions"
                    type="textarea"
                    label="Additional Instructions (Optional)"
                    outlined
                    rows="6"
                    placeholder="e.g. 'Make sure to include a 2-week spring break in March' or 'Include mid-term exam dates'"
                    hint="Add specific dates or events you want the AI to include"
                  />
                  
                  <div class="q-mt-md">
                    <div class="text-subtitle2 text-grey-7 q-mb-sm">Quick Add:</div>
                    <div class="q-gutter-sm">
                      <q-btn size="sm" outline color="blue-grey" label="+ Mid-term exams" @click="addInstruction('Include mid-term exam periods')" />
                      <q-btn size="sm" outline color="blue-grey" label="+ Teacher prep days" @click="addInstruction('Include teacher preparation days before each semester')" />
                      <q-btn size="sm" outline color="blue-grey" label="+ Final exams" @click="addInstruction('Include final exam periods at the end of each semester')" />
                    </div>
                  </div>
                </div>
              </div>

              <q-stepper-navigation class="q-mt-lg">
                <q-btn @click="generatePrompt" color="primary" label="Generate Prompt" icon-right="arrow_forward" unelevated class="px-6 py-2" />
              </q-stepper-navigation>
            </q-step>

            <!-- STEP 2: Copy Prompt -->
            <q-step :name="2" title="Copy" icon="content_copy" :done="step > 2">
              <div class="text-h6 q-mb-md">Copy Prompt to AI</div>
              
              <q-banner class="bg-blue-50 text-blue-9 border-blue-200 border rounded q-mb-md q-pa-sm">
                <template v-slot:avatar>
                  <q-icon name="info" color="blue" />
                </template>
                Copy this exact prompt and paste it into ChatGPT, Claude, or Gemini. Ask the AI to generate the response, then copy its JSON output.
              </q-banner>

              <q-input
                v-model="generatedPrompt"
                type="textarea"
                readonly
                outlined
                rows="14"
                class="q-mb-md text-mono bg-grey-1"
                style="font-family: monospace; font-size: 13px;"
              />
              
              <q-stepper-navigation>
                <q-btn @click="copyPrompt" color="primary" label="Copy to Clipboard" icon="content_copy" unelevated class="q-mr-sm" />
                <q-btn @click="step = 3" color="secondary" label="Next Step" outline />
                <q-btn flat @click="step = 1" label="Back" class="q-ml-sm text-grey-7" />
              </q-stepper-navigation>
            </q-step>

            <!-- STEP 3: Paste Response -->
            <q-step :name="3" title="Paste" icon="content_paste" :done="step > 3">
              <div class="text-h6 q-mb-md">Paste AI Response</div>
              
              <q-input
                v-model="aiResponse"
                type="textarea"
                label="Paste AI Response (JSON format)"
                outlined
                rows="12"
                placeholder="{ ... }"
                class="q-mb-md font-mono"
              />

              <q-btn
                @click="pasteFromClipboard"
                color="primary"
                label="Paste from Clipboard"
                icon="content_paste"
                outline
                class="q-mb-lg"
              />

              <q-stepper-navigation>
                <q-btn @click="validateResponse" color="primary" label="Validate & Preview" icon-right="visibility" :loading="validating" unelevated />
                <q-btn flat @click="step = 2" label="Back" class="q-ml-sm text-grey-7" />
              </q-stepper-navigation>
            </q-step>

            <!-- STEP 4: Preview & Apply -->
            <q-step :name="4" title="Preview" icon="visibility">
              <div class="row items-center justify-between q-mb-md">
                <div class="text-h6">Preview Generated Plan</div>
              </div>

              <!-- Mode Selector -->
              <div class="row q-gutter-md q-mb-lg">
                <q-card
                  bordered flat
                  class="col cursor-pointer q-pa-md"
                  :class="applyMode === 'update' ? 'bg-blue-1 border-primary' : 'bg-white'"
                  @click="applyMode = 'update'"
                >
                  <div class="row items-center q-gutter-sm">
                    <q-radio v-model="applyMode" val="update" color="primary" />
                    <div>
                      <div class="text-weight-bold text-body2">Update Existing</div>
                      <div class="text-caption text-grey-6">Update semester dates and events. Existing calendar records are kept.</div>
                    </div>
                  </div>
                </q-card>
                <q-card
                  bordered flat
                  class="col cursor-pointer q-pa-md"
                  :class="applyMode === 'replace' ? 'bg-red-1 border-negative' : 'bg-white'"
                  @click="applyMode = 'replace'"
                >
                  <div class="row items-center q-gutter-sm">
                    <q-radio v-model="applyMode" val="replace" color="negative" />
                    <div>
                      <div class="text-weight-bold text-body2 text-negative">Replace All</div>
                      <div class="text-caption text-grey-6">⚠️ Delete ALL existing semesters & calendar data, then recreate fresh from AI.</div>
                    </div>
                  </div>
                </q-card>
              </div>

              <div class="row justify-end q-mb-md">
                <q-btn
                  @click="applySetup"
                  :color="applyMode === 'replace' ? 'negative' : 'positive'"
                  :label="applyMode === 'replace' ? 'Replace & Apply' : 'Apply to Calendar'"
                  :icon="applyMode === 'replace' ? 'delete_sweep' : 'check'"
                  :loading="applying"
                  unelevated
                  class="shadow-2 q-px-md font-bold"
                />
              </div>

              <div v-if="validatedSemesters.length > 0" class="q-col-gutter-lg row">
                <!-- Semesters Grid -->
                <div v-for="(sem, index) in validatedSemesters" :key="index" class="col-12 col-md-6">
                  <q-card bordered flat class="h-full bg-grey-1">
                    <q-card-section class="bg-blue-grey-8 text-white q-py-sm">
                      <div class="row justify-between items-center">
                        <div class="text-subtitle1 font-bold">{{ sem.name }} (Semester {{ sem.number }})</div>
                        <q-badge color="white" text-color="dark" class="font-mono">
                          {{ formatDateShort(sem.start_date) }} — {{ formatDateShort(sem.end_date) }}
                        </q-badge>
                      </div>
                    </q-card-section>
                    
                    <q-card-section class="q-pa-sm">
                      <!-- Vacations Table -->
                      <div class="text-weight-bold text-caption text-uppercase text-grey-8 q-mb-xs q-mt-sm q-px-sm">Vacations ({{ sem.vacations?.length || 0 }})</div>
                      <q-list separator dense class="bg-white rounded border">
                        <q-item v-if="!sem.vacations?.length" class="text-grey-5 italic q-py-xs"><q-item-section>None specified</q-item-section></q-item>
                        <q-item v-for="(v, vi) in sem.vacations" :key="'v'+vi" class="q-py-xs">
                          <q-item-section>
                            <q-item-label class="text-body2 text-red-9 font-medium">{{ v.name }}</q-item-label>
                            <q-item-label caption class="font-mono text-xs">{{ formatDateShort(v.start_date) }} to {{ formatDateShort(v.end_date) }}</q-item-label>
                          </q-item-section>
                        </q-item>
                      </q-list>

                      <!-- Events Table -->
                      <div class="text-weight-bold text-caption text-uppercase text-grey-8 q-mb-xs q-mt-md q-px-sm">Events ({{ sem.events?.length || 0 }})</div>
                      <q-list separator dense class="bg-white rounded border">
                        <q-item v-if="!sem.events?.length" class="text-grey-5 italic q-py-xs"><q-item-section>None specified</q-item-section></q-item>
                        <q-item v-for="(e, ei) in sem.events" :key="'e'+ei" class="q-py-xs">
                          <q-item-section avatar style="min-width: 40px" class="q-pr-xs">
                             <q-badge :color="getEventColor(e.type)" rounded class="q-px-sm self-start">{{ e.type }}</q-badge>
                          </q-item-section>
                          <q-item-section>
                            <q-item-label class="text-body2 text-dark">{{ e.name }}</q-item-label>
                            <q-item-label caption class="font-mono text-xs">{{ formatDateShort(e.date) }}</q-item-label>
                          </q-item-section>
                        </q-item>
                      </q-list>
                    </q-card-section>
                  </q-card>
                </div>
              </div>

              <q-stepper-navigation class="q-mt-xl">
                <q-btn flat @click="step = 3" label="Back to Edit JSON" class="text-grey-7" icon="edit" />
              </q-stepper-navigation>
            </q-step>
          </q-stepper>
        </div>
      </q-card-section>
    </q-card>
  </q-dialog>
</template>

<script setup>
import { ref, watch, computed } from 'vue';
import { useQuasar } from 'quasar';
import { router } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
  modelValue: { type: Boolean, required: true },
  yearId: { type: Number, required: true },
  yearName: { type: String, default: '' },
  schoolId: { type: Number, required: true }
});

const emit = defineEmits(['update:modelValue', 'success']);

const $q = useQuasar();

// Internal state for dialog visibility
const internalValue = computed({
  get: () => props.modelValue,
  set: (val) => emit('update:modelValue', val)
});

// UI State
const step = ref(1);
const validating = ref(false);
const applying = ref(false);
const applyMode = ref('update'); // 'update' | 'replace'

const config = ref({
  country: 'Saudi Arabia',
  schoolType: 'Primary',
  language: 'English',
  semesterCount: 3,
  customInstructions: ''
});

const generatedPrompt = ref('');
const aiResponse = ref('');
const validatedSemesters = ref([]);

// Watch for dialog open to reset state
watch(() => props.modelValue, (isOpen) => {
  if (isOpen) {
    // Only reset if we haven't started yet
    if (step.value === 4 && validatedSemesters.value.length > 0) return;
    step.value = 1;
    aiResponse.value = '';
    validatedSemesters.value = [];
  }
});

const addInstruction = (text) => {
  if (config.value.customInstructions) {
    config.value.customInstructions += '\n- ' + text;
  } else {
    config.value.customInstructions = '- ' + text;
  }
};

const generatePrompt = () => {
  const parts = [
    `Act as an expert academic planner for a ${config.value.schoolType} school in ${config.value.country}.`,
    `Generate exactly ${config.value.semesterCount} semesters for the academic year "${props.yearName}".`,
    `The content language must be ${config.value.language}.`,
    '',
    `Requirements:`,
    `- Provide realistic start and end dates for each of the ${config.value.semesterCount} semesters.`,
    `- Include realistic national/public holidays and school breaks (vacations) for ${config.value.country} during these dates.`,
    `- Include key academic events like "First Day of School", "Mid-term Exams", "Final Exams", "Report Card Day".`,
    `- Event types MUST be one of: 'activity', 'test', 'exam', 'holiday'.`,
    `- All dates MUST be in YYYY-MM-DD format.`,
    '',
    config.value.customInstructions ? `Additional User Instructions:\n${config.value.customInstructions}\n` : '',
    '',
    `Return ONLY a raw JSON object matching this exact schema. Do not include markdown \`\`\`json wrappers.`,
    `{
  "semesters": [
    {
      "number": 1,
      "name": "Semester 1",
      "start_date": "YYYY-MM-DD",
      "end_date": "YYYY-MM-DD",
      "vacations": [
        { "name": "National Day", "start_date": "YYYY-MM-DD", "end_date": "YYYY-MM-DD" }
      ],
      "events": [
        { "name": "First Day of School", "date": "YYYY-MM-DD", "type": "activity" }
      ]
    }
  ]
}`
  ];

  generatedPrompt.value = parts.filter(Boolean).join('\n');
  step.value = 2;
};

const copyPrompt = async () => {
  try {
    await navigator.clipboard.writeText(generatedPrompt.value);
    $q.notify({ type: 'positive', message: 'Prompt copied! Paste it into your AI.', icon: 'check_circle', position: 'top' });
  } catch (err) {
    $q.notify({ type: 'negative', message: 'Failed to copy to clipboard.', position: 'top' });
  }
};

const pasteFromClipboard = async () => {
  try {
    const text = await navigator.clipboard.readText();
    aiResponse.value = text;
    $q.notify({ type: 'positive', message: 'Pasted from clipboard!', icon: 'content_paste', position: 'top' });
  } catch (err) {
    $q.notify({ type: 'negative', message: 'Failed to read clipboard.', caption: 'Try manually pasting (Ctrl+V / Cmd+V).', position: 'top' });
  }
};

const validateResponse = () => {
  validating.value = true;
  try {
    let cleanText = aiResponse.value.trim();
    const jsonMatch = cleanText.match(/```(?:json)?\s*([\s\S]*?)```/);
    if (jsonMatch) {
      cleanText = jsonMatch[1];
    }

    const data = JSON.parse(cleanText);

    if (!data.semesters || !Array.isArray(data.semesters)) {
      throw new Error('Missing "semesters" array in JSON response.');
    }

    // Basic structure checking
    data.semesters.forEach((sem, idx) => {
      if (!sem.number || !sem.start_date || !sem.end_date) {
         throw new Error(`Semester at index ${idx} is missing required fields (number, start_date, end_date)`);
      }
    });

    validatedSemesters.value = data.semesters;
    step.value = 4;
    $q.notify({ type: 'positive', message: `Recognized ${validatedSemesters.value.length} semesters.`, position: 'top' });
  } catch (err) {
    $q.notify({ type: 'negative', message: 'Invalid JSON', caption: err.message, position: 'top' });
  } finally {
    validating.value = false;
  }
};

const applySetup = async () => {
  applying.value = true;
  try {
    const url = route('admin.academic_calendar.year.ai_setup', { year: props.yearId });
    await axios.post(url, {
      semesters: validatedSemesters.value,
      mode: applyMode.value,
    });
    
    $q.notify({
      type: 'positive',
      message: applyMode.value === 'replace'
        ? 'All data replaced with AI-generated schedule!'
        : 'Calendar successfully updated with AI data!',
      icon: 'auto_awesome',
      position: 'top',
      timeout: 4000
    });
    
    internalValue.value = false;
    emit('success');
  } catch (err) {
    $q.notify({
      type: 'negative',
      message: 'Failed to apply AI configuration',
      caption: err.response?.data?.message || err.message,
      position: 'top'
    });
  } finally {
    applying.value = false;
  }
};

// Utilities
const formatDateShort = (dateStr) => {
  if (!dateStr) return '';
  const d = new Date(dateStr);
  return d.toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' });
};

const getEventColor = (type) => {
  switch(type?.toLowerCase()) {
    case 'test': return 'orange-8';
    case 'exam': return 'red-8';
    case 'holiday': return 'green-7';
    case 'activity': return 'indigo-6';
    default: return 'deep-purple';
  }
};

</script>
