<template>
  <q-dialog v-model="isOpen" persistent maximized transition-show="slide-up" transition-hide="slide-down">
    <q-card class="flex flex-col">
      <!-- Header -->
      <q-card-section class="bg-gradient-to-r from-purple-600 to-indigo-600 text-white">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-3">
            <q-icon name="auto_awesome" size="32px" />
            <div>
              <div class="text-h5 font-bold">AI Template Generator</div>
              <div class="text-sm opacity-90">Let AI help you create the perfect lesson template</div>
            </div>
          </div>
          <q-btn flat round dense icon="close" @click="handleClose" />
        </div>
      </q-card-section>

      <!-- Stepper -->
      <q-stepper v-model="currentStep" ref="stepper" color="primary" animated class="flex-1">
        
        <!-- Step 1: Configuration -->
        <q-step :name="1" title="Configure" icon="settings" :done="currentStep > 1">
          <div class="max-w-3xl mx-auto p-6">
            <h3 class="text-xl font-semibold mb-4">Template Configuration</h3>
            
            <!-- Quick Suggestions -->
            <div class="mb-6">
              <label class="text-sm font-medium text-gray-700 mb-2 block">Quick Suggestions</label>
              <div class="flex flex-wrap gap-2">
                <q-btn
                  v-for="preset in quickSuggestions"
                  :key="preset.label"
                  outline
                  color="primary"
                  :label="preset.label"
                  @click="applyPreset(preset)"
                  size="sm"
                />
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
              <!-- Subject -->
              <q-select
                v-model="config.subject"
                :options="subjectOptions"
                label="Subject *"
                outlined
                dense
                emit-value
                map-options
              />

              <!-- Grade Level -->
              <q-select
                v-model="config.grade"
                :options="gradeOptions"
                label="Grade Level *"
                outlined
                dense
                emit-value
                map-options
              />

              <!-- Number of Sections -->
              <q-input
                v-model.number="config.sectionCount"
                type="number"
                label="Number of Sections *"
                outlined
                dense
                min="3"
                max="10"
                hint="Between 3 and 10 sections"
              />
            </div>

            <!-- Custom Instructions -->
            <q-input
              v-model="config.customInstructions"
              type="textarea"
              label="Additional Instructions (Optional)"
              outlined
              rows="3"
              hint="Add any specific requirements or focus areas"
              class="mb-4"
            />

            <div class="flex justify-end gap-2">
              <q-btn flat label="Cancel" @click="handleClose" />
              <q-btn 
                unelevated 
                color="primary" 
                label="Generate Prompt" 
                icon-right="arrow_forward"
                @click="generatePrompt"
                :disable="!isConfigValid"
              />
            </div>
          </div>
        </q-step>

        <!-- Step 2: Prompt Display with AI Tool -->
        <q-step :name="2" title="Use AI Tool" icon="smart_toy" :done="currentStep > 2">
          <div class="max-w-6xl mx-auto p-6">
            <h3 class="text-xl font-semibold mb-4">Work with AI Tool</h3>
            
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
              <div class="flex items-start gap-2">
                <q-icon name="info" color="blue" size="20px" class="mt-1" />
                <div class="text-sm text-blue-800">
                  <strong>Instructions:</strong> Copy the prompt below and paste it into the AI tool. You can use the embedded iframe or open your AI tool in a new tab.
                </div>
              </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
              <!-- Left: Prompt -->
              <div>
                <div class="flex items-center justify-between mb-2">
                  <label class="text-sm font-medium text-gray-700">Generated Prompt</label>
                  <q-btn
                    dense
                    unelevated
                    color="primary"
                    icon="content_copy"
                    label="Copy"
                    @click="copyPromptToClipboard"
                    size="sm"
                    :loading="copying"
                  />
                </div>
                <q-card flat bordered class="bg-gray-50" style="max-height: 400px; overflow-y: auto;">
                  <q-card-section>
                    <pre class="whitespace-pre-wrap text-xs font-mono">{{ generatedPrompt }}</pre>
                  </q-card-section>
                </q-card>

                <!-- AI Tool URL Configuration -->
                <div class="mt-4">
                  <div class="flex items-center justify-between mb-2">
                    <label class="text-sm font-medium text-gray-700">AI Tool URL</label>
                    <q-btn
                      dense
                      flat
                      color="primary"
                      icon="settings"
                      label="Change"
                      @click="showUrlConfig = true"
                      size="sm"
                    />
                  </div>
                  <div class="flex gap-2">
                    <q-btn
                      v-for="preset in aiToolPresets"
                      :key="preset.name"
                      dense
                      outline
                      :color="aiToolUrl === preset.url ? 'primary' : 'grey'"
                      :label="preset.name"
                      @click="setAIToolUrl(preset.url)"
                      size="sm"
                    />
                  </div>
                </div>
              </div>

              <!-- Right: AI Tool Iframe -->
              <div>
                <div class="flex items-center justify-between mb-2">
                  <label class="text-sm font-medium text-gray-700">AI Tool (Embedded)</label>
                  <q-btn
                    dense
                    flat
                    color="primary"
                    icon="open_in_new"
                    label="Open in New Tab"
                    @click="openAIToolInNewTab"
                    size="sm"
                  />
                </div>
                <div class="border rounded-lg overflow-hidden bg-white" style="height: 400px;">
                  <iframe
                    v-if="aiToolUrl"
                    :src="aiToolUrl"
                    class="w-full h-full"
                    frameborder="0"
                    sandbox="allow-same-origin allow-scripts allow-forms allow-popups allow-popups-to-escape-sandbox"
                  ></iframe>
                  <div v-else class="flex items-center justify-center h-full text-gray-400">
                    <div class="text-center">
                      <q-icon name="smart_toy" size="48px" class="mb-2" />
                      <div>Select an AI tool to get started</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="flex justify-between gap-2 mt-6">
              <q-btn flat label="Back" icon="arrow_back" @click="currentStep = 1" />
              <q-btn 
                unelevated 
                color="primary" 
                label="I Have the Response" 
                icon-right="arrow_forward"
                @click="currentStep = 3"
              />
            </div>
          </div>
        </q-step>

        <!-- URL Configuration Dialog -->
        <q-dialog v-model="showUrlConfig">
          <q-card style="min-width: 400px;">
            <q-card-section class="bg-primary text-white">
              <div class="text-h6">Configure AI Tool URL</div>
            </q-card-section>

            <q-card-section>
              <q-input
                v-model="customAiToolUrl"
                label="Custom AI Tool URL"
                outlined
                dense
                placeholder="https://chatgpt.com"
                hint="Enter the URL of your preferred AI tool"
              />

              <div class="mt-4">
                <div class="text-sm font-medium text-gray-700 mb-2">Quick Presets:</div>
                <div class="space-y-2">
                  <q-btn
                    v-for="preset in aiToolPresets"
                    :key="preset.name"
                    outline
                    color="primary"
                    :label="preset.name"
                    @click="customAiToolUrl = preset.url"
                    class="w-full"
                    align="left"
                  >
                    <q-tooltip>{{ preset.url }}</q-tooltip>
                  </q-btn>
                </div>
              </div>
            </q-card-section>

            <q-card-actions align="right">
              <q-btn flat label="Cancel" v-close-popup />
              <q-btn 
                unelevated 
                color="primary" 
                label="Save" 
                @click="saveCustomUrl"
              />
            </q-card-actions>
          </q-card>
        </q-dialog>

        <!-- Step 3: Response Input -->
        <q-step :name="3" title="Paste Response" icon="content_paste" :done="currentStep > 3">
          <div class="max-w-4xl mx-auto p-6">
            <h3 class="text-xl font-semibold mb-4">Paste AI Response</h3>
            
            <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mb-4">
              <div class="flex items-start gap-2">
                <q-icon name="tips_and_updates" color="amber" size="20px" class="mt-1" />
                <div class="text-sm text-amber-800">
                  <strong>Tip:</strong> Paste the complete JSON response from your AI tool. The system will automatically validate the format.
                </div>
              </div>
            </div>

            <!-- Response Input with Paste Button -->
            <div class="relative mb-4">
              <q-input
                v-model="aiResponse"
                type="textarea"
                label="AI Response *"
                outlined
                rows="12"
                placeholder='Paste the AI response here...'
                class="font-mono"
              />
              
              <!-- Paste Button -->
              <q-btn
                unelevated
                color="secondary"
                icon="content_paste"
                label="Paste from Clipboard"
                @click="pasteFromClipboard"
                class="absolute top-4 right-4"
                size="sm"
              />
            </div>

            <!-- Validation Errors -->
            <div v-if="validationErrors.length > 0" class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
              <div class="flex items-start gap-2">
                <q-icon name="error" color="red" size="20px" class="mt-1" />
                <div class="flex-1">
                  <div class="font-semibold text-red-800 mb-2">Validation Errors:</div>
                  <ul class="list-disc list-inside text-sm text-red-700 space-y-1">
                    <li v-for="(error, index) in validationErrors" :key="index">{{ error }}</li>
                  </ul>
                </div>
              </div>
            </div>

            <div class="flex justify-between gap-2">
              <q-btn flat label="Back" icon="arrow_back" @click="currentStep = 2" />
              <q-btn 
                unelevated 
                color="primary" 
                label="Validate & Preview" 
                icon-right="arrow_forward"
                @click="validateAndPreview"
                :disable="!aiResponse.trim()"
                :loading="validating"
              />
            </div>
          </div>
        </q-step>

        <!-- Step 4: Preview -->
        <q-step :name="4" title="Preview" icon="visibility" :done="currentStep > 4">
          <div class="max-w-4xl mx-auto p-6">
            <h3 class="text-xl font-semibold mb-4">Preview Template</h3>
            
            <div v-if="parsedTemplate" class="space-y-4">
              <!-- Template Name -->
              <q-card flat bordered>
                <q-card-section>
                  <div class="text-sm text-gray-600 mb-1">Template Name</div>
                  <div class="text-lg font-semibold">{{ parsedTemplate.name }}</div>
                </q-card-section>
              </q-card>

              <!-- Sections Preview -->
              <q-card flat bordered>
                <q-card-section>
                  <div class="text-sm text-gray-600 mb-3">Sections ({{ parsedTemplate.sections.length }})</div>
                  <div class="space-y-2">
                    <div
                      v-for="(section, index) in parsedTemplate.sections"
                      :key="index"
                      class="flex items-center gap-3 p-3 rounded-lg border"
                      :style="{
                        backgroundColor: section.bg,
                        borderColor: section.borderColor,
                        color: section.textColor
                      }"
                    >
                      <div class="flex items-center gap-2">
                        <span class="text-2xl">{{ section.icon }}</span>
                        <q-icon :name="section.qIcon" size="24px" />
                      </div>
                      <div class="flex-1">
                        <div class="font-semibold">{{ section.title }}</div>
                        <div class="text-xs opacity-75">ID: {{ section.id }}</div>
                      </div>
                      <q-chip dense :style="{ backgroundColor: section.bgActive }">
                        Active State
                      </q-chip>
                    </div>
                  </div>
                </q-card-section>
              </q-card>

              <!-- Color Palette Preview -->
              <q-card flat bordered>
                <q-card-section>
                  <div class="text-sm text-gray-600 mb-3">Color Palette</div>
                  <div class="flex flex-wrap gap-2">
                    <div
                      v-for="(section, index) in parsedTemplate.sections"
                      :key="index"
                      class="flex items-center gap-2 text-xs"
                    >
                      <div
                        class="w-8 h-8 rounded border"
                        :style="{ backgroundColor: section.bg, borderColor: section.borderColor }"
                      ></div>
                      <span>{{ section.title }}</span>
                    </div>
                  </div>
                </q-card-section>
              </q-card>
            </div>

            <div class="flex justify-between gap-2 mt-6">
              <q-btn flat label="Back" icon="arrow_back" @click="currentStep = 3" />
              <div class="flex gap-2">
                <q-btn flat label="Reject" color="negative" @click="handleClose" />
                <q-btn 
                  unelevated 
                  color="positive" 
                  label="Accept Template" 
                  icon="check_circle"
                  @click="acceptTemplate"
                />
              </div>
            </div>
          </div>
        </q-step>

      </q-stepper>
    </q-card>
  </q-dialog>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useQuasar } from 'quasar';
import {
  generateTemplatePrompt,
  validateTemplateResponse,
  parseAIResponse,
  quickSuggestions
} from '@/utils/promptTemplates';

const $q = useQuasar();
const emit = defineEmits(['template-accepted', 'close']);

// Dialog state
const isOpen = ref(false);
const currentStep = ref(1);

// Configuration
const config = ref({
  subject: 'Mathematics',
  grade: 'Elementary (K-5)',
  sectionCount: 5,
  customInstructions: ''
});

// Options
const subjectOptions = [
  { label: 'Mathematics', value: 'Mathematics' },
  { label: 'Science', value: 'Science' },
  { label: 'Language Arts', value: 'Language Arts' },
  { label: 'Social Studies', value: 'Social Studies' },
  { label: 'Arts', value: 'Arts' },
  { label: 'Physical Education', value: 'Physical Education' },
  { label: 'General', value: 'General' }
];

const gradeOptions = [
  { label: 'Elementary (K-5)', value: 'Elementary (K-5)' },
  { label: 'Middle School (6-8)', value: 'Middle School (6-8)' },
  { label: 'High School (9-12)', value: 'High School (9-12)' },
  { label: 'Mixed Grades', value: 'Mixed Grades' }
];

// Prompt generation
const generatedPrompt = ref('');
const promptCopied = ref(false);
const copying = ref(false);

// AI Tool URL Management
const aiToolUrl = ref('');
const customAiToolUrl = ref('');
const showUrlConfig = ref(false);

const aiToolPresets = [
  { name: 'Gemini', url: 'https://gemini.google.com/' },
  { name: 'ChatGPT', url: 'https://chatgpt.com/' },
  { name: 'Claude', url: 'https://claude.ai/' },
  { name: 'Perplexity', url: 'https://www.perplexity.ai/' }
];

// Response handling
const aiResponse = ref('');
const validationErrors = ref([]);
const parsedTemplate = ref(null);
const validating = ref(false);

// Computed
const isConfigValid = computed(() => {
  return config.value.subject && 
         config.value.grade && 
         config.value.sectionCount >= 3 && 
         config.value.sectionCount <= 10;
});

// AI Tool URL Methods
const loadAIToolUrl = () => {
  const savedUrl = localStorage.getItem('ai_tool_url');
  if (savedUrl) {
    aiToolUrl.value = savedUrl;
  } else {
    // Default to Gemini
    aiToolUrl.value = aiToolPresets[0].url;
  }
};

const setAIToolUrl = (url) => {
  aiToolUrl.value = url;
  localStorage.setItem('ai_tool_url', url);
  $q.notify({
    type: 'positive',
    message: 'AI tool updated',
    position: 'top',
    timeout: 1500
  });
};

const saveCustomUrl = () => {
  if (customAiToolUrl.value.trim()) {
    setAIToolUrl(customAiToolUrl.value.trim());
    showUrlConfig.value = false;
  }
};

const openAIToolInNewTab = () => {
  if (aiToolUrl.value) {
    window.open(aiToolUrl.value, '_blank');
  }
};

// Methods
const open = () => {
  isOpen.value = true;
  currentStep.value = 1;
  loadAIToolUrl(); // Load saved AI tool URL
  resetForm();
};

const handleClose = () => {
  isOpen.value = false;
  emit('close');
};

const resetForm = () => {
  config.value = {
    subject: 'Mathematics',
    grade: 'Elementary (K-5)',
    sectionCount: 5,
    customInstructions: ''
  };
  generatedPrompt.value = '';
  aiResponse.value = '';
  validationErrors.value = [];
  parsedTemplate.value = null;
  promptCopied.value = false;
};

const applyPreset = (preset) => {
  config.value.subject = preset.subject;
  config.value.grade = preset.grade;
  config.value.sectionCount = preset.sections;
  config.value.customInstructions = preset.instructions;
};

const generatePrompt = () => {
  generatedPrompt.value = generateTemplatePrompt(config.value);
  currentStep.value = 2;
};

const copyPromptToClipboard = async () => {
  copying.value = true;
  try {
    await navigator.clipboard.writeText(generatedPrompt.value);
    promptCopied.value = true;
    $q.notify({
      type: 'positive',
      message: 'Prompt copied to clipboard!',
      position: 'top',
      timeout: 2000
    });
    setTimeout(() => {
      promptCopied.value = false;
    }, 2000);
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: 'Failed to copy to clipboard',
      position: 'top'
    });
  } finally {
    copying.value = false;
  }
};

const pasteFromClipboard = async () => {
  try {
    const text = await navigator.clipboard.readText();
    aiResponse.value = text;
    $q.notify({
      type: 'positive',
      message: 'Pasted from clipboard!',
      position: 'top',
      timeout: 1500
    });
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: 'Failed to read from clipboard. Please paste manually.',
      position: 'top'
    });
  }
};

const validateAndPreview = () => {
  validating.value = true;
  validationErrors.value = [];
  
  setTimeout(() => {
    // Parse the response
    const parseResult = parseAIResponse(aiResponse.value);
    
    if (!parseResult.success) {
      validationErrors.value = [parseResult.error];
      validating.value = false;
      return;
    }
    
    // Validate the structure
    const validationResult = validateTemplateResponse(parseResult.data);
    
    if (!validationResult.valid) {
      validationErrors.value = validationResult.errors;
      validating.value = false;
      return;
    }
    
    // Success - move to preview
    parsedTemplate.value = parseResult.data;
    currentStep.value = 4;
    validating.value = false;
  }, 500);
};

const acceptTemplate = () => {
  emit('template-accepted', parsedTemplate.value);
  isOpen.value = false;
};

// Expose methods
defineExpose({
  open
});
</script>

<style scoped>
pre {
  margin: 0;
  font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
}
</style>
