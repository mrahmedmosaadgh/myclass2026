<template>
  <q-dialog v-model="isOpen" persistent maximized transition-show="slide-up" transition-hide="slide-down">

    
    
    
    <q-card class=" bg-grey-1">
      <!-- Header with improved gradient -->
     <div class="  z-10  w-full left-0   bg-grey-1">
          <div class="q-pa-md" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
        <div class="row items-center">
          <q-avatar size="36px" color="white" text-color="purple-8" class="q-mr-md">
            <q-icon name="auto_awesome" size="24px" />
          </q-avatar>
          <div class="col">
            <div class="text-h5 text-white font-bold">AI Lesson Plan Generator</div>
            <div class="text-subtitle2 text-white" style="opacity: 0.9;">Generate complete lesson content with intelligent AI assistance</div>
          </div>
          <q-btn flat round dense icon="close" color="white" @click="handleClose" size="md" />
        </div>
      </div>
    
    </div>

      <!-- Stepper with no top padding -->
      <q-stepper v-model="currentStep" ref="stepper" color="primary" animated flat class="flex-1 bg-grey-1">
        
        <!-- Step 1: Configure -->
        <q-step :name="1" title="Configure" icon="settings" :done="currentStep > 1">
          <div class="max-w-3xl mx-auto p-6">
            <h3 class="text-xl font-semibold mb-4">Lesson Configuration</h3>
            
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

            <!-- Lesson Info (Read-only) -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
              <div class="grid grid-cols-2 gap-3 text-sm">
                <div>
                  <span class="font-semibold text-blue-900">Subject:</span>
                  <span class="ml-2 text-blue-700">{{ lessonConfig.subject }}</span>
                </div>
                <div>
                  <span class="font-semibold text-blue-900">Grade:</span>
                  <span class="ml-2 text-blue-700">{{ lessonConfig.grade }}</span>
                </div>
                <div class="col-span-2">
                  <span class="font-semibold text-blue-900">Lesson:</span>
                  <span class="ml-2 text-blue-700">{{ lessonConfig.lessonTitle }}</span>
                </div>
              </div>
            </div>

            <!-- Sections (Read-only) -->
            <div class="mb-4">
              <label class="text-sm font-medium text-gray-700 mb-2 block">Lesson Sections</label>
              <div class="grid grid-cols-2 gap-2">
                <div
                  v-for="section in lessonConfig.sections"
                  :key="section.id"
                  class="flex items-center gap-2 p-2 bg-gray-50 rounded border"
                >
                  <span class="text-xl">{{ section.icon }}</span>
                  <span class="text-sm font-medium">{{ section.title }}</span>
                </div>
              </div>
              <p class="text-xs text-gray-500 mt-2">
                AI will generate 3-5 slides for each section
              </p>
            </div>

            <!-- Custom Instructions -->
            <q-input
              v-model="config.customInstructions"
              type="textarea"
              label="Additional Instructions (Optional)"
              outlined
              rows="4"
              hint="Add specific requirements, topics to cover, or teaching style preferences"
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
              />
            </div>
          </div>
        </q-step>

        <!-- Step 2: Use AI Tool -->
        <q-step :name="2" title="Use AI Tool" icon="smart_toy" :done="currentStep > 2">
          <div class="max-w-6xl mx-auto p-6">
            <h3 class="text-xl font-semibold mb-4">Work with AI Tool</h3>
            
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
              <div class="flex items-start gap-2">
                <q-icon name="info" color="blue" size="20px" class="mt-1" />
                <div class="text-sm text-blue-800">
                  <strong>Instructions:</strong> Copy the prompt below and paste it into your AI tool. The AI will generate slides for all sections.
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

        <!-- Step 3: Paste Response -->
        <q-step :name="3" title="Paste Response" icon="content_paste" :done="currentStep > 3">
          <div class="max-w-4xl mx-auto p-6">
            <h3 class="text-xl font-semibold mb-4">Paste AI Response</h3>
            
            <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mb-4">
              <div class="flex items-start gap-2">
                <q-icon name="tips_and_updates" color="amber" size="20px" class="mt-1" />
                <div class="text-sm text-amber-800">
                  <strong>Tip:</strong> Paste the complete JSON response from your AI tool. The system will validate and create slides for all sections.
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
          <div class="max-w-5xl mx-auto p-6">
            <h3 class="text-xl font-semibold mb-4">Preview Generated Lesson Plan</h3>
            
            <div v-if="parsedPlan" class="space-y-4">
              <!-- Summary -->
              <q-card flat bordered>
                <q-card-section>
                  <div class="text-sm text-gray-600 mb-2">Summary</div>
                  <div class="grid grid-cols-2 gap-4">
                    <div>
                      <span class="font-semibold">Total Sections:</span>
                      <span class="ml-2">{{ parsedPlan.sections.length }}</span>
                    </div>
                    <div>
                      <span class="font-semibold">Total Slides:</span>
                      <span class="ml-2">{{ totalSlides }}</span>
                    </div>
                  </div>
                </q-card-section>
              </q-card>

              <!-- Sections Preview -->
              <div
                v-for="(section, idx) in parsedPlan.sections"
                :key="idx"
                class="border rounded-lg overflow-hidden"
              >
                <div class="bg-gray-100 px-4 py-2 font-semibold flex items-center gap-2">
                  <q-icon name="folder" size="sm" />
                  <span>{{ getSectionTitle(section.sectionId) }}</span>
                  <q-chip dense size="sm" color="primary" text-color="white">
                    {{ section.slides.length }} slides
                  </q-chip>
                </div>
                <div class="p-4 space-y-2">
                  <div
                    v-for="(slide, slideIdx) in section.slides"
                    :key="slideIdx"
                    class="flex items-start gap-3 p-3 bg-gray-50 rounded border"
                  >
                    <q-icon name="description" size="sm" color="primary" />
                    <div class="flex-1">
                      <div class="text-xs text-gray-500 mb-1">Slide {{ slideIdx + 1 }} - {{ slide.slide_type }}</div>
                      <div class="text-sm" v-html="getSlidePreview(slide)"></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="flex justify-between gap-2 mt-6">
              <q-btn flat label="Back" icon="arrow_back" @click="currentStep = 3" />
              <div class="flex gap-2">
                <q-btn flat label="Reject" color="negative" @click="handleClose" />
                <q-btn 
                  unelevated 
                  color="positive" 
                  label="Accept & Create Slides" 
                  icon="check_circle"
                  @click="acceptPlan"
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
  generateLessonPlanPrompt,
  validateLessonPlanResponse,
  parseAIResponse,
  lessonPlanQuickSuggestions
} from '@/utils/lessonPlanPrompts';

const $q = useQuasar();
const emit = defineEmits(['plan-accepted', 'close']);

const props = defineProps({
  lessonConfig: {
    type: Object,
    required: true,
    // Expected: { lessonTitle, subject, grade, sections: [{id, title, icon}] }
  }
});

// Dialog state
const isOpen = ref(false);
const currentStep = ref(1);

// Configuration
const config = ref({
  customInstructions: ''
});

// Quick suggestions
const quickSuggestions = lessonPlanQuickSuggestions;

// Prompt generation
const generatedPrompt = ref('');
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
const parsedPlan = ref(null);
const validating = ref(false);

// Computed
const totalSlides = computed(() => {
  if (!parsedPlan.value) return 0;
  return parsedPlan.value.sections.reduce((sum, section) => sum + section.slides.length, 0);
});

// AI Tool URL Methods
const loadAIToolUrl = () => {
  const savedUrl = localStorage.getItem('ai_tool_url');
  if (savedUrl) {
    aiToolUrl.value = savedUrl;
  } else {
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
  loadAIToolUrl();
  resetForm();
};

const handleClose = () => {
  isOpen.value = false;
  emit('close');
};

const resetForm = () => {
  config.value = {
    customInstructions: ''
  };
  generatedPrompt.value = '';
  aiResponse.value = '';
  validationErrors.value = [];
  parsedPlan.value = null;
};

const applyPreset = (preset) => {
  config.value.customInstructions = preset.instructions;
};

const generatePrompt = () => {
  generatedPrompt.value = generateLessonPlanPrompt({
    ...props.lessonConfig,
    customInstructions: config.value.customInstructions
  });
  currentStep.value = 2;
};

const copyPromptToClipboard = async () => {
  copying.value = true;
  try {
    await navigator.clipboard.writeText(generatedPrompt.value);
    $q.notify({
      type: 'positive',
      message: 'Prompt copied to clipboard!',
      position: 'top',
      timeout: 2000
    });
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
    const validationResult = validateLessonPlanResponse(parseResult.data);
    
    if (!validationResult.valid) {
      validationErrors.value = validationResult.errors;
      validating.value = false;
      return;
    }
    
    // Success - move to preview
    parsedPlan.value = parseResult.data;
    currentStep.value = 4;
    validating.value = false;
  }, 500);
};

const getSectionTitle = (sectionId) => {
  const section = props.lessonConfig.sections.find(s => s.id === sectionId);
  return section ? section.title : sectionId;
};

const getSlidePreview = (slide) => {
  if (slide.slide_type === 'text' && slide.slide_content?.text) {
    // Strip HTML tags for preview and limit length
    const text = slide.slide_content.text.replace(/<[^>]*>/g, ' ').trim();
    return text.length > 150 ? text.substring(0, 150) + '...' : text;
  }
  return 'Content preview not available';
};

const acceptPlan = () => {
  emit('plan-accepted', parsedPlan.value);
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
