 <template>
  <Head :title="exam ? 'Edit Exam' : 'Create Exam'" />
  <div class="q-pa-md">
    <div class="row items-center q-mb-md">
      <div class="text-h5">{{ exam ? 'Edit Exam' : 'Create New Exam' }}</div>
    </div>

    <q-card flat bordered class="bg-white">
      <q-tabs
        v-model="activeTab"
        class="text-grey"
        active-color="primary"
        indicator-color="primary"
        align="left"
        narrow-indicator
      >
        <q-tab name="basic" label="Basic Info" icon="info" />
        <q-tab name="timing" label="Timing" icon="schedule" />
        <q-tab name="settings" label="Settings" icon="settings" />
        <q-tab name="questions" label="Questions" icon="quiz" />
        <q-tab name="assign" label="Assign" icon="group_add" />
      </q-tabs>

      <q-separator />

      <q-form @submit.prevent="submitForm">
        <q-tab-panels v-model="activeTab" keep-alive>
          
          <q-tab-panel name="basic" class="q-pa-md">
            <div class="q-gutter-md" style="max-width: 800px">
              <div class="text-h6">Basic Information</div>
              <q-separator />

              <q-input
                v-model="form.title"
                label="Exam Title *"
                hint="Enter a clear, descriptive title (10-200 characters)"
                counter
                maxlength="200"
                :rules="[
                  val => !!val || 'Title is required',
                  val => val.length >= 10 || 'Title must be at least 10 characters'
                ]"
              />

              <q-input
                v-model="form.description"
                label="Description"
                type="textarea"
                hint="Optional description or instructions for students"
                rows="3"
              />

              <q-select
                v-if="!selectedSubjectId"
                v-model="form.subject_id"
                :options="subjects"
                option-value="id"
                option-label="name"
                label="Subject *"
                emit-value
                map-options
                :rules="[val => !!val || 'Subject is required']"
              />

              <q-select
                v-model="form.exam_type"
                :options="examTypeOptions"
                label="Exam Type *"
                emit-value
                map-options
                :rules="[val => !!val || 'Exam type is required']"
                @update:model-value="onExamTypeChange"
              >
                <template v-slot:hint>
                  <div v-if="form.exam_type === 'survey'">
                    Survey: For gathering information or opinions (no grading)
                  </div>
                </template>
              </q-select>

              <q-input
                v-model="form.custom_group"
                label="Custom Group (Optional)"
                hint="Organize exams into custom categories (e.g., 'Unit 1 Tests', 'Final Exams 2026')"
              >
                <template v-slot:append>
                  <q-icon name="category" />
                </template>
                <q-menu>
                  <q-list style="min-width: 200px">
                    <q-item
                      v-for="group in customGroups"
                      :key="group"
                      clickable
                      v-close-popup
                      @click="form.custom_group = group"
                    >
                      <q-item-section>{{ group }}</q-item-section>
                    </q-item>
                  </q-list>
                </q-menu>
              </q-input>
            </div>
          </q-tab-panel>

          <q-tab-panel name="timing" class="q-pa-md">
            <div class="q-gutter-md" style="max-width: 800px">
              <div class="text-h6">Exam Timing & Duration</div>
              <q-separator />

              <q-input
                v-model.number="form.duration_minutes"
                type="number"
                label="Duration (minutes) *"
                hint="How long students have to complete the exam"
                style="min-width: 200px"
                :rules="[
                  val => !!val || 'Duration is required',
                  val => val > 0 || 'Duration must be greater than 0'
                ]"
              />

              <div class="text-subtitle2 q-mt-lg">Scheduling</div>
              <q-toggle
                v-model="isScheduled"
                label="Schedule exam (set start/end dates)"
                @update:model-value="onScheduleToggle"
                class="q-mb-md"
              />

              <div v-if="isScheduled" class="row q-gutter-md q-mb-md">
                <q-input
                  v-model="form.start_date"
                  label="Start Date & Time"
                  type="datetime-local"
                  hint="When exam becomes available"
                  class="col-12 col-md-5"
                />

                <q-input
                  v-model="form.end_date"
                  label="End Date & Time"
                  type="datetime-local"
                  hint="Submission deadline"
                  class="col-12 col-md-5"
                  :rules="[
                    val => !form.start_date || !val || new Date(val) > new Date(form.start_date) || 'End date must be after start date'
                  ]"
                />
              </div>

              <q-select
                v-model="form.publish_results_timing"
                :options="publishResultsOptions"
                label="Publish Results"
                emit-value
                map-options
                class="col-12 col-md-6"
              />
            </div>
          </q-tab-panel>

          <q-tab-panel name="settings" class="q-pa-md">
            <div class="q-gutter-md" style="max-width: 800px">
              <div class="text-h6">Exam Settings</div>
              <q-separator />

              <div class="row q-gutter-md">
                <q-input
                  v-if="form.exam_type !== 'survey'"
                  v-model.number="form.passing_score"
                  type="number"
                  label="Passing Score (%)"
                  hint="Minimum score to pass (Optional, default 50%)"
                  placeholder="50"
                  style="min-width: 200px"
                  :rules="[
                    val => val === null || val === '' || (val >= 0 && val <= 100) || 'Must be between 0 and 100'
                  ]"
                />
              </div>

              <q-card flat bordered class="q-mt-md bg-grey-1">
                <q-card-section>
                  <div class="text-subtitle2 q-mb-sm">Attempts & Grading</div>
                  <div class="row q-gutter-md">
                    <q-select
                      v-model="maxAttemptsOption"
                      :options="maxAttemptsOptions"
                      label="Maximum Attempts"
                      emit-value
                      map-options
                      class="col-12 col-md-5"
                      @update:model-value="onMaxAttemptsChange"
                    />

                    <q-input
                      v-if="maxAttemptsOption === 'custom'"
                      v-model.number="form.max_attempts"
                      type="number"
                      label="Custom Attempts Count"
                      class="col-12 col-md-5"
                      :rules="[val => val > 0 || 'Must be greater than 0']"
                    />
                  </div>

                  <q-option-group
                    v-model="form.mark_calculation_method"
                    :options="markCalculationOptions"
                    label="Final Mark Calculation"
                    class="q-mt-md"
                  />
                </q-card-section>
              </q-card>

              <div class="text-subtitle2 q-mt-lg">Advanced</div>
              <div class="row q-gutter-md q-mt-xs">
                <q-toggle
                  v-model="form.settings.shuffle_questions"
                  label="Shuffle Questions"
                  color="primary"
                />
                <q-toggle
                  v-model="form.settings.shuffle_options"
                  label="Shuffle Options"
                  color="primary"
                />
                <q-toggle
                  v-model="form.settings.allow_print"
                  label="Allow Print (for practice)"
                  color="secondary"
                >
                  <q-tooltip>
                    Enable students to print this exam for offline practice
                  </q-tooltip>
                </q-toggle>
              </div>
            </div>
          </q-tab-panel>

          <q-tab-panel name="questions" class="q-pa-md">
            <div class="row q-col-gutter-md">
              <div class="col-12 col-md-8">
                <div class="row items-center justify-between q-mb-md">
                  <div class="text-h6">Questions ({{ selectedQuestions.length }})</div>
                  <div class="q-gutter-sm">
                    <q-btn
                      color="secondary"
                      icon="auto_awesome"
                      label="AI Generate Questions"
                      @click="showAIGenerator = true"
                      outline
                    />
                    <q-btn
                      color="primary"
                      icon="add"
                      label="Add Questions"
                      @click="questionSelectorDialog = true"
                    />
                  </div>
                </div>

                <div v-if="selectedQuestions.length === 0" class="text-center q-pa-xl bg-grey-1 rounded-borders">
                  <q-icon name="quiz" size="64px" color="grey-4" />
                  <p class="text-h6 text-grey-6 q-mt-md">No questions selected</p>
                  <p class="text-grey-7">Click "Add Questions" to select from the bank</p>
                </div>

                <draggable
                  v-else
                  v-model="selectedQuestions"
                  item-key="id"
                  handle=".drag-handle"
                  @end="updateTotalPoints"
                  class="q-gutter-y-md"
                >
                  <template #item="{ element: question, index }">
                    <q-card class="rounded-lg shadow-1" bordered>
                      <q-card-section>
                        <div class="row items-start q-gutter-x-md">
                          <div class="column justify-center self-stretch cursor-move drag-handle q-pr-sm">
                            <q-icon name="drag_indicator" size="sm" color="grey-5" />
                          </div>

                          <q-badge color="grey-3" text-color="grey-9" class="text-subtitle2 q-pa-xs">
                            Q{{ index + 1 }}
                          </q-badge>

                          <div class="col">
                            <div class="text-body1 text-weight-medium" v-html="question.question_text"></div>
                            <div class="row q-gutter-x-sm q-mt-sm">
                              <q-chip size="sm" :color="getDifficultyColor(question.difficulty)" text-color="white">
                                {{ question.difficulty || 'Medium' }}
                              </q-chip>
                            </div>
                          </div>

                          <div class="column items-end q-gutter-y-sm" style="min-width: 100px">
                            <q-input
                              v-model.number="question.marks"
                              type="number"
                              dense
                              outlined
                              label="Points"
                              style="width: 80px"
                              @update:model-value="updateTotalPoints"
                            />
                            <q-btn
                              flat
                              round
                              dense
                              color="negative"
                              icon="delete"
                              @click="removeQuestion(index)"
                            />
                          </div>
                        </div>
                      </q-card-section>
                    </q-card>
                  </template>
                </draggable>
              </div>

              <div class="col-12 col-md-4">
                <q-card bordered flat class="bg-grey-1">
                  <q-card-section>
                    <div class="text-subtitle1 text-weight-bold q-mb-md">Exam Summary</div>
                    <q-list separator>
                      <q-item>
                        <q-item-section>Total Questions</q-item-section>
                        <q-item-section side>{{ selectedQuestions.length }}</q-item-section>
                      </q-item>
                      <q-item>
                        <q-item-section>Total Marks</q-item-section>
                        <q-item-section side class="text-weight-bold text-primary">{{ computedTotalMarks }}</q-item-section>
                      </q-item>
                    </q-list>
                  </q-card-section>
                </q-card>
              </div>
            </div>
          </q-tab-panel>

          <q-tab-panel name="assign" class="q-pa-md">
             <div class="q-gutter-md" style="max-width: 800px">
                <div class="text-h6">Target Audience</div>
                <div class="text-subtitle2 text-grey-7">Control who can see and take this exam</div>
                <q-separator />

                <div class="q-gutter-md q-mt-sm">
                  <q-btn-toggle
                    v-model="audienceType"
                    :options="[
                      { label: 'Public (Everyone)', value: 'public' },
                      { label: 'Specific Classrooms', value: 'specific' }
                    ]"
                    toggle-color="secondary"
                    unelevated
                  />

                  <div v-if="audienceType === 'specific'" class="q-pa-md bg-grey-1 rounded-borders">
                    <div class="text-subtitle2 q-mb-sm">Select Classrooms</div>
                    
                    <q-select
                      v-model="form.target_audience.classroom_ids"
                      :options="classrooms"
                      option-value="id"
                      option-label="name"
                      label="Classrooms"
                      multiple
                      emit-value
                      map-options
                      hint="Select which classrooms can access this exam"
                    >
                      <template v-slot:option="{ itemProps, opt, selected, toggleOption }">
                        <q-item v-bind="itemProps">
                          <q-item-section>
                            <q-item-label v-html="opt.name" />
                            <q-item-label caption v-if="opt.grade">Grade: {{ opt.grade.name }}</q-item-label>
                          </q-item-section>
                          <q-item-section side>
                            <q-toggle :model-value="selected" @update:model-value="toggleOption(opt)" />
                          </q-item-section>
                        </q-item>
                      </template>
                    </q-select>

                    <q-separator class="q-my-md" />

                    <div class="text-subtitle2 q-mb-sm">Specific Students (Optional)</div>
                    <q-select
                      v-model="form.target_audience.user_ids"
                      label="Assign to Specific Students"
                      multiple
                      use-input
                      fill-input
                      hide-selected
                      input-debounce="300"
                      :options="userOptions"
                      option-value="id"
                      option-label="name"
                      emit-value
                      map-options
                      @filter="filterUsers"
                      hint="Search by name or email to add individual students"
                    >
                      <template v-slot:no-option>
                        <q-item>
                          <q-item-section class="text-grey">
                            Type at least 2 characters to search
                          </q-item-section>
                        </q-item>
                      </template>
                      <template v-slot:option="{ itemProps, opt, selected, toggleOption }">
                        <q-item v-bind="itemProps">
                          <q-item-section>
                            <q-item-label>{{ opt.name }}</q-item-label>
                            <q-item-label caption>{{ opt.email }} ({{ opt.role }})</q-item-label>
                          </q-item-section>
                          <q-item-section side>
                            <q-toggle :model-value="selected" @update:model-value="toggleOption(opt)" />
                          </q-item-section>
                        </q-item>
                      </template>
                    </q-select>

                    <!-- Selected Students Badge List -->
                    <div v-if="form.target_audience.user_ids.length > 0" class="q-mt-sm row q-gutter-xs">
                      <q-badge
                        v-for="id in form.target_audience.user_ids"
                        :key="id"
                        color="primary"
                        removable
                        @click="form.target_audience.user_ids = form.target_audience.user_ids.filter(uid => uid !== id)"
                      >
                        User ID: {{ id }}
                      </q-badge>
                    </div>
                  </div>
                </div>
             </div>
          </q-tab-panel>
        </q-tab-panels> <div class="q-pa-md bg-grey-1 rounded-borders">
          <div class="row justify-end q-gutter-sm">
            <q-btn
              type="submit"
              color="primary"
              label="Save as Draft"
              :loading="form.processing"
              @click="form.is_published = false"
            />
            <q-btn
              type="submit"
              color="positive"
              label="Publish"
              :loading="form.processing"
              @click="form.is_published = true"
              :disable="!canPublish"
            >
              <q-tooltip v-if="!canPublish">
                {{ publishValidationMessage }}
              </q-tooltip>
            </q-btn>
            <q-btn
              flat
              label="Cancel"
              color="negative"
              @click="onCancel"
            />
          </div>
        </div>
      </q-form>
    </q-card>

    <QuExamQuestionSelector
      v-model="questionSelectorDialog"
      :subject-id="form.subject_id"
      :selected-questions="selectedQuestions"
      @update:selected-questions="onQuestionsSelected"
    />

    <QuQuestionAIGeneratorDialog
      v-model="showAIGenerator"
      :subject-id="form.subject_id"
      :subjects="subjects"
      :exam-title="form.title"
      @success="handleAIGeneratorSuccess"
    />
  </div>
</template>
<script setup>
import { ref, reactive, computed, watch } from 'vue';
import { useForm, Head } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { useQuasar } from 'quasar';
import axios from 'axios';
import draggable from 'vuedraggable';
import QuExamQuestionSelector from './QuComponents/QuExamQuestionSelector.vue';
import QuQuestionAIGeneratorDialog from './QuComponents/QuQuestionAIGeneratorDialog.vue';

const $q = useQuasar();

const props = defineProps({
  subjects: Array,
  examTypes: Array,
  markCalculationMethods: Array,
  publishResultsTimings: Array,
  customGroups: Array,
  grades: Array,
  classrooms: Array,
  selectedSubjectId: [Number, String],
  exam: Object
});

const emit = defineEmits(['success', 'cancel']);

// Tab state
const activeTab = ref('basic');
const questionSelectorDialog = ref(false);
const showAIGenerator = ref(false);

const handleAIGeneratorSuccess = (questions) => {
  // Use existing method to add questions to the list
  onQuestionsSelected(questions);
  
  $q.notify({
    type: 'positive',
    message: `Added ${questions.length} AI generated questions to the exam!`,
    position: 'top'
  });
};

// Form initialization
const form = useForm({
  title: props.exam?.title || '',
  description: props.exam?.description || '',
  subject_id: props.exam?.subject_id || props.selectedSubjectId || null,
  exam_type: props.exam?.exam_type || 'quiz',
  custom_group: props.exam?.custom_group || '',
  duration_minutes: props.exam?.duration_minutes || 60,
  passing_score: props.exam ? props.exam.passing_score : 50,
  max_attempts: props.exam ? props.exam.max_attempts : 3,
  mark_calculation_method: props.exam?.mark_calculation_method || 'last',
  start_date: props.exam?.start_date || null,
  end_date: props.exam?.end_date || null,
  publish_results_timing: props.exam?.publish_results_timing || 'immediate',
  bloom_distribution: props.exam?.bloom_distribution || {
    remember: 0,
    understand: 0,
    apply: 0,
    analyze: 0,
    evaluate: 0,
    create: 0
  },
  question_ids: [],
  questions: [],
  is_published: props.exam?.is_published || false,
  total_marks: props.exam?.total_marks || 0,
  settings: props.exam?.settings || {
    shuffle_questions: false,
    shuffle_options: false,
    allow_print: false
  },
  target_audience: props.exam?.target_audience || {
    roles: ['student'],
    grade_ids: [],
    classroom_ids: [],
    user_ids: []
  }
});

// Question management
const selectedQuestions = ref(props.exam?.questions?.map(q => ({
  ...q,
  marks: q.pivot?.points || q.marks || 1
})) || []);

// Sync selected questions with form
watch(selectedQuestions, (newVal) => {
  form.question_ids = newVal.map(q => q.id);
  form.questions = newVal.map(q => ({ 
    id: q.id, 
    points: q.marks 
  }));
  form.total_marks = newVal.reduce((sum, q) => sum + (Number(q.marks) || 0), 0);
}, { deep: true });

const computedTotalMarks = computed(() => {
  return selectedQuestions.value.reduce((sum, q) => sum + (Number(q.marks) || 0), 0);
});

const onQuestionsSelected = (questions) => {
  questions.forEach(q => {
    if (!selectedQuestions.value.find(existing => existing.id === q.id)) {
      selectedQuestions.value.push({
        ...q,
        marks: q.marks || 1
      });
    }
  });
};

const removeQuestion = (index) => {
  selectedQuestions.value.splice(index, 1);
};

const updateTotalPoints = () => {
  selectedQuestions.value = [...selectedQuestions.value];
};

const getDifficultyColor = (difficulty) => {
  const colors = {
    easy: 'green',
    medium: 'orange',
    hard: 'red'
  };
  return colors[difficulty?.toLowerCase()] || 'grey';
};

// Audience Selection Logic
const audienceType = ref(
  (props.exam?.target_audience && (
    props.exam.target_audience.classroom_ids?.length > 0 ||
    props.exam.target_audience.user_ids?.length > 0
  )) ? 'specific' : 'public'
);

// User Search
const userOptions = ref([]);
const isSearchingUsers = ref(false);
const filterUsers = (val, update, abort) => {
  if (val.length < 2) {
    abort();
    return;
  }
  isSearchingUsers.value = true;
  axios.get(route('qu-exams.users.search', { search: val }))
    .then(response => {
      update(() => {
        userOptions.value = response.data.users;
      });
    })
    .finally(() => {
      isSearchingUsers.value = false;
    });
};

watch(audienceType, (newVal) => {
  if (newVal === 'public') {
    form.target_audience = { roles: ['student'], grade_ids: [], classroom_ids: [], user_ids: [] };
  } else {
    form.target_audience.roles = ['student'];
  }
});

const isScheduled = ref(!!(props.exam?.start_date || props.exam?.end_date));
const maxAttemptsOption = ref(
  props.exam ? (
    props.exam.max_attempts === null ? 'unlimited' :
    [1, 2, 3].includes(props.exam.max_attempts) ? props.exam.max_attempts :
    'custom'
  ) : 3
);

const examTypeOptions = props.examTypes.map(type => ({
  value: type,
  label: capitalizeFirst(type) + (type === 'survey' ? ' (No grading)' : '')
}));

const maxAttemptsOptions = [
  { value: 'unlimited', label: 'Unlimited' },
  { value: 1, label: '1 Attempt' },
  { value: 2, label: '2 Attempts' },
  { value: 3, label: '3 Attempts' },
  { value: 'custom', label: 'Custom' }
];

const markCalculationOptions = props.markCalculationMethods.map(method => ({
  value: method,
  label: method === 'last' ? 'Last Attempt - Use the most recent attempt score' :
         method === 'best' ? 'Best Attempt - Use the highest score achieved' :
         'Average - Calculate average of all attempts'
}));

const publishResultsOptions = props.publishResultsTimings.map(timing => ({
  value: timing,
  label: timing === 'immediate' ? 'Immediately after submission' :
         timing === 'after_end' ? 'After exam end date' :
         'Manual (teacher controls)'
}));

const canPublish = computed(() => {
  if (!form.title || form.title.length < 10) return false;
  if (!form.subject_id) return false;
  if (isScheduled.value && form.start_date && form.end_date) {
    if (new Date(form.end_date) <= new Date(form.start_date)) return false;
  }
  return true;
});

const publishValidationMessage = computed(() => {
  if (!form.title || form.title.length < 10) return 'Title must be at least 10 characters';
  if (!form.subject_id) return 'Subject is required';
  if (isScheduled.value && form.start_date && form.end_date) {
    if (new Date(form.end_date) <= new Date(form.start_date)) {
      return 'End date must be after start date';
    }
  }
  return '';
});

const onExamTypeChange = () => {
  if (form.exam_type === 'survey') {
    form.passing_score = null;
  }
};

const onMaxAttemptsChange = (value) => {
  if (value === 'unlimited') {
    form.max_attempts = null;
  } else if (value !== 'custom') {
    form.max_attempts = value;
  }
};

const onScheduleToggle = (value) => {
  if (!value) {
    form.start_date = null;
    form.end_date = null;
  }
};

const onCancel = () => {
  emit('cancel');
  if (!route().current('qu-exams.index')) {
    router.visit(route('qu-exams.index'));
  }
};

const submitForm = () => {
  if (!props.exam) {
    form.bloom_distribution = {
      remember: 0,
      understand: 0,
      apply: 0,
      analyze: 0,
      evaluate: 0,
      create: 0
    };
  }

  const url = props.exam 
    ? route('qu-exams.update', props.exam.id)
    : route('qu-exams.store');

  const method = props.exam ? 'put' : 'post';

  form[method](url, {
    onSuccess: () => {
      $q.notify({
        type: 'positive',
        message: `Exam ${props.exam ? 'updated' : 'created'} successfully`,
        position: 'top'
      });
      emit('success');
    },
    onError: (errors) => {
      $q.notify({
        type: 'negative',
        message: 'Failed to save exam. Please check the form.',
        caption: Object.values(errors)[0],
        position: 'top'
      });
    }
  });
};

function capitalizeFirst(str) {
  return str ? str.charAt(0).toUpperCase() + str.slice(1) : '';
}
</script>

<style scoped>
.dashed-border {
  border: 2px dashed #e0e0e0;
}

.cursor-move {
  cursor: move;
}
</style>
