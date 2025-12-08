<template>
  <q-form @submit="onSubmit" class="question-form">
    <!-- Question Type -->
    <div class="form-section">
      <div class="text-subtitle1 q-mb-md">Question Type</div>
      <q-select
        v-model="formData.question_type_id"
        :options="questionTypes"
        option-value="id"
        option-label="name"
        emit-value
        map-options
        outlined
        dense
        label="Select question type *"
        :rules="[val => !!val || 'Question type is required']"
        @update:model-value="onQuestionTypeChange"
      >
        <template v-slot:prepend>
          <q-icon name="quiz" />
        </template>
      </q-select>
    </div>

    <!-- Question Text -->
    <div class="form-section">
      <div class="text-subtitle1 q-mb-md">Question Text</div>
      <q-input
        v-model="formData.question_text"
        type="textarea"
        outlined
        label="Enter your question *"
        rows="4"
        :rules="[
          val => !!val || 'Question text is required',
          val => val.length >= 10 || 'Question must be at least 10 characters'
        ]"
      />
    </div>

    <!-- Curriculum Alignment -->
    <div class="form-section">
      <div class="text-subtitle1 q-mb-md">Curriculum Alignment</div>
      <div class="row q-col-gutter-md">
        <div class="col-12 col-md-4">
          <q-select
            v-model="formData.grade_id"
            :options="grades"
            option-value="id"
            option-label="name"
            emit-value
            map-options
            outlined
            dense
            label="Grade"
            clearable
            @update:model-value="onGradeChange"
          >
            <template v-slot:prepend>
              <q-icon name="school" />
            </template>
          </q-select>
        </div>
        
        <div class="col-12 col-md-4">
          <q-select
            v-model="formData.subject_id"
            :options="filteredSubjects"
            option-value="id"
            option-label="name"
            emit-value
            map-options
            outlined
            dense
            label="Subject"
            clearable
            :disable="!formData.grade_id"
            @update:model-value="onSubjectChange"
          >
            <template v-slot:prepend>
              <q-icon name="subject" />
            </template>
          </q-select>
        </div>
        
        <div class="col-12 col-md-4">
          <q-select
            v-model="formData.topic_id"
            :options="filteredTopics"
            option-value="id"
            option-label="name"
            emit-value
            map-options
            outlined
            dense
            label="Topic"
            clearable
            :disable="!formData.subject_id"
          >
            <template v-slot:prepend>
              <q-icon name="label" />
            </template>
          </q-select>
        </div>
      </div>
    </div>

    <!-- Cognitive Settings -->
    <div class="form-section">
      <div class="text-subtitle1 q-mb-md">Cognitive Settings</div>
      <div class="row q-col-gutter-md">
        <div class="col-12 col-md-4">
          <q-select
            v-model="formData.difficulty_level"
            :options="difficultyLevels"
            emit-value
            map-options
            outlined
            dense
            label="Difficulty"
          >
            <template v-slot:prepend>
              <q-icon name="speed" />
            </template>
          </q-select>
        </div>
        
        <div class="col-12 col-md-4">
          <q-select
            v-model="formData.bloom_level"
            :options="bloomLevels"
            emit-value
            map-options
            outlined
            dense
            label="Bloom Level"
          >
            <template v-slot:prepend>
              <q-icon name="psychology" />
            </template>
          </q-select>
        </div>
        
        <div class="col-12 col-md-4">
          <q-input
            v-model.number="formData.estimated_time_sec"
            type="number"
            outlined
            dense
            label="Est. Time (seconds)"
            min="0"
          >
            <template v-slot:prepend>
              <q-icon name="timer" />
            </template>
          </q-input>
        </div>
      </div>
    </div>

    <!-- Answer Options (for option-based questions) -->
    <div v-if="selectedQuestionType?.has_options" class="form-section">
      <option-editor
        v-model="formData.options"
        :allow-multiple="isMultiSelect"
        :min-options="2"
        :max-options="6"
      />
    </div>

    <!-- Hints -->
    <div class="form-section">
      <div class="text-subtitle1 q-mb-md">Hints (Optional)</div>
      <div v-for="(hint, index) in formData.hints" :key="index" class="row q-col-gutter-sm q-mb-sm">
        <div class="col">
          <q-input
            v-model="formData.hints[index]"
            outlined
            dense
            :label="`Hint ${index + 1}`"
          />
        </div>
        <div>
          <q-btn
            flat
            round
            dense
            icon="close"
            size="sm"
            color="negative"
            @click="removeHint(index)"
          />
        </div>
      </div>
      <q-btn
        flat
        icon="add"
        label="Add Hint"
        color="primary"
        size="sm"
        @click="addHint"
      />
    </div>

    <!-- Explanation -->
    <div class="form-section">
      <div class="text-subtitle1 q-mb-md">Explanation (Optional)</div>
      <q-input
        v-model="formData.explanation.text"
        type="textarea"
        outlined
        label="Explain the correct answer"
        rows="3"
      />
      <q-checkbox
        v-model="formData.explanation.revealed_after_attempt"
        label="Reveal after first attempt"
        class="q-mt-sm"
      />
    </div>

    <!-- Status -->
    <div class="form-section">
      <div class="text-subtitle1 q-mb-md">Status</div>
      <q-select
        v-model="formData.status"
        :options="statusOptions"
        emit-value
        map-options
        outlined
        dense
        label="Question status"
      >
        <template v-slot:prepend>
          <q-icon name="flag" />
        </template>
      </q-select>
    </div>

    <!-- Form Actions -->
    <div class="row q-gutter-sm justify-end q-mt-lg">
      <q-btn
        flat
        label="Cancel"
        color="grey-7"
        @click="$emit('cancel')"
      />
      <q-btn
        flat
        label="Save as Draft"
        color="grey-8"
        @click="saveAsDraft"
        :loading="saving"
      />
      <q-btn
        type="submit"
        label="Save"
        color="primary"
        :loading="saving"
      />
    </div>
  </q-form>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import axios from 'axios';
import OptionEditor from './OptionEditor.vue';

const props = defineProps({
  modelValue: {
    type: Object,
    default: () => ({})
  },
  mode: {
    type: String,
    default: 'create' // 'create' or 'edit'
  }
});

const emit = defineEmits(['submit', 'cancel', 'update:modelValue']);

// Form data
const formData = ref({
  question_type_id: null,
  question_text: '',
  grade_id: null,
  subject_id: null,
  topic_id: null,
  difficulty_level: 3,
  bloom_level: null,
  estimated_time_sec: 60,
  status: 'draft',
  options: [],
  hints: [],
  explanation: {
    text: '',
    revealed_after_attempt: true
  },
  ...props.modelValue
});

// Metadata
const questionTypes = ref([]);
const grades = ref([]);
const subjects = ref([]);
const topics = ref([]);
const saving = ref(false);

// Options
const difficultyLevels = [
  { label: 'Very Easy', value: 1 },
  { label: 'Easy', value: 2 },
  { label: 'Medium', value: 3 },
  { label: 'Hard', value: 4 },
  { label: 'Very Hard', value: 5 }
];

const bloomLevels = [
  { label: 'Level 1 - Remember', value: 1 },
  { label: 'Level 2 - Understand', value: 2 },
  { label: 'Level 3 - Apply', value: 3 },
  { label: 'Level 4 - Analyze', value: 4 },
  { label: 'Level 5 - Evaluate', value: 5 },
  { label: 'Level 6 - Create', value: 6 }
];

const statusOptions = [
  { label: 'Draft', value: 'draft' },
  { label: 'Active', value: 'active' },
  { label: 'Archived', value: 'archived' },
  { label: 'Review', value: 'review' }
];

// Computed
const selectedQuestionType = computed(() => {
  return questionTypes.value.find(qt => qt.id === formData.value.question_type_id);
});

const isMultiSelect = computed(() => {
  return selectedQuestionType.value?.slug === 'multi_select';
});

const filteredSubjects = computed(() => {
  // if (!formData.value.grade_id) return subjects.value;
  return subjects.value 
  // return subjects.value.filter(s => s.grade_id === formData.value.grade_id);
});

const filteredTopics = computed(() => {
  if (!formData.value.subject_id) return topics.value;
  return topics.value.filter(t => t.subject_id === formData.value.subject_id);
});

// Methods
const onQuestionTypeChange = () => {
  // Reset options when question type changes
  if (selectedQuestionType.value?.has_options) {
    const questionTypeSlug = selectedQuestionType.value?.slug;
    
    // Special handling for True/False questions
    if (questionTypeSlug === 'true_false') {
      formData.value.options = [
        { id: Date.now(), option_key: 'A', option_text: 'True', is_correct: false, order_index: 0 },
        { id: Date.now() + 1, option_key: 'B', option_text: 'False', is_correct: false, order_index: 1 }
      ];
    } else if (formData.value.options.length === 0) {
      // Default options for other question types
      formData.value.options = [
        { id: Date.now(), option_key: 'A', option_text: '', is_correct: false, order_index: 0 },
        { id: Date.now() + 1, option_key: 'B', option_text: '', is_correct: false, order_index: 1 }
      ];
    }
  } else {
    formData.value.options = [];
  }
};

const onGradeChange = () => {
  formData.value.subject_id = null;
  formData.value.topic_id = null;
};

const onSubjectChange = () => {
  formData.value.topic_id = null;
};

const addHint = () => {
  formData.value.hints.push('');
};

const removeHint = (index) => {
  formData.value.hints.splice(index, 1);
};

const onSubmit = () => {
  emit('submit', formData.value);
};

const saveAsDraft = () => {
  formData.value.status = 'draft';
  emit('submit', formData.value);
};

// Load metadata
const loadQuestionTypes = async () => {
  try {
    const response = await axios.get('/api/question-types');
    questionTypes.value = response.data.data || response.data;
  } catch (error) {
    console.error('Failed to load question types:', error);
  }
};

const loadGrades = async () => {
  try {
    const response = await axios.get('/api/grades');
    grades.value = response.data.data || response.data;
  } catch (error) {
    console.error('Failed to load grades:', error);
  }
};

const loadSubjects = async () => {
  try {
    const response = await axios.get('/api/subjects');
    subjects.value = response.data.data || response.data;
    console.log('ddddddd',response.data.data)
    console.log('subjects',subjects.value)
  } catch (error) {
    console.error('Failed to load subjects:', error);
  }
};

const loadTopics = async () => {
  try {
    const response = await axios.get('/api/topics');
    topics.value = response.data.data || response.data;
  } catch (error) {
    console.error('Failed to load topics:', error);
  }
};

// Watch for external changes
watch(() => props.modelValue, (newValue) => {
  formData.value = { ...formData.value, ...newValue };
}, { deep: true });

// Load metadata on mount
onMounted(() => {
  loadQuestionTypes();
  loadGrades();
  loadSubjects();
  loadTopics();
});
</script>

<style scoped lang="scss">
.question-form {
  .form-section {
    margin-bottom: 32px;
    padding-bottom: 24px;
    border-bottom: 1px solid #e2e8f0;
    
    &:last-child {
      border-bottom: none;
    }
  }
}
</style>
