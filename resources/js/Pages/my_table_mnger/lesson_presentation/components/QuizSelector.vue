<template>
  <div class="space-y-3">
    <!-- Filters Row -->
    <div class="flex items-center gap-3 flex-wrap">
      <!-- Grade Filter -->
      <div class="flex items-center gap-2">
        <label class="text-sm font-medium text-gray-700">Grade:</label>
        <select
          v-model="selectedGradeId"
          class="border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 text-sm px-3 py-2 min-w-[150px]"
        >
          <option :value="null">All Grades</option>
          <option
            v-for="grade in grades"
            :key="grade.id"
            :value="grade.id"
          >
            {{ grade.name }}
          </option>
        </select>
      </div>

      <!-- Subject Filter -->
      <div class="flex items-center gap-2">
        <label class="text-sm font-medium text-gray-700">Subject:</label>
        <select
          v-model="selectedSubjectId"
          class="border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 text-sm px-3 py-2 min-w-[150px]"
        >
          <option :value="null">All Subjects</option>
          <option
            v-for="subject in subjects"
            :key="subject.id"
            :value="subject.id"
          >
            {{ subject.name }}
          </option>
        </select>
      </div>
    </div>

    <!-- Quiz Selector Row -->
    <div class="flex items-center gap-2">
      <label class="text-sm font-medium text-gray-700">Quiz:</label>
      <select
        :value="modelValue"
        @change="$emit('update:modelValue', $event.target.value ? parseInt($event.target.value) : null)"
        class="border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 text-sm px-3 py-2 min-w-[200px]"
      >
        <option :value="null">No Quiz Assigned</option>
        <option
          v-for="quiz in quizzes"
          :key="quiz.id"
          :value="quiz.id"
        >
          {{ quiz.name }} ({{ quiz.questions_count }} questions)
        </option>
        <option value="create-new" class="font-semibold text-blue-600">
          + Create New Quiz
        </option>
      </select>
    </div>
  </div>

  <!-- Create Quiz Modal -->
  <q-dialog v-model="showCreateModal" persistent>
    <q-card style="min-width: 500px">
      <q-card-section class="row items-center bg-primary text-white">
        <div class="text-h6">Create New Quiz</div>
        <q-space />
        <q-btn icon="close" flat round dense v-close-popup />
      </q-card-section>

      <q-card-section class="q-pt-md">
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Quiz Name *</label>
            <input
              v-model="newQuiz.name"
              type="text"
              class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
              placeholder="Enter quiz name"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <textarea
              v-model="newQuiz.description"
              rows="3"
              class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
              placeholder="Enter quiz description (optional)"
            ></textarea>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Time Limit (minutes)</label>
            <input
              v-model.number="newQuiz.time_limit_minutes"
              type="number"
              min="1"
              class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
              placeholder="Optional"
            />
          </div>

          <div class="flex items-center gap-4">
            <label class="flex items-center gap-2">
              <input
                v-model="newQuiz.shuffle_questions"
                type="checkbox"
                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
              />
              <span class="text-sm text-gray-700">Shuffle Questions</span>
            </label>

            <label class="flex items-center gap-2">
              <input
                v-model="newQuiz.shuffle_options"
                type="checkbox"
                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
              />
              <span class="text-sm text-gray-700">Shuffle Options</span>
            </label>
          </div>
        </div>
      </q-card-section>

      <q-card-actions align="right" class="q-px-md q-pb-md">
        <q-btn flat label="Cancel" color="grey" v-close-popup />
        <q-btn
          unelevated
          label="Create Quiz"
          color="primary"
          @click="createQuiz"
          :loading="isCreating"
          :disable="!newQuiz.name"
        />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue';
import { useQuasar } from 'quasar';
import axios from 'axios';

const props = defineProps({
  modelValue: {
    type: Number,
    default: null
  },
  schoolId: {
    type: Number,
    required: true
  },
  gradeId: {
    type: Number,
    default: null
  },
  subjectId: {
    type: Number,
    default: null
  }
});

const emit = defineEmits(['update:modelValue']);

const $q = useQuasar();
const quizzes = ref([]);
const grades = ref([]);
const subjects = ref([]);
const selectedGradeId = ref(props.gradeId);
const selectedSubjectId = ref(props.subjectId);
const showCreateModal = ref(false);
const isCreating = ref(false);
const newQuiz = ref({
  name: '',
  description: '',
  time_limit_minutes: null,
  shuffle_questions: false,
  shuffle_options: false,
  allow_review: true
});

const fetchQuizzes = async () => {
  try {
    const params = {
      school_id: props.schoolId,
      status: 'active'
    };

    if (selectedGradeId.value) {
      params.grade_id = selectedGradeId.value;
    }

    if (selectedSubjectId.value) {
      params.subject_id = selectedSubjectId.value;
    }

    const response = await axios.get('/api/quizzes', { params });
    
    // Handle new response structure
    if (response.data.quizzes) {
      quizzes.value = response.data.quizzes;
      grades.value = response.data.filters?.grades || [];
      subjects.value = response.data.filters?.subjects || [];
    } else {
      // Fallback for old response format
      quizzes.value = response.data;
    }
  } catch (error) {
    console.error('Failed to fetch quizzes:', error);
    $q.notify({
      type: 'negative',
      message: 'Failed to load quizzes',
      icon: 'error',
      position: 'top'
    });
  }
};

const createQuiz = async () => {
  if (!newQuiz.value.name) {
    $q.notify({
      type: 'warning',
      message: 'Please enter a quiz name',
      icon: 'warning',
      position: 'top'
    });
    return;
  }

  isCreating.value = true;
  try {
    const response = await axios.post('/api/quizzes', {
      ...newQuiz.value,
      school_id: props.schoolId,
      grade_id: selectedGradeId.value || props.gradeId,
      subject_id: selectedSubjectId.value || props.subjectId,
      status: 'active'
    });

    $q.notify({
      type: 'positive',
      message: 'Quiz created successfully!',
      icon: 'check_circle',
      position: 'top'
    });

    // Refresh quiz list
    await fetchQuizzes();

    // Select the newly created quiz
    emit('update:modelValue', response.data.id);

    // Reset form and close modal
    newQuiz.value = {
      name: '',
      description: '',
      time_limit_minutes: null,
      shuffle_questions: false,
      shuffle_options: false,
      allow_review: true
    };
    showCreateModal.value = false;
  } catch (error) {
    console.error('Failed to create quiz:', error);
    $q.notify({
      type: 'negative',
      message: 'Failed to create quiz',
      icon: 'error',
      position: 'top'
    });
  } finally {
    isCreating.value = false;
  }
};

// Watch for "create-new" selection
watch(() => props.modelValue, (newValue) => {
  if (newValue === 'create-new') {
    showCreateModal.value = true;
    // Reset to null immediately
    emit('update:modelValue', null);
  }
});

// Watch for filter changes and refetch quizzes
watch([selectedGradeId, selectedSubjectId], () => {
  fetchQuizzes();
});

// Fetch quizzes on mount and when props change
watch([() => props.gradeId, () => props.subjectId], () => {
  selectedGradeId.value = props.gradeId;
  selectedSubjectId.value = props.subjectId;
  fetchQuizzes();
});

onMounted(() => {
  fetchQuizzes();
});
</script>
