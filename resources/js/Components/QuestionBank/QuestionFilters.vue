<template>
  <q-card flat bordered>
    <q-card-section>
      <div class="row items-center justify-between q-mb-md">
        <div class="text-h6">Filters</div>
        <q-btn
          v-if="hasActiveFilters"
          flat
          dense
          size="sm"
          label="Clear"
          color="primary"
          @click="clearFilters"
        />
      </div>

      <!-- Question Type Filter -->
      <div class="q-mb-md">
        <div class="text-subtitle2 q-mb-sm">Question Type</div>
        <q-select
          v-model="localFilters.question_type_id"
          :options="questionTypes"
          option-value="id"
          option-label="name"
          emit-value
          map-options
          outlined
          dense
          clearable
          placeholder="All types"
        />
      </div>

      <!-- Difficulty Filter -->
      <div class="q-mb-md">
        <div class="text-subtitle2 q-mb-sm">Difficulty</div>
        <q-select
          v-model="localFilters.difficulty"
          :options="difficultyOptions"
          outlined
          dense
          clearable
          placeholder="All difficulties"
        />
      </div>

      <!-- Grade Filter -->
      <div class="q-mb-md">
        <div class="text-subtitle2 q-mb-sm">Grade</div>
        <q-select
          v-model="localFilters.grade_id"
          :options="grades"
          option-value="id"
          option-label="name"
          emit-value
          map-options
          outlined
          dense
          clearable
          placeholder="All grades"
          @update:model-value="onGradeChange"
        />
      </div>

      <!-- Subject Filter -->
      <div class="q-mb-md">
        <div class="text-subtitle2 q-mb-sm">Subject</div>
        <q-select
          v-model="localFilters.subject_id"
          :options="filteredSubjects"
          option-value="id"
          option-label="name"
          emit-value
          map-options
          outlined
          dense
          clearable
          placeholder="All subjects"
          :disable="!localFilters.grade_id"
          @update:model-value="onSubjectChange"
        />
      </div>

      <!-- Topic Filter -->
      <div class="q-mb-md">
        <div class="text-subtitle2 q-mb-sm">Topic</div>
        <q-select
          v-model="localFilters.topic_id"
          :options="filteredTopics"
          option-value="id"
          option-label="name"
          emit-value
          map-options
          outlined
          dense
          clearable
          placeholder="All topics"
          :disable="!localFilters.subject_id"
        />
      </div>

      <!-- Status Filter -->
      <div class="q-mb-md">
        <div class="text-subtitle2 q-mb-sm">Status</div>
        <q-select
          v-model="localFilters.status"
          :options="statusOptions"
          outlined
          dense
          clearable
          placeholder="All statuses"
        />
      </div>
    </q-card-section>
  </q-card>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import axios from 'axios';

const props = defineProps({
  modelValue: {
    type: Object,
    default: () => ({})
  }
});

const emit = defineEmits(['update:modelValue']);

// State
const localFilters = ref({
  question_type_id: null,
  difficulty: null,
  grade_id: null,
  subject_id: null,
  topic_id: null,
  status: null
});

const questionTypes = ref([]);
const grades = ref([]);
const subjects = ref([]);
const topics = ref([]);

const difficultyOptions = ['Easy', 'Medium', 'Hard'];
const statusOptions = ['draft', 'active', 'archived', 'review'];

// Computed
const hasActiveFilters = computed(() => {
  return Object.values(localFilters.value).some(v => v !== null && v !== undefined && v !== '');
});

const filteredSubjects = computed(() => {
  if (!localFilters.value.grade_id) {
    return subjects.value;
  }
  return subjects.value.filter(s => s.grade_id === localFilters.value.grade_id);
});

const filteredTopics = computed(() => {
  if (!localFilters.value.subject_id) {
    return topics.value;
  }
  return topics.value.filter(t => t.subject_id === localFilters.value.subject_id);
});

// Methods
const loadQuestionTypes = async () => {
  try {
    const response = await axios.get('/api/question-types');
    if (response.data.success) {
      questionTypes.value = response.data.data;
    }
  } catch (error) {
    console.error('Failed to load question types:', error);
  }
};

const loadGrades = async () => {
  try {
    const response = await axios.get('/api/grades');
    if (response.data.success) {
      grades.value = response.data.data;
    }
  } catch (error) {
    console.error('Failed to load grades:', error);
  }
};

const loadSubjects = async () => {
  try {
    const response = await axios.get('/api/subjects');
    if (response.data.success) {
      subjects.value = response.data.data;
    }
  } catch (error) {
    console.error('Failed to load subjects:', error);
  }
};

const loadTopics = async () => {
  try {
    const response = await axios.get('/api/topics');
    if (response.data.success) {
      topics.value = response.data.data;
    }
  } catch (error) {
    console.error('Failed to load topics:', error);
  }
};

const onGradeChange = () => {
  // Clear subject and topic when grade changes
  localFilters.value.subject_id = null;
  localFilters.value.topic_id = null;
};

const onSubjectChange = () => {
  // Clear topic when subject changes
  localFilters.value.topic_id = null;
};

const clearFilters = () => {
  localFilters.value = {
    question_type_id: null,
    difficulty: null,
    grade_id: null,
    subject_id: null,
    topic_id: null,
    status: null
  };
};

// Watch local filters and emit changes
watch(localFilters, (newValue) => {
  emit('update:modelValue', { ...newValue });
}, { deep: true });

// Watch prop changes
watch(() => props.modelValue, (newValue) => {
  localFilters.value = { ...newValue };
}, { deep: true, immediate: true });

// Load data on mount
onMounted(() => {
  loadQuestionTypes();
  loadGrades();
  loadSubjects();
  loadTopics();
});
</script>

<style scoped lang="scss">
.text-subtitle2 {
  font-weight: 500;
  color: #424242;
}
</style>
