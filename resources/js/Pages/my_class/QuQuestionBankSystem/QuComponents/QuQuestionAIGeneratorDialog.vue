<template>
  <q-dialog v-model="showDialog" maximized>
    <q-card>
      <q-card-section class="row items-center q-pb-none">
        <q-icon name="auto_awesome" size="md" color="primary" class="q-mr-sm" />
        <div class="text-h6">AI Question Generator</div>
        <q-space />
        <q-btn icon="close" flat round dense v-close-popup />
      </q-card-section>

      <q-card-section>
        <q-stepper
          v-model="step"
          vertical
          color="primary"
          animated
        >
          <!-- Step 1: Configure Prompt -->
          <q-step
            :name="1"
            title="Configure AI Prompt"
            icon="settings"
            :done="step > 1"
          >
            <div class="q-gutter-md">
              <!-- Number of Questions -->
              <q-input
                v-model.number="config.count"
                type="number"
                label="Number of Questions *"
                min="1"
                max="50"
                hint="How many questions to generate (1-50)"
                :rules="[val => val > 0 && val <= 50 || 'Must be between 1 and 50']"
              />

              <!-- Question Types -->
              <q-select
                v-model="config.types"
                :options="questionTypeOptions"
                label="Question Types *"
                multiple
                emit-value
                map-options
                hint="Select one or more question types"
                :rules="[val => val.length > 0 || 'Select at least one type']"
              />

              <!-- Difficulty Levels -->
              <q-select
                v-model="config.difficulties"
                :options="difficultyOptions"
                label="Difficulty Levels"
                multiple
                hint="Leave empty for mixed difficulty"
              />

              <!-- Bloom Levels -->
              <q-select
                v-model="config.bloomLevels"
                :options="bloomLevelOptions"
                option-value="value"
                option-label="label"
                label="Bloom's Taxonomy Levels"
                multiple
                emit-value
                map-options
                hint="Optional: Specify cognitive levels"
              />

              <!-- Topic Selection (Curriculum or Custom) -->
              <div class="q-mb-md">
                <q-radio
                  v-model="topicMode"
                  val="curriculum"
                  label="Select from Curriculum Topics"
                  class="q-mb-sm"
                />
                <q-radio
                  v-model="topicMode"
                  val="custom"
                  label="Enter Custom Topic"
                />
              </div>

              <!-- Curriculum Topic -->
              <q-select
                v-if="topicMode === 'curriculum'"
                v-model="config.topicId"
                :options="topics"
                option-value="id"
                option-label="name"
                label="Curriculum Topic"
                clearable
                emit-value
                map-options
                hint="Select from existing curriculum topics"
                class="q-mb-md"
              />

              <!-- Custom Topic Input -->
              <q-input
                v-if="topicMode === 'custom'"
                v-model="config.customTopic"
                label="Custom Topic"
                outlined
                hint="Enter any topic you want questions about"
                placeholder="e.g., Photosynthesis, Quadratic Equations, World War II"
                class="q-mb-md"
              />

              <!-- Learning Objectives (Optional) -->
              <q-input
                v-model="config.learningObjectives"
                type="textarea"
                label="Learning Objectives (Optional)"
                outlined
                rows="2"
                hint="What should students learn? (e.g., 'Students will be able to solve quadratic equations')"
                placeholder="Enter specific learning goals..."
                class="q-mb-md"
              />

              <!-- Custom Instructions / Context -->
              <q-input
                v-model="config.customInstructions"
                type="textarea"
                label="Additional Instructions / Context"
                outlined
                rows="3"
                hint="Add any special requirements, context, or style preferences"
                placeholder="e.g., 'Focus on real-world applications', 'Include diagrams descriptions', 'Avoid complex terminology'"
                class="q-mb-md"
              />

              <!-- Smart Suggestions -->
              <q-expansion-item
                icon="lightbulb"
                label="Smart Suggestions"
                caption="Click for intelligent recommendations"
                class="q-mb-md"
              >
                <q-card flat bordered class="q-pa-md">
                  <div class="text-subtitle2 q-mb-sm">Quick Presets:</div>
                  <div class="q-gutter-sm">
                    <q-btn
                      size="sm"
                      outline
                      color="primary"
                      label="Exam Prep"
                      @click="applyPreset('exam')"
                    />
                    <q-btn
                      size="sm"
                      outline
                      color="primary"
                      label="Homework"
                      @click="applyPreset('homework')"
                    />
                    <q-btn
                      size="sm"
                      outline
                      color="primary"
                      label="Quick Quiz"
                      @click="applyPreset('quiz')"
                    />
                    <q-btn
                      size="sm"
                      outline
                      color="primary"
                      label="Practice"
                      @click="applyPreset('practice')"
                    />
                  </div>
                  
                  <q-separator class="q-my-md" />
                  
                  <div class="text-subtitle2 q-mb-sm">Suggested Instructions:</div>
                  <q-list dense>
                    <q-item clickable @click="addInstruction('Include step-by-step solutions')">
                      <q-item-section avatar>
                        <q-icon name="add_circle" color="primary" />
                      </q-item-section>
                      <q-item-section>Include step-by-step solutions</q-item-section>
                    </q-item>
                    <q-item clickable @click="addInstruction('Focus on real-world applications')">
                      <q-item-section avatar>
                        <q-icon name="add_circle" color="primary" />
                      </q-item-section>
                      <q-item-section>Focus on real-world applications</q-item-section>
                    </q-item>
                    <q-item clickable @click="addInstruction('Include visual/diagram descriptions')">
                      <q-item-section avatar>
                        <q-icon name="add_circle" color="primary" />
                      </q-item-section>
                      <q-item-section>Include visual/diagram descriptions</q-item-section>
                    </q-item>
                    <q-item clickable @click="addInstruction('Vary difficulty progressively')">
                      <q-item-section avatar>
                        <q-icon name="add_circle" color="primary" />
                      </q-item-section>
                      <q-item-section>Vary difficulty progressively</q-item-section>
                    </q-item>
                  </q-list>
                </q-card>
              </q-expansion-item>

              <!-- LaTeX Support -->
              <q-checkbox
                v-model="config.latex"
                label="Enable LaTeX Support"
              >
                <q-tooltip>For math/science questions with formulas</q-tooltip>
              </q-checkbox>

              <!-- Language -->
              <q-select
                v-model="config.language"
                :options="['English', 'Arabic']"
                label="Language"
                hint="Question language"
              />
            </div>

            <q-stepper-navigation>
              <q-btn @click="generatePrompt" color="primary" label="Generate Prompt" icon="content_copy" />
            </q-stepper-navigation>
          </q-step>

          <!-- Step 2: Copy Prompt -->
          <q-step
            :name="2"
            title="Copy Prompt to AI"
            icon="content_copy"
            :done="step > 2"
          >
            <q-card flat bordered>
              <q-card-section>
                <div class="text-subtitle2 q-mb-sm">Generated Prompt:</div>
                <q-input
                  v-model="generatedPrompt"
                  type="textarea"
                  readonly
                  outlined
                  rows="15"
                  class="q-mb-md"
                />
                <q-btn
                  @click="copyPrompt"
                  color="primary"
                  label="Copy to Clipboard"
                  icon="content_copy"
                  class="q-mr-sm"
                />
                <q-banner class="bg-info text-white q-mt-md">
                  <template v-slot:avatar>
                    <q-icon name="info" />
                  </template>
                  <div>
                    Copy prompt above and paste into:
                    <div class="row q-gutter-x-sm q-mt-xs items-center flex-wrap">
                        <q-btn dense flat no-caps color="white" label="ChatGPT" type="a" href="https://chatgpt.com/" target="_blank" icon-right="open_in_new" size="sm" />
                        <q-btn dense flat no-caps color="white" label="Claude" type="a" href="https://claude.ai/" target="_blank" icon-right="open_in_new" size="sm" />
                        <q-btn dense flat no-caps color="white" label="Gemini" type="a" href="https://gemini.google.com/" target="_blank" icon-right="open_in_new" size="sm" />
                        <q-btn dense flat no-caps color="white" label="DeepSeek" type="a" href="https://chat.deepseek.com/" target="_blank" icon-right="open_in_new" size="sm" />
                        <q-btn dense flat no-caps color="white" label="Copilot" type="a" href="https://copilot.microsoft.com/" target="_blank" icon-right="open_in_new" size="sm" />
                    </div>
                    <div class="q-mt-xs">Then paste the JSON response in the next step.</div>
                  </div>
                </q-banner>
              </q-card-section>
            </q-card>

            <q-stepper-navigation>
              <q-btn @click="step = 3" color="primary" label="Next: Paste AI Response" />
              <q-btn flat @click="step = 1" label="Back" class="q-ml-sm" />
            </q-stepper-navigation>
          </q-step>

          <!-- Step 3: Paste AI Response -->
          <q-step
            :name="3"
            title="Paste AI Response"
            icon="paste"
            :done="step > 3"
          >
            <div class="row items-center justify-between q-mb-sm">
              <div class="text-subtitle2">Paste AI Response (JSON)</div>
              <q-btn
                flat
                dense
                color="primary"
                icon="content_paste"
                label="Paste from Clipboard"
                @click="pasteFromClipboard"
              />
            </div>
            <q-input
              v-model="aiResponse"
              type="textarea"
              placeholder="Paste the JSON response from the AI here..."
              outlined
              rows="10"
              hint="Paste the JSON response from the AI"
              class="q-mb-md"
            />

            <q-stepper-navigation>
              <q-btn @click="validateResponse" color="primary" label="Validate & Preview" :loading="validating" />
              <q-btn flat @click="step = 2" label="Back" class="q-ml-sm" />
            </q-stepper-navigation>
          </q-step>

          <!-- Step 4: Preview & Insert -->
          <q-step
            :name="4"
            title="Preview & Insert"
            icon="preview"
          >
            <div v-if="validatedQuestions.length > 0">
              <!-- Selection Summary -->
              <q-banner class="bg-primary text-white q-mb-md">
                <template v-slot:avatar>
                  <q-icon name="check_circle" />
                </template>
                <div class="row items-center">
                  <div class="col">
                    <strong>{{ selectedCount }}</strong> of <strong>{{ validatedQuestions.length }}</strong> questions selected
                  </div>
                  <div class="col-auto q-gutter-sm">
                    <q-btn
                      size="sm"
                      outline
                      color="white"
                      label="Select All"
                      @click="selectAll"
                      icon="check_box"
                    />
                    <q-btn
                      size="sm"
                      outline
                      color="white"
                      label="Select None"
                      @click="selectNone"
                      icon="check_box_outline_blank"
                    />
                    <q-btn
                      size="sm"
                      outline
                      color="white"
                      label="Inverse"
                      @click="selectInverse"
                      icon="swap_vert"
                    />
                  </div>
                </div>
              </q-banner>

                <q-table
                  :rows="validatedQuestions"
                  :columns="previewColumns"
                  row-key="index"
                  flat
                  bordered
                  selection="multiple"
                  v-model:selected="selectedQuestions"
                  class="q-mb-md"
                  :pagination="{ rowsPerPage: 0 }"
                  hide-bottom
                >
                <template v-slot:body-cell-number="props">
                  <q-td :props="props">
                    <strong>#{{ props.row.index + 1 }}</strong>
                  </q-td>
                </template>
                <template v-slot:body-cell-question_text="props">
                  <q-td :props="props">
                    <div class="text-pre-wrap">{{ truncate(props.row.question_text, 100) }}</div>
                  </q-td>
                </template>
                <template v-slot:body-cell-status="props">
                  <q-td :props="props">
                    <q-badge :color="props.row.valid ? 'positive' : 'negative'">
                      {{ props.row.valid ? 'Valid' : 'Invalid' }}
                    </q-badge>
                    <div v-if="!props.row.valid" class="text-caption text-negative">
                      {{ props.row.errors }}
                    </div>
                  </q-td>
                </template>
              </q-table>

              <q-banner v-if="invalidCount > 0" class="bg-warning text-white q-mb-md">
                <template v-slot:avatar>
                  <q-icon name="warning" />
                </template>
                {{ invalidCount }} invalid question(s) detected (cannot be selected)
              </q-banner>
            </div>

            <q-stepper-navigation>
              <q-btn
                @click="bulkInsert"
                color="primary"
                :label="`Insert ${selectedCount} Selected Question${selectedCount !== 1 ? 's' : ''}`"
                icon="upload"
                :loading="inserting"
                :disable="selectedCount === 0"
              />
              <q-btn flat @click="step = 3" label="Back" class="q-ml-sm" />
            </q-stepper-navigation>
          </q-step>
        </q-stepper>
      </q-card-section>
    </q-card>
  </q-dialog>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { useQuasar } from 'quasar';
import axios from 'axios';

const $q = useQuasar();

const props = defineProps({
  modelValue: Boolean,
  subjectId: [Number, String],
  subjects: {
    type: Array,
    default: () => []
  },
  // New props for reuse
  emitDataOnly: {
    type: Boolean,
    default: false
  },
  subjectName: {
    type: String,
    default: ''
  },
  gradeName: {
    type: String,
    default: ''
  },
  examTitle: {
    type: String,
    default: ''
  }
});

const emit = defineEmits(['update:modelValue', 'success', 'imported']);

const showDialog = computed({
  get: () => props.modelValue,
  set: (val) => emit('update:modelValue', val)
});

const step = ref(1);
const topics = ref([]);
const validating = ref(false);
const inserting = ref(false);
const topicMode = ref('custom'); // Default to custom topic

const config = ref({
  count: 10,
  types: ['mcq'],
  difficulties: [],
  bloomLevels: [],
  topicId: null,
  customTopic: '',
  learningObjectives: '',
  customInstructions: '',
  latex: true, // Default to enabled
  language: 'English'
});

// Watch for dialog open to set custom topic from exam title
import { watch } from 'vue';
watch(() => props.modelValue, (val) => {
  if (val && props.examTitle && !config.value.customTopic) {
    config.value.customTopic = props.examTitle;
  }
});

const pasteFromClipboard = async () => {
  try {
    const text = await navigator.clipboard.readText();
    if (text) {
      aiResponse.value = text;
      $q.notify({
        type: 'positive',
        message: 'Pasted from clipboard',
        icon: 'content_paste',
        position: 'top'
      });
    } else {
      $q.notify({
        type: 'warning',
        message: 'Clipboard is empty',
        position: 'top'
      });
    }
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: 'Failed to read from clipboard',
      caption: 'Please allow clipboard access or paste manually',
      position: 'top'
    });
  }
};

const generatedPrompt = ref('');
const aiResponse = ref('');
const validatedQuestions = ref([]);

const questionTypeOptions = [
  { value: 'mcq', label: 'Multiple Choice (MCQ)' },
  { value: 'true_false', label: 'True/False' },
  { value: 'short', label: 'Short Answer' },
  { value: 'long', label: 'Long Answer' }
];

const difficultyOptions = ['easy', 'medium', 'hard'];

const bloomLevelOptions = [
  { value: 'remember', label: 'Remember' },
  { value: 'understand', label: 'Understand' },
  { value: 'apply', label: 'Apply' },
  { value: 'analyze', label: 'Analyze' },
  { value: 'evaluate', label: 'Evaluate' },
  { value: 'create', label: 'Create' }
];

const previewColumns = [
  { name: 'number', label: '#', field: 'index', align: 'center', style: 'width: 50px' },
  { name: 'question_text', label: 'Question', field: 'question_text', align: 'left' },
  { name: 'question_type', label: 'Type', field: 'question_type', align: 'center' },
  { name: 'difficulty', label: 'Difficulty', field: 'difficulty', align: 'center' },
  { name: 'marks', label: 'Marks', field: 'marks', align: 'center' },
  { name: 'status', label: 'Status', align: 'center' }
];

const invalidCount = computed(() => {
  return validatedQuestions.value.filter(q => !q.valid).length;
});

const selectedQuestions = ref([]);

const selectedCount = computed(() => {
  return selectedQuestions.value.length;
});

// Load topics when subject is set
if (props.subjectId && props.subjects.length > 0) {
  const subject = props.subjects.find(s => s.id == props.subjectId);
  if (subject?.curricula) {
    topics.value = subject.curricula.flatMap(c => c.topics || []);
  }
}

const generatePrompt = () => {
  const subject = props.subjects.length > 0 
    ? props.subjects.find(s => s.id == props.subjectId)
    : null;
    
  const subjectName = subject?.name || props.subjectName || 'General';
  const gradeText = props.gradeName ? ` for Grade ${props.gradeName}` : '';
  
  const topic = topics.value.find(t => t.id === config.value.topicId);
  
  // Determine topic text
  const topicText = topicMode.value === 'custom' && config.value.customTopic 
    ? config.value.customTopic 
    : (topic ? topic.name : null);
  
  const promptParts = [
    `Generate ${config.value.count} educational questions for the subject "${subjectName}"${gradeText}.`,
    topicText ? `Focus on the topic: "${topicText}".` : '',
    config.value.learningObjectives ? `Learning Objectives: ${config.value.learningObjectives}` : '',
    '',
    `Question types: ${config.value.types.join(', ')}.`,
    config.value.difficulties.length > 0 ? `Difficulty levels: ${config.value.difficulties.join(', ')}.` : 'Mixed difficulty levels.',
    config.value.bloomLevels.length > 0 ? `Bloom's taxonomy levels: ${config.value.bloomLevels.join(', ')}.` : '',
    config.value.latex ? 'Use LaTeX notation for mathematical formulas (wrap in $ or $$).' : '',
    `Language: ${config.value.language}.`,
    '',
    config.value.customInstructions ? `Additional Instructions:\n${config.value.customInstructions}` : '',
    '',
    'Return ONLY a valid JSON object in this exact format:',
    '```json',
    JSON.stringify({
      questions: [
        {
          question_text: "Example question text here",
          question_type: "mcq",
          options: { A: "Option A", B: "Option B", C: "Option C", D: "Option D" },
          correct_answer: ["B"],
          difficulty: "medium",
          bloom_level: "understand",
          marks: 1
        }
      ]
    }, null, 2),
    '```',
    '',
    'Important rules:',
    '- For MCQ: provide options as object with keys A, B, C, D',
    '- For True/False: options should be {"true": "True", "false": "False"}',
    '- For Short/Long Answer: set options to {} and correct_answer to ["N/A"]',
    '- correct_answer must be an array of keys',
    '- question_type must be one of: mcq, true_false, short, long',
    '- difficulty must be one of: easy, medium, hard',
    '- bloom_level is optional, one of: remember, understand, apply, analyze, evaluate, create',
    '- marks should be a positive integer'
  ];

  generatedPrompt.value = promptParts.filter(Boolean).join('\n');
  step.value = 2;
};

const copyPrompt = async () => {
  try {
    await navigator.clipboard.writeText(generatedPrompt.value);
    $q.notify({
      type: 'positive',
      message: 'Prompt copied to clipboard!',
      icon: 'content_copy',
      position: 'top'
    });
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: 'Failed to copy to clipboard',
      position: 'top'
    });
  }
};

const validateResponse = () => {
  validating.value = true;
  
  try {
    // Try to parse JSON
    let data;
    const cleaned = aiResponse.value.trim();
    
    // Remove markdown code blocks if present
    const jsonMatch = cleaned.match(/```(?:json)?\s*([\s\S]*?)```/);
    if (jsonMatch) {
      data = JSON.parse(jsonMatch[1]);
    } else {
      data = JSON.parse(cleaned);
    }

    if (!data.questions || !Array.isArray(data.questions)) {
      throw new Error('Invalid format: "questions" array not found');
    }

    // Validate each question
    validatedQuestions.value = data.questions.map((q, index) => {
      const errors = [];
      
      if (!q.question_text) errors.push('Missing question_text');
      if (!['mcq', 'true_false', 'short', 'long'].includes(q.question_type)) {
        errors.push('Invalid question_type');
      }
      if (!q.difficulty || !['easy', 'medium', 'hard'].includes(q.difficulty)) {
        errors.push('Invalid difficulty');
      }
      if (!q.correct_answer || !Array.isArray(q.correct_answer)) {
        errors.push('Invalid correct_answer');
      }
      if (!q.marks || q.marks < 1) errors.push('Invalid marks');

      return {
        ...q,
        index,
        valid: errors.length === 0,
        errors: errors.join(', '),
        subject_id: props.subjectId,
        topic_id: config.value.topicId
      };
    });

    step.value = 4;
    
    // Auto-select all valid questions
    selectedQuestions.value = validatedQuestions.value.filter(q => q.valid);
    
    $q.notify({
      type: 'positive',
      message: `Validated ${validatedQuestions.value.length} questions`,
      position: 'top'
    });
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: 'Invalid JSON format',
      caption: error.message,
      position: 'top'
    });
  } finally {
    validating.value = false;
  }
};

// Selection functions
const selectAll = () => {
  selectedQuestions.value = validatedQuestions.value.filter(q => q.valid);
};

const selectNone = () => {
  selectedQuestions.value = [];
};

const selectInverse = () => {
  const currentSelected = new Set(selectedQuestions.value.map(q => q.index));
  selectedQuestions.value = validatedQuestions.value.filter(q => 
    q.valid && !currentSelected.has(q.index)
  );
};

const bulkInsert = () => {
  inserting.value = true;
  
  // Use only selected valid questions and add custom_group from config
  const questionsToInsert = selectedQuestions.value
    .filter(q => q.valid)
    .map(q => ({
      ...q,
      custom_group: config.value.customTopic // Use the custom topic (which defaults to exam title) as the group
    }));
  
  if (questionsToInsert.length === 0) {
    $q.notify({
      type: 'warning',
      message: 'No questions selected',
      position: 'top'
    });
    inserting.value = false;
    return;
  }
  
  // If in emit mode (client-side only), just emit the question data
  if (props.emitDataOnly) {
    emit('imported', questionsToInsert);
    $q.notify({
      type: 'positive',
      message: `${questionsToInsert.length} questions ready to add!`,
      icon: 'check_circle',
      position: 'top'
    });
    showDialog.value = false;
    inserting.value = false;
    return;
  }
  
  // Server-side creation via axios
  axios.post(route('qu.questions.bulk-store'), {
    questions: questionsToInsert
  })
  .then(response => {
    // Check for created items
    const created = response.data.created || [];
    const skipped = response.data.skipped || [];
    
    if (created.length > 0) {
      $q.notify({
        type: 'positive',
        message: `Successfully created ${created.length} questions!`,
        icon: 'check_circle',
        position: 'top'
      });
      
      // Emit success with created questions
      emit('success', created);
      showDialog.value = false;
    }
    
    if (skipped.length > 0) {
      $q.notify({
        type: 'warning',
        message: `Skipped ${skipped.length} duplicates`,
        position: 'top'
      });
    }
  })
  .catch(error => {
    console.error('Bulk insert error:', error);
    $q.notify({
      type: 'negative',
      message: 'Failed to create questions',
      caption: error.response?.data?.message || error.message,
      position: 'top'
    });
  })
  .finally(() => {
    inserting.value = false;
  });
};

const truncate = (text, length) => {
  return text && text.length > length ? text.substring(0, length) + '...' : text;
};

// Smart Presets
const applyPreset = (type) => {
  const presets = {
    exam: {
      count: 20,
      types: ['mcq', 'short'],
      difficulties: ['medium', 'hard'],
      bloomLevels: ['apply', 'analyze', 'evaluate'],
      customInstructions: 'Create exam-level questions that test deep understanding and application of concepts.'
    },
    homework: {
      count: 15,
      types: ['mcq', 'short', 'long'],
      difficulties: ['easy', 'medium'],
      bloomLevels: ['remember', 'understand', 'apply'],
      customInstructions: 'Create practice questions suitable for homework assignments with varying difficulty.'
    },
    quiz: {
      count: 10,
      types: ['mcq', 'true_false'],
      difficulties: ['easy', 'medium'],
      bloomLevels: ['remember', 'understand'],
      customInstructions: 'Create quick assessment questions for a short quiz.'
    },
    practice: {
      count: 25,
      types: ['mcq', 'true_false', 'short'],
      difficulties: [],
      bloomLevels: [],
      customInstructions: 'Create diverse practice questions covering all difficulty levels for student practice.'
    }
  };

  const preset = presets[type];
  if (preset) {
    Object.assign(config.value, preset);
    $q.notify({
      type: 'info',
      message: `Applied "${type}" preset`,
      position: 'top'
    });
  }
};

// Add instruction helper
const addInstruction = (instruction) => {
  if (config.value.customInstructions) {
    config.value.customInstructions += '\n- ' + instruction;
  } else {
    config.value.customInstructions = '- ' + instruction;
  }
  $q.notify({
    type: 'positive',
    message: 'Instruction added',
    position: 'top'
  });
};
</script>
