<template>
  <Head title="Import Questions" />
  <div class="question-import-page">
    <div class="q-pa-md">
      <!-- Header -->
      <div class="row items-center q-mb-lg">
        <q-btn
          flat
          icon="arrow_back"
          label="Back to Question Bank"
          @click="goBack"
          class="q-mr-md"
        />
        <div>
          <h4 class="q-ma-none">Import Questions</h4>
          <p class="text-grey-7 q-mb-none">Easy import - Just provide questions and answers, we'll handle the rest!</p>
        </div>
      </div>

      <div class="row justify-center">
        <div class="col-12 col-md-10">
          <!-- Step 1: Set Default Metadata -->
          <q-card flat bordered class="q-mb-md">
            <q-card-section class="bg-primary text-white">
              <div class="text-h6">
                <q-icon name="settings" class="q-mr-sm" />
                Step 1: Choose Settings for All Questions
              </div>
              <p class="q-mb-none text-caption">These settings will apply to all questions you import</p>
            </q-card-section>
            
            <q-card-section>
              <div class="row q-col-gutter-md">
                <!-- Question Type -->
                <div class="col-12 col-md-6">questionTypes:{{ questionTypes }}
                  <q-select
                    v-model="defaultValues.question_type_id"
                    :options="questionTypes"
                    option-value="id"
                    option-label="name"
                    emit-value
                    map-options
                    outlined
                    label="Question Type *"
                    hint="Required - What type of questions are you importing?"
                    :rules="[val => !!val || 'Question type is required']"
                  >
                    <template v-slot:prepend>
                      <q-icon name="quiz" color="primary" />
                    </template>
                  </q-select>
                </div>

                <!-- Difficulty -->
                <div class="col-12 col-md-6">
                  <q-select
                    v-model="defaultValues.difficulty"
                    :options="difficultyOptions"
                    outlined
                    label="Difficulty Level"
                    clearable
                    hint="Optional - How hard are these questions?"
                  >
                    <template v-slot:prepend>
                      <q-icon name="speed" color="warning" />
                    </template>
                  </q-select>
                </div>

                <!-- Grade -->
                <div class="col-12 col-md-4">
                  <q-select
                    v-model="defaultValues.grade_id"
                    :options="grades"
                    option-value="id"
                    option-label="name"
                    emit-value
                    map-options
                    outlined
                    label="Grade Level"
                    clearable
                    hint="Optional - Which grade?"
                    @update:model-value="onDefaultGradeChange"
                  >
                    <template v-slot:prepend>
                      <q-icon name="school" color="positive" />
                    </template>
                  </q-select>
                </div>

                <!-- Subject -->
                <div class="col-12 col-md-4">
                  <q-select
                    v-model="defaultValues.subject_id"
                    :options="filteredSubjects"
                    option-value="id"
                    option-label="name"
                    emit-value
                    map-options
                    outlined
                    label="Subject"
                    clearable
                    hint="Optional - Which subject?"
                    :disable="!defaultValues.grade_id && subjects.length > 0"
                    @update:model-value="onDefaultSubjectChange"
                  >
                    <template v-slot:prepend>
                      <q-icon name="subject" color="info" />
                    </template>
                  </q-select>
                </div>

                <!-- Topic -->
                <div class="col-12 col-md-4">
                  <q-select
                    v-model="defaultValues.topic_id"
                    :options="filteredTopics"
                    option-value="id"
                    option-label="name"
                    emit-value
                    map-options
                    outlined
                    label="Topic"
                    clearable
                    hint="Optional - Specific topic?"
                    :disable="!defaultValues.subject_id && topics.length > 0"
                  >
                    <template v-slot:prepend>
                      <q-icon name="label" color="secondary" />
                    </template>
                  </q-select>
                </div>

                <!-- Bloom Level -->
                <div class="col-12 col-md-6">
                  <q-select
                    v-model="defaultValues.bloom_level"
                    :options="bloomLevels"
                    emit-value
                    map-options
                    outlined
                    label="Bloom's Taxonomy Level"
                    clearable
                    hint="Optional - Cognitive complexity"
                  >
                    <template v-slot:prepend>
                      <q-icon name="psychology" color="purple" />
                    </template>
                  </q-select>
                </div>

                <!-- Status -->
                <div class="col-12 col-md-6">
                  <q-select
                    v-model="defaultValues.status"
                    :options="statusOptions"
                    outlined
                    label="Status"
                    hint="Questions will be created with this status"
                  >
                    <template v-slot:prepend>
                      <q-icon name="flag" color="orange" />
                    </template>
                  </q-select>
                </div>
              </div>
            </q-card-section>
          </q-card>

          <!-- Step 2: Upload File -->
          <q-card flat bordered class="q-mb-md">
            <q-card-section class="bg-secondary text-white">
              <div class="text-h6">
                <q-icon name="upload_file" class="q-mr-sm" />
                Step 2: Provide Your Questions
              </div>
              <p class="q-mb-none text-caption">Choose your preferred method!</p>
            </q-card-section>
            
            <q-card-section>
              <!-- Import Method Tabs -->
              <q-tabs
                v-model="importMethod"
                dense
                class="text-grey-7 q-mb-md"
                active-color="primary"
                indicator-color="primary"
                align="justify"
              >
                <q-tab name="file" icon="upload_file" label="Upload File" />
                <q-tab name="paste" icon="content_paste" label="Paste from Excel" />
                <q-tab name="json" icon="code" label="Paste JSON" />
              </q-tabs>

              <q-separator class="q-mb-md" />

              <!-- File Upload Tab -->
              <q-tab-panels v-model="importMethod" animated>
                <q-tab-panel name="file">
                  <q-file
                    v-model="file"
                    outlined
                    label="Choose file (Excel or CSV)"
                    accept=".xlsx,.xls,.csv"
                    max-file-size="10485760"
                    @update:model-value="handleFileSelect"
                    class="q-mb-md"
                  >
                    <template v-slot:prepend>
                      <q-icon name="attach_file" />
                    </template>
                    <template v-slot:hint>
                      Supported: .xlsx, .xls, .csv (Max 10MB)
                    </template>
                  </q-file>

                  <div class="row q-gutter-sm">
                    <q-btn
                      flat
                      color="primary"
                      icon="download"
                      label="Download Template"
                      @click="downloadTemplate"
                    />
                    <q-btn
                      flat
                      color="info"
                      icon="help"
                      label="View Example"
                      @click="showExample = true"
                    />
                  </div>
                </q-tab-panel>

                <!-- Paste from Excel Tab -->
                <q-tab-panel name="paste">
                  <div v-if="!selectedQuestionType" class="text-center q-pa-lg">
                    <q-icon name="arrow_upward" size="48px" color="grey-5" />
                    <p class="text-grey-7">Please select a question type in Step 1 first</p>
                  </div>

                  <div v-else>
                    <div class="text-body2 q-mb-md">
                      <q-icon name="info" color="info" />
                      Copy cells from Excel/Google Sheets and paste here
                    </div>

                    <!-- Example Data Section -->
                    <q-card flat bordered class="bg-green-1 q-mb-md">
                      <q-card-section>
                        <div class="row items-center justify-between q-mb-sm">
                          <div class="text-subtitle2 text-green-9">
                            <q-icon name="table_chart" class="q-mr-xs" />
                            Example Data for {{ selectedQuestionType.name }}
                          </div>
                          <q-btn
                            flat
                            dense
                            size="sm"
                            color="green-9"
                            icon="content_copy"
                            label="Copy Example"
                            @click="copyExcelExample"
                          />
                        </div>
                        <div class="text-caption text-green-9 q-mb-sm">
                          Copy this example, paste in Excel to see the format, then replace with your questions:
                        </div>
                        <pre class="example-data">{{ excelExampleData }}</pre>
                      </q-card-section>
                    </q-card>
                    
                    <q-input
                      v-model="pastedData"
                      type="textarea"
                      outlined
                      label="Paste your data here (Ctrl+V)"
                      rows="10"
                      :placeholder="excelPlaceholder"
                      class="q-mb-md"
                    >
                      <template v-slot:prepend>
                        <q-icon name="content_paste" />
                      </template>
                    </q-input>

                    <div class="row q-gutter-sm">
                      <q-btn
                        color="primary"
                        icon="check"
                        label="Process Pasted Data"
                        @click="processPastedData"
                        :disable="!pastedData"
                      />
                      <q-btn
                        flat
                        color="grey-7"
                        icon="clear"
                        label="Clear"
                        @click="pastedData = ''"
                      />
                    </div>

                    <q-banner class="bg-blue-1 text-blue-9 q-mt-md">
                      <template v-slot:avatar>
                        <q-icon name="lightbulb" color="blue" />
                      </template>
                      <strong>Tip:</strong> Click "Copy Example" above, paste in Excel to see the format, then replace with your questions!
                    </q-banner>
                  </div>
                </q-tab-panel>

                <!-- Paste JSON Tab -->
                <q-tab-panel name="json">
                  <div v-if="!selectedQuestionType" class="text-center q-pa-lg">
                    <q-icon name="arrow_upward" size="48px" color="grey-5" />
                    <p class="text-grey-7">Please select a question type in Step 1 first</p>
                  </div>

                  <div v-else>
                    <div class="row q-gutter-sm q-mb-md">
                      <q-btn
                        color="purple"
                        icon="smart_toy"
                        label="Copy AI Prompt"
                        @click="copyAIPrompt"
                      />
                      <q-btn
                        flat
                        color="green"
                        icon="content_copy"
                        label="Copy JSON Example"
                        @click="copyJsonExample"
                      />
                    </div>

                    <q-input
                      v-model="jsonData"
                      type="textarea"
                      outlined
                      label="Paste JSON here"
                      rows="12"
                      :placeholder="jsonPlaceholder"
                      class="q-mb-md"
                      style="font-family: monospace;"
                    >
                      <template v-slot:prepend>
                        <q-icon name="code" />
                      </template>
                    </q-input>

                    <div class="row q-gutter-sm">
                      <q-btn
                        color="primary"
                        icon="check"
                        label="Process JSON"
                        @click="processJsonData"
                        :disable="!jsonData"
                      />
                      <q-btn
                        flat
                        color="grey-7"
                        icon="clear"
                        label="Clear"
                        @click="jsonData = ''"
                      />
                    </div>

                    <q-banner class="bg-purple-1 text-purple-9 q-mt-md">
                      <template v-slot:avatar>
                        <q-icon name="auto_awesome" color="purple" />
                      </template>
                      <strong>AI Tip:</strong> Click "Copy AI Prompt" and paste it into ChatGPT/Claude to generate questions in the correct format!
                    </q-banner>
                  </div>
                </q-tab-panel>
              </q-tab-panels>
            </q-card-section>
          </q-card>

          <!-- Step 3: Preview & Import -->
          <q-card v-if="preview" flat bordered class="q-mb-md">
            <q-card-section class="bg-positive text-white">
              <div class="text-h6">
                <q-icon name="preview" class="q-mr-sm" />
                Step 3: Review & Import
              </div>
            </q-card-section>
            
            <q-card-section>
              <div class="row q-gutter-md q-mb-md">
                <q-chip color="positive" text-color="white" icon="check_circle" size="md">
                  {{ preview.valid }} Ready to Import
                </q-chip>
                <q-chip v-if="preview.warnings > 0" color="warning" text-color="white" icon="warning" size="md">
                  {{ preview.warnings }} Warnings
                </q-chip>
                <q-chip v-if="preview.errors > 0" color="negative" text-color="white" icon="error" size="md">
                  {{ preview.errors }} Errors
                </q-chip>
              </div>

              <q-separator class="q-my-md" />

              <div class="text-subtitle2 q-mb-sm">
                Preview All Questions ({{ previewQuestions.length }} total):
              </div>
              
              <!-- Scrollable list with all questions -->
              <div style="max-height: 500px; overflow-y: auto;" class="q-mb-md">
                <q-list bordered separator>
                  <q-item v-for="(question, index) in previewQuestions" :key="index">
                    <q-item-section avatar>
                      <q-avatar :color="question.valid ? 'positive' : 'negative'" text-color="white" size="md">
                        {{ index + 1 }}
                      </q-avatar>
                    </q-item-section>
                    <q-item-section>
                      <q-item-label class="text-weight-medium">
                        {{ question.question_text }}
                      </q-item-label>
                      
                      <!-- Show options if available -->
                      <div v-if="question.option_a" class="q-mt-xs">
                        <q-chip 
                          v-for="(option, key) in getQuestionOptions(question)" 
                          :key="key"
                          size="sm"
                          :color="question.correct_answer && question.correct_answer.includes(key) ? 'positive' : 'grey-3'"
                          :text-color="question.correct_answer && question.correct_answer.includes(key) ? 'white' : 'grey-8'"
                          dense
                          class="q-mr-xs"
                        >
                          <strong>{{ key }}:</strong> {{ option }}
                        </q-chip>
                      </div>
                      
                      <q-item-label caption class="q-mt-xs">
                        Question #{{ index + 1 }} | {{ selectedQuestionType?.name || 'Unknown Type' }}
                        <span v-if="question.correct_answer" class="q-ml-sm">
                          | Correct: {{ question.correct_answer }}
                        </span>
                      </q-item-label>
                      
                      <q-item-label v-if="question.error" caption class="text-negative q-mt-xs">
                        <q-icon name="error" size="xs" /> {{ question.error }}
                      </q-item-label>
                    </q-item-section>
                    
                    <q-item-section side>
                      <q-icon
                        :name="question.valid !== false ? 'check_circle' : 'error'"
                        :color="question.valid !== false ? 'positive' : 'negative'"
                        size="md"
                      />
                    </q-item-section>
                  </q-item>
                </q-list>
              </div>
              
              <!-- Error details if any -->
              <div v-if="preview.errors && preview.errors.length > 0" class="q-mt-md">
                <q-expansion-item
                  icon="error"
                  label="Error Details"
                  :caption="`${preview.errors.length} questions failed validation`"
                  header-class="bg-negative text-white"
                >
                  <q-card>
                    <q-card-section>
                      <q-list separator>
                        <q-item v-for="(error, idx) in preview.errors" :key="idx">
                          <q-item-section>
                            <q-item-label>Row {{ error.row }}</q-item-label>
                            <q-item-label caption class="text-negative">
                              {{ error.message }}
                            </q-item-label>
                          </q-item-section>
                        </q-item>
                      </q-list>
                    </q-card-section>
                  </q-card>
                </q-expansion-item>
              </div>
            </q-card-section>

            <q-card-actions align="right" class="q-pa-md">
              <q-btn
                flat
                label="Cancel"
                color="grey-7"
                icon="close"
                @click="cancelImport"
              />
              <q-btn
                unelevated
                label="Import Questions"
                color="primary"
                icon="cloud_upload"
                :disable="preview.valid === 0 || !defaultValues.question_type_id"
                :loading="importing"
                @click="confirmImport"
              />
            </q-card-actions>
          </q-card>

          <!-- Instructions -->
          <q-card flat bordered>
            <q-card-section>
              <div class="text-h6 q-mb-md">
                <q-icon name="info" color="info" class="q-mr-sm" />
                Simple File Format
              </div>
              
              <div class="text-body2">
                <p class="text-weight-medium">Your file only needs these columns:</p>
                
                <q-list bordered class="q-mb-md">
                  <q-item>
                    <q-item-section avatar>
                      <q-icon name="check" color="positive" />
                    </q-item-section>
                    <q-item-section>
                      <q-item-label><code>question_text</code></q-item-label>
                      <q-item-label caption>The question itself</q-item-label>
                    </q-item-section>
                  </q-item>
                  
                  <q-item v-if="selectedQuestionType?.has_options">
                    <q-item-section avatar>
                      <q-icon name="check" color="positive" />
                    </q-item-section>
                    <q-item-section>
                      <q-item-label><code>option_a, option_b, option_c, option_d</code></q-item-label>
                      <q-item-label caption>Answer choices (for multiple choice questions)</q-item-label>
                    </q-item-section>
                  </q-item>
                  
                  <q-item v-if="selectedQuestionType?.has_options">
                    <q-item-section avatar>
                      <q-icon name="check" color="positive" />
                    </q-item-section>
                    <q-item-section>
                      <q-item-label><code>correct_answer</code></q-item-label>
                      <q-item-label caption>Which option is correct (A, B, C, or D)</q-item-label>
                    </q-item-section>
                  </q-item>
                </q-list>

                <q-banner class="bg-blue-1 text-blue-9">
                  <template v-slot:avatar>
                    <q-icon name="lightbulb" color="blue" />
                  </template>
                  <strong>That's it!</strong> No need for IDs or complex data. 
                  We'll use the settings you chose in Step 1 for all questions.
                </q-banner>
              </div>
            </q-card-section>
          </q-card>
        </div>
      </div>
    </div>

    <!-- Example Dialog -->
    <q-dialog v-model="showExample">
      <q-card style="min-width: 600px">
        <q-card-section class="bg-info text-white">
          <div class="text-h6">Example File Format</div>
        </q-card-section>

        <q-card-section>
          <p>Here's what your CSV/Excel file should look like:</p>
          
          <q-markup-table flat bordered>
            <thead>
              <tr>
                <th>question_text</th>
                <th>option_a</th>
                <th>option_b</th>
                <th>option_c</th>
                <th>option_d</th>
                <th>correct_answer</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>What is 2+2?</td>
                <td>2</td>
                <td>3</td>
                <td>4</td>
                <td>5</td>
                <td>C</td>
              </tr>
              <tr>
                <td>What is the capital of France?</td>
                <td>London</td>
                <td>Paris</td>
                <td>Berlin</td>
                <td>Madrid</td>
                <td>B</td>
              </tr>
            </tbody>
          </q-markup-table>

          <q-banner class="q-mt-md bg-green-1 text-green-9">
            <template v-slot:avatar>
              <q-icon name="check_circle" color="green" />
            </template>
            Simple and clean! Just questions and answers.
          </q-banner>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Close" color="primary" v-close-popup />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- JSON Example Dialog -->
    <q-dialog v-model="showJsonExample">
      <q-card style="min-width: 700px; max-width: 800px">
        <q-card-section class="bg-purple text-white">
          <div class="text-h6">
            <q-icon name="code" class="q-mr-sm" />
            JSON Format Example
          </div>
        </q-card-section>

        <q-card-section>
          <p class="text-body2">Copy this format and modify with your questions:</p>
          
          <q-input
            :model-value="jsonExampleText"
            type="textarea"
            outlined
            readonly
            rows="15"
            class="q-mb-md"
            style="font-family: monospace; font-size: 12px;"
          >
            <template v-slot:append>
              <q-btn
                flat
                round
                dense
                icon="content_copy"
                @click="copyJsonExample"
              >
                <q-tooltip>Copy to clipboard</q-tooltip>
              </q-btn>
            </template>
          </q-input>

          <q-banner class="bg-purple-1 text-purple-9">
            <template v-slot:avatar>
              <q-icon name="auto_awesome" color="purple" />
            </template>
            <strong>Pro Tip:</strong> Use the "Copy AI Prompt" button to generate this automatically with AI!
          </q-banner>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Close" color="primary" v-close-popup />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { router, Head } from '@inertiajs/vue3';
import { useQuasar } from 'quasar';
import axios from 'axios';

const $q = useQuasar();

// State
const file = ref(null);
const preview = ref(null);
const importing = ref(false);
const showExample = ref(false);
const showJsonExample = ref(false);
const importMethod = ref('file');
const pastedData = ref('');
const jsonData = ref('');

// Default values that user selects
const defaultValues = ref({
  question_type_id: null,
  difficulty: null,
  grade_id: null,
  subject_id: null,
  topic_id: null,
  bloom_level: null,
  status: 'draft'
});

// Metadata
const questionTypes = ref([]);
const grades = ref([]);
const subjects = ref([]);
const topics = ref([]);

// Options
const difficultyOptions = ['Easy', 'Medium', 'Hard'];
const statusOptions = ['draft', 'active', 'archived', 'review'];
const bloomLevels = [
  { label: 'Level 1 - Remember', value: 1 },
  { label: 'Level 2 - Understand', value: 2 },
  { label: 'Level 3 - Apply', value: 3 },
  { label: 'Level 4 - Analyze', value: 4 },
  { label: 'Level 5 - Evaluate', value: 5 },
  { label: 'Level 6 - Create', value: 6 }
];

// Computed
const selectedQuestionType = computed(() => {
  return questionTypes.value.find(qt => qt.id === defaultValues.value.question_type_id);
});

const filteredSubjects = computed(() => {
  if (!defaultValues.value.grade_id) return subjects.value;
  return subjects.value.filter(s => s.grade_id === defaultValues.value.grade_id);
});

const filteredTopics = computed(() => {
  if (!defaultValues.value.subject_id) return topics.value;
  return topics.value.filter(t => t.subject_id === defaultValues.value.subject_id);
});

const previewQuestions = computed(() => {
  if (!preview.value || !preview.value.questions) return [];
  return preview.value.questions;
});

const jsonExampleText = computed(() => {
  if (selectedQuestionType.value?.has_options) {
    return `[
  {
    "question_text": "What is 2+2?",
    "option_a": "2",
    "option_b": "3",
    "option_c": "4",
    "option_d": "5",
    "correct_answer": "C"
  },
  {
    "question_text": "What is the capital of France?",
    "option_a": "London",
    "option_b": "Paris",
    "option_c": "Berlin",
    "option_d": "Madrid",
    "correct_answer": "B"
  },
  {
    "question_text": "Which planet is closest to the Sun?",
    "option_a": "Venus",
    "option_b": "Earth",
    "option_c": "Mercury",
    "option_d": "Mars",
    "correct_answer": "C"
  }
]`;
  } else {
    return `[
  {
    "question_text": "What is the chemical symbol for water?"
  },
  {
    "question_text": "Who wrote Romeo and Juliet?"
  },
  {
    "question_text": "What is the largest ocean on Earth?"
  }
]`;
  }
});

const excelExampleData = computed(() => {
  if (selectedQuestionType.value?.has_options) {
    return `question_text\toption_a\toption_b\toption_c\toption_d\tcorrect_answer
What is 2+2?\t2\t3\t4\t5\tC
What is the capital of France?\tLondon\tParis\tBerlin\tMadrid\tB
Which planet is closest to the Sun?\tVenus\tEarth\tMercury\tMars\tC`;
  } else {
    return `question_text
What is the chemical symbol for water?
Who wrote Romeo and Juliet?
What is the largest ocean on Earth?`;
  }
});

const jsonPlaceholder = computed(() => {
  if (selectedQuestionType.value?.has_options) {
    return `Paste JSON array here...

Example:
[
  {
    "question_text": "What is 2+2?",
    "option_a": "2",
    "option_b": "3",
    "option_c": "4",
    "option_d": "5",
    "correct_answer": "C"
  }
]`;
  } else {
    return `Paste JSON array here...

Example:
[
  {
    "question_text": "What is the chemical symbol for water?"
  }
]`;
  }
});

const excelPlaceholder = computed(() => {
  if (selectedQuestionType.value?.has_options) {
    return `Copy from Excel and paste here...

Example (tab-separated):
question_text	option_a	option_b	option_c	option_d	correct_answer
What is 2+2?	2	3	4	5	C
Capital of France?	London	Paris	Berlin	Madrid	B`;
  } else {
    return `Copy from Excel and paste here...

Example:
question_text
What is the chemical symbol for water?
Who wrote Romeo and Juliet?`;
  }
});

// Methods
const goBack = () => {
  router.visit('/questions');
};

const getQuestionOptions = (question) => {
  const options = {};
  const optionKeys = ['A', 'B', 'C', 'D', 'E', 'F'];
  
  optionKeys.forEach(key => {
    const optionKey = `option_${key.toLowerCase()}`;
    if (question[optionKey]) {
      options[key] = question[optionKey];
    }
  });
  
  return options;
};

const onDefaultGradeChange = () => {
  defaultValues.value.subject_id = null;
  defaultValues.value.topic_id = null;
};

const onDefaultSubjectChange = () => {
  defaultValues.value.topic_id = null;
};

const handleFileSelect = async (selectedFile) => {
  if (!selectedFile) {
    preview.value = null;
    return;
  }

  // Validate question type is selected
  if (!defaultValues.value.question_type_id) {
    $q.notify({
      type: 'warning',
      message: 'Please select a question type first',
      caption: 'Choose the question type in Step 1 before uploading'
    });
    file.value = null;
    return;
  }

  // Validate file type
  const validExtensions = ['.xlsx', '.xls', '.csv'];
  const fileName = selectedFile.name.toLowerCase();
  const isValid = validExtensions.some(ext => fileName.endsWith(ext));

  if (!isValid) {
    $q.notify({
      type: 'negative',
      message: 'Invalid file type',
      caption: 'Please upload an Excel (.xlsx, .xls) or CSV (.csv) file'
    });
    file.value = null;
    return;
  }

  await parseFile(selectedFile);
};

const parseFile = async (selectedFile) => {
  try {
    $q.loading.show({ message: 'Parsing file...' });
    
    // Read and parse the file to JSON
    const questions = await readFileAsJson(selectedFile);
    
    if (questions.length === 0) {
      $q.notify({
        type: 'warning',
        message: 'No questions found in file',
        caption: 'Please check your file format'
      });
      file.value = null;
      return;
    }
    
    // Send as JSON to backend
    await processQuestionsArray(questions);
    
  } catch (error) {
    console.error('Failed to parse file:', error);
    $q.notify({
      type: 'negative',
      message: 'Failed to parse file',
      caption: error.message || 'Please check your file format'
    });
    file.value = null;
  } finally {
    $q.loading.hide();
  }
};

const readFileAsJson = (file) => {
  return new Promise((resolve, reject) => {
    const reader = new FileReader();
    const fileName = file.name.toLowerCase();
    
    reader.onload = (e) => {
      try {
        const content = e.target.result;
        let questions = [];
        
        if (fileName.endsWith('.csv') || fileName.endsWith('.txt')) {
          // Parse CSV
          questions = parseCSV(content);
        } else if (fileName.endsWith('.xlsx') || fileName.endsWith('.xls')) {
          // For Excel files, we need a library - for now, ask user to paste
          reject(new Error('Excel files: Please copy the data and use "Paste from Excel" tab instead'));
          return;
        }
        
        resolve(questions);
      } catch (error) {
        reject(error);
      }
    };
    
    reader.onerror = () => reject(new Error('Failed to read file'));
    reader.readAsText(file);
  });
};

const parseCSV = (csvText) => {
  const lines = csvText.trim().split('\n');
  if (lines.length < 2) return [];
  
  // Parse header
  const headers = lines[0].split(',').map(h => h.trim().replace(/^"|"$/g, ''));
  
  // Parse rows
  const questions = [];
  for (let i = 1; i < lines.length; i++) {
    const values = lines[i].split(',').map(v => v.trim().replace(/^"|"$/g, ''));
    if (values.length === 0 || !values[0]) continue;
    
    const question = {};
    headers.forEach((header, index) => {
      if (values[index]) {
        question[header] = values[index];
      }
    });
    
    questions.push(question);
  }
  
  return questions;
};

const confirmImport = async () => {
  if (!preview.value || !defaultValues.value.question_type_id) return;

  importing.value = true;
  try {
    // Get the questions from preview (they're already in JSON format)
    const questions = preview.value.questions || [];
    
    if (questions.length === 0) {
      $q.notify({
        type: 'warning',
        message: 'No questions to import',
        caption: 'Please add questions first'
      });
      importing.value = false;
      return;
    }

    console.log('Importing questions:', {
      count: questions.length,
      metadata: defaultValues.value,
      firstQuestion: questions[0]
    });
    
    const response = await axios.post('/api/questions/import', {
      questions: questions,
      preview: false,
      ...defaultValues.value
    });

    console.log('Import response:', response.data);

    if (response.data.success) {
      const data = response.data.data;
      
      $q.notify({
        type: 'positive',
        message: 'Import completed successfully!',
        caption: `Imported ${data.successful} questions`,
        timeout: 3000
      });

      // Redirect to question bank
      router.visit('/questions');
    } else {
      $q.notify({
        type: 'warning',
        message: 'Import completed with issues',
        caption: response.data.message || 'Some questions may not have been imported'
      });
    }
  } catch (error) {
    console.error('Failed to import questions:', error);
    console.error('Error response:', error.response?.data);
    
    const errorMessage = error.response?.data?.message 
      || error.response?.data?.error?.message 
      || error.message;
    
    const errorDetails = error.response?.data?.errors 
      ? JSON.stringify(error.response.data.errors) 
      : '';
    
    $q.notify({
      type: 'negative',
      message: 'Failed to import questions',
      caption: errorMessage + (errorDetails ? ': ' + errorDetails : ''),
      timeout: 5000
    });
  } finally {
    importing.value = false;
  }
};

const cancelImport = () => {
  file.value = null;
  preview.value = null;
};

const processPastedData = () => {
  if (!pastedData.value.trim()) return;
  
  if (!defaultValues.value.question_type_id) {
    $q.notify({
      type: 'warning',
      message: 'Please select a question type first',
      caption: 'Choose the question type in Step 1'
    });
    return;
  }

  try {
    // Parse tab-separated or comma-separated data
    const lines = pastedData.value.trim().split('\n');
    const questions = [];
    
    // Detect separator (tab or comma)
    const firstLine = lines[0];
    const separator = firstLine.includes('\t') ? '\t' : ',';
    
    // Skip header if it looks like a header
    const startIndex = lines[0].toLowerCase().includes('question') ? 1 : 0;
    
    for (let i = startIndex; i < lines.length; i++) {
      const cells = lines[i].split(separator).map(cell => cell.trim().replace(/^"|"$/g, ''));
      
      if (cells.length === 0 || !cells[0]) continue;
      
      const question = {
        question_text: cells[0]
      };
      
      if (selectedQuestionType.value?.has_options) {
        question.option_a = cells[1] || '';
        question.option_b = cells[2] || '';
        question.option_c = cells[3] || '';
        question.option_d = cells[4] || '';
        question.correct_answer = cells[5] || '';
      }
      
      questions.push(question);
    }
    
    if (questions.length === 0) {
      $q.notify({
        type: 'warning',
        message: 'No valid questions found',
        caption: 'Please check your pasted data'
      });
      return;
    }
    
    // Send to backend for processing
    processQuestionsArray(questions);
    
  } catch (error) {
    console.error('Failed to parse pasted data:', error);
    $q.notify({
      type: 'negative',
      message: 'Failed to parse data',
      caption: 'Please check the format and try again'
    });
  }
};

const processJsonData = () => {
  if (!jsonData.value.trim()) return;
  
  if (!defaultValues.value.question_type_id) {
    $q.notify({
      type: 'warning',
      message: 'Please select a question type first',
      caption: 'Choose the question type in Step 1'
    });
    return;
  }

  try {
    const questions = JSON.parse(jsonData.value);
    
    if (!Array.isArray(questions)) {
      throw new Error('JSON must be an array of questions');
    }
    
    if (questions.length === 0) {
      $q.notify({
        type: 'warning',
        message: 'No questions found in JSON',
        caption: 'Please provide at least one question'
      });
      return;
    }
    
    // Send to backend for processing
    processQuestionsArray(questions);
    
  } catch (error) {
    console.error('Failed to parse JSON:', error);
    $q.notify({
      type: 'negative',
      message: 'Invalid JSON format',
      caption: error.message || 'Please check your JSON and try again'
    });
  }
};

const processQuestionsArray = async (questions) => {
  try {
    $q.loading.show({ message: 'Processing questions...' });
    
    const response = await axios.post('/api/questions/import', {
      questions: questions,
      preview: true,
      ...defaultValues.value
    });

    if (response.data.success || response.data.data) {
      preview.value = {
        ...response.data.data,
        questions: questions // Store questions for later import
      };
      
      $q.notify({
        type: 'positive',
        message: 'Questions processed successfully',
        caption: `Found ${preview.value.valid} valid questions`
      });
    }
  } catch (error) {
    console.error('Failed to process questions:', error);
    const errorMessage = error.response?.data?.message || error.response?.data?.error?.message || error.message;
    const errorDetails = error.response?.data?.errors ? JSON.stringify(error.response.data.errors) : '';
    
    $q.notify({
      type: 'negative',
      message: 'Failed to process questions',
      caption: errorMessage + (errorDetails ? ': ' + errorDetails : '')
    });
  } finally {
    $q.loading.hide();
  }
};

const copyAIPrompt = () => {
  const questionType = selectedQuestionType.value;
  
  let prompt = '';
  
  if (questionType?.has_options) {
    prompt = `Generate 10 ${questionType.name} questions in JSON format. Use this exact structure:

[
  {
    "question_text": "What is 2+2?",
    "option_a": "2",
    "option_b": "3",
    "option_c": "4",
    "option_d": "5",
    "correct_answer": "C"
  },
  {
    "question_text": "What is the capital of France?",
    "option_a": "London",
    "option_b": "Paris",
    "option_c": "Berlin",
    "option_d": "Madrid",
    "correct_answer": "B"
  }
]

Requirements:
- Generate exactly 10 questions
- Each question must have 4 options (option_a, option_b, option_c, option_d)
- correct_answer must be A, B, C, or D
- Return ONLY the JSON array, no additional text
- Make questions appropriate for ${defaultValues.value.grade_id ? grades.value.find(g => g.id === defaultValues.value.grade_id)?.name : 'students'}
${defaultValues.value.subject_id ? '- Subject: ' + subjects.value.find(s => s.id === defaultValues.value.subject_id)?.name : ''}
${defaultValues.value.difficulty ? '- Difficulty: ' + defaultValues.value.difficulty : ''}`;
  } else {
    prompt = `Generate 10 ${questionType.name} questions in JSON format. Use this exact structure:

[
  {
    "question_text": "What is the chemical symbol for water?"
  },
  {
    "question_text": "Who wrote Romeo and Juliet?"
  }
]

Requirements:
- Generate exactly 10 questions
- Return ONLY the JSON array, no additional text
- Make questions appropriate for ${defaultValues.value.grade_id ? grades.value.find(g => g.id === defaultValues.value.grade_id)?.name : 'students'}
${defaultValues.value.subject_id ? '- Subject: ' + subjects.value.find(s => s.id === defaultValues.value.subject_id)?.name : ''}
${defaultValues.value.difficulty ? '- Difficulty: ' + defaultValues.value.difficulty : ''}`;
  }
  
  // Copy to clipboard
  navigator.clipboard.writeText(prompt).then(() => {
    $q.notify({
      type: 'positive',
      message: 'AI Prompt copied!',
      caption: 'Paste it into ChatGPT or Claude',
      icon: 'content_copy'
    });
  }).catch(() => {
    $q.notify({
      type: 'negative',
      message: 'Failed to copy',
      caption: 'Please copy manually'
    });
  });
};

const downloadTemplate = () => {
  const questionType = selectedQuestionType.value;
  
  let headers, exampleRow;
  
  if (questionType?.has_options) {
    // Template for questions with options
    headers = [
      'question_text',
      'option_a',
      'option_b',
      'option_c',
      'option_d',
      'correct_answer'
    ];
    
    exampleRow = [
      'What is 2+2?',
      '2',
      '3',
      '4',
      '5',
      'C'
    ];
  } else {
    // Template for questions without options
    headers = ['question_text'];
    exampleRow = ['What is the chemical symbol for water?'];
  }

  const csv = [
    headers.join(','),
    exampleRow.map(cell => `"${cell}"`).join(',')
  ].join('\n');

  const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
  const url = window.URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = `question_import_template_${questionType?.slug || 'simple'}.csv`;
  a.click();
  window.URL.revokeObjectURL(url);

  $q.notify({
    type: 'positive',
    message: 'Template downloaded',
    caption: 'Check your downloads folder'
  });
};

const copyExcelExample = () => {
  navigator.clipboard.writeText(excelExampleData.value).then(() => {
    $q.notify({
      type: 'positive',
      message: 'Example data copied!',
      caption: 'Paste in Excel to see the format',
      icon: 'content_copy'
    });
  }).catch(() => {
    $q.notify({
      type: 'negative',
      message: 'Failed to copy',
      caption: 'Please copy manually'
    });
  });
};

const copyJsonExample = () => {
  navigator.clipboard.writeText(jsonExampleText.value).then(() => {
    $q.notify({
      type: 'positive',
      message: 'JSON example copied!',
      caption: 'Paste and modify with your questions',
      icon: 'content_copy'
    });
  }).catch(() => {
    $q.notify({
      type: 'negative',
      message: 'Failed to copy',
      caption: 'Please copy manually'
    });
  });
};

// Load metadata
const loadMetadata = async () => {
  try {
    const [typesRes, gradesRes, subjectsRes, topicsRes] = await Promise.all([
      axios.get('/api/question-types'),
      axios.get('/api/grades'),
      axios.get('/api/subjects'),
      axios.get('/api/subjects'),
      // axios.get('/api/topics')
    ]);

    questionTypes.value = typesRes.data.data || typesRes.data;
    console.log('questionTypes.value',questionTypes.value)
    grades.value = gradesRes.data.data || gradesRes.data;
    subjects.value = subjectsRes.data.data || subjectsRes.data;
    topics.value = topicsRes.data.data || topicsRes.data;
  } catch (error) {
    console.error('Failed to load metadata:', error);
  }
};

onMounted(() => {
  loadMetadata();
});
</script>

<style scoped lang="scss">
.question-import-page {
  background: #f7fafc;
  min-height: 100vh;
}

code {
  background: #f1f5f9;
  padding: 2px 6px;
  border-radius: 4px;
  font-family: 'Courier New', monospace;
  font-size: 0.9em;
  color: #1e40af;
}
</style>
