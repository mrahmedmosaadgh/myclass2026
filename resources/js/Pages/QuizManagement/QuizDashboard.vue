<template>
  <Head title="Quiz Dashboard" />
  <div class="quiz-dashboard q-pa-md bg-grey-1">
    <div class="quiz-dashboard__container">
      
      <!-- Navigation -->
      <QuizNavigation role="teacher" />

      <!-- Welcome Banner -->
      <q-card class="welcome-banner q-mb-lg bg-gradient-primary text-white rounded-xl shadow-2">
        <q-card-section class="row items-center q-pa-lg">
          <div class="col-12 col-md-8">
            <h1 class="text-h3 text-weight-bold q-mb-sm">Hello, Teacher! 👋</h1>
            <p class="text-h6 opacity-90">Ready to create some amazing quizzes for your students?</p>
          </div>
          <div class="col-12 col-md-4 text-right gt-sm">
            <q-icon name="school" size="100px" class="opacity-50" />
          </div>
        </q-card-section>
      </q-card>

      <!-- Header & Actions -->
      <div class="row items-center justify-between q-mb-lg">
        <div>
          <h2 class="text-h4 text-weight-bold text-primary q-my-none">Dashboard</h2>
          <p class="text-subtitle1 text-grey-7 q-mt-xs">Manage your quizzes and track progress</p>
        </div>
        
        <q-btn
          push
          glossy
          rounded
          color="secondary"
          icon="add"
          label="Create New Quiz"
          size="lg"
          class="q-px-xl q-py-xs"
          @click="showCreateDialog = true"
        />
      </div>

      <!-- Statistics Cards -->
      <div class="row q-col-gutter-md q-mb-xl">
        <div class="col-12 col-sm-6 col-md-3">
         
          <quiz-stats class="stats-card"
            icon="quiz"
            label="Total Quizzes"
            :value="statistics.totalQuizzes"
            variant="primary"
            :trend="statistics.quizzesTrend"
          />
        </div>
        <div class="col-12 col-sm-6 col-md-3">
          <quiz-stats
            icon="check_circle"
            label="Active Quizzes"
            :value="statistics.activeQuizzes"
            variant="success"
            :trend="statistics.activeTrend"
          />
        </div>
        <div class="col-12 col-sm-6 col-md-3">
          <quiz-stats
            icon="help_outline"
            label="Total Questions"
            :value="statistics.totalQuestions"
            variant="info"
          />
        </div>
        <div class="col-12 col-sm-6 col-md-3">
          <quiz-stats
            icon="trending_up"
            label="Avg. Completion"
            :value="`${statistics.avgCompletion}%`"
            variant="warning"
            :trend="statistics.completionTrend"
          />
        </div>
      </div>

      <!-- Filters and Search -->
      <q-card class="rounded-xl shadow-1 q-mb-lg">
        <q-card-section>
          <div class="row q-col-gutter-md items-center">
            <div class="col-12 col-md-4">
              <q-input
                v-model="searchQuery"
                outlined
                rounded
                dense
                placeholder="Search quizzes..."
                bg-color="grey-1"
              >
                <template v-slot:prepend>
                  <q-icon name="search" color="primary" />
                </template>
                <template v-slot:append v-if="searchQuery">
                  <q-icon
                    name="close"
                    class="cursor-pointer"
                    @click="searchQuery = ''"
                  />
                </template>
              </q-input>
            </div>
            
            <div class="col-12 col-md-2">
              <q-select
                v-model="filterGrade"
                outlined
                rounded
                dense
                :options="gradeOptions"
                label="Grade"
                clearable
                bg-color="grey-1"
              />
            </div>
            
            <div class="col-12 col-md-2">
              <q-select
                v-model="filterSubject"
                outlined
                rounded
                dense
                :options="subjectOptions"
                label="Subject"
                clearable
                bg-color="grey-1"
              />
            </div>
            
            <div class="col-12 col-md-2">
              <q-select
                v-model="filterStatus"
                outlined
                rounded
                dense
                :options="statusOptions"
                label="Status"
                clearable
                bg-color="grey-1"
              />
            </div>

            <div class="col-12 col-md-2 text-right">
              <q-btn
                flat
                round
                color="primary"
                icon="filter_list"
                @click="showAdvancedFilters = !showAdvancedFilters"
              >
                <q-tooltip>Advanced Filters</q-tooltip>
              </q-btn>
            </div>
          </div>
        </q-card-section>
        
        <!-- Advanced Filters (Collapsible) -->
        <q-slide-transition>
          <div v-show="showAdvancedFilters">
            <q-separator />
            <q-card-section class="bg-grey-1">
              <div class="row q-col-gutter-md">
                <div class="col-12 col-sm-4">
                  <q-input
                    v-model.number="filterMinQuestions"
                    outlined
                    rounded
                    dense
                    type="number"
                    label="Min Questions"
                    bg-color="white"
                  />
                </div>
                <div class="col-12 col-sm-4">
                  <q-input
                    v-model.number="filterMaxQuestions"
                    outlined
                    rounded
                    dense
                    type="number"
                    label="Max Questions"
                    bg-color="white"
                  />
                </div>
                <div class="col-12 col-sm-4">
                  <q-select
                    v-model="filterCreatedBy"
                    outlined
                    rounded
                    dense
                    :options="creatorOptions"
                    label="Created By"
                    clearable
                    bg-color="white"
                  />
                </div>
              </div>
            </q-card-section>
          </div>
        </q-slide-transition>
      </q-card>

      <!-- Quiz Grid -->
      <div class="quiz-dashboard__content">
        <!-- Loading State -->
        <div v-if="loading" class="flex flex-center q-pa-xl">
          <q-spinner-dots color="primary" size="3em" />
          <div class="text-grey-7 q-mt-md">Loading your awesome quizzes...</div>
        </div>

        <!-- Empty State -->
        <div v-else-if="filteredQuizzes.length === 0" class="text-center q-pa-xl">
          <q-icon name="sentiment_dissatisfied" size="80px" color="grey-4" />
          <h3 class="text-h5 text-grey-7 q-mt-md">No quizzes found</h3>
          <p class="text-grey-6" v-if="hasActiveFilters">Try adjusting your filters to find what you're looking for.</p>
          <p class="text-grey-6" v-else>Create your first quiz to get started!</p>
          <q-btn
            v-if="!hasActiveFilters"
            push
            glossy
            rounded
            color="primary"
            label="Create Quiz"
            class="q-mt-md"
            @click="showCreateDialog = true"
          />
        </div>

        <!-- Quiz Cards Grid -->
        <transition-group
          v-else
          name="quiz-grid"
          tag="div"
          class="row q-col-gutter-lg"
        >
          <div
            v-for="quiz in filteredQuizzes"
            :key="quiz.id"
            class="col-12 col-sm-6 col-lg-4"
          >
            <quiz-card
              :quiz="quiz"
              @click="viewQuiz(quiz)"
              @preview="previewQuiz"
              @edit="editQuiz"
              @analytics="viewAnalytics"
              @duplicate="duplicateQuiz"
              @export="exportQuiz"
              @delete="confirmDelete"
            />
          </div>
        </transition-group>
      </div>

      <!-- Create/Edit Quiz Dialog -->
      <q-dialog v-model="showCreateDialog" persistent>
        <q-card style="min-width: 600px; max-width: 800px" class="rounded-xl">
          <q-card-section class="row items-center bg-primary text-white q-py-md">
            <div class="text-h6 text-weight-bold">{{ editingQuiz ? 'Edit Quiz' : 'Create New Quiz' }}</div>
            <q-space />
            <q-btn icon="close" flat round dense v-close-popup />
          </q-card-section>

          <q-card-section class="q-pt-lg q-px-lg">
            <div class="q-gutter-md">
              <q-input
                v-model="quizForm.name"
                outlined
                rounded
                label="Quiz Name *"
                placeholder="e.g., Math Quiz - Chapter 1"
                :rules="[val => !!val || 'Name is required']"
              />
              
              <q-input
                v-model="quizForm.description"
                outlined
                rounded
                type="textarea"
                label="Description"
                placeholder="Brief description of the quiz"
                rows="3"
              />
              
              <div class="row q-col-gutter-md">
                <div class="col-6">
                  <q-select
                    v-model="quizForm.grade_id"
                    outlined
                    rounded
                    :options="gradeOptions"
                    label="Grade"
                    emit-value
                    map-options
                  />
                </div>
                
                <div class="col-6">
                  <q-select
                    v-model="quizForm.subject_id"
                    outlined
                    rounded
                    :options="subjectOptions"
                    label="Subject"
                    emit-value
                    map-options
                  />
                </div>
              </div>
              
              <div class="row q-col-gutter-md">
                <div class="col-6">
                  <q-input
                    v-model.number="quizForm.time_limit_minutes"
                    outlined
                    rounded
                    type="number"
                    label="Time Limit (minutes)"
                    min="1"
                  />
                </div>
                
                <div class="col-6">
                  <q-select
                    v-model="quizForm.status"
                    outlined
                    rounded
                    :options="statusOptions"
                    label="Status"
                    emit-value
                    map-options
                  />
                </div>
              </div>
              
              <div class="row q-col-gutter-md">
                <div class="col-6">
                  <q-toggle
                    v-model="quizForm.shuffle_questions"
                    label="Shuffle Questions"
                    color="primary"
                  />
                </div>
                
                <div class="col-6">
                  <q-toggle
                    v-model="quizForm.shuffle_options"
                    label="Shuffle Options"
                    color="primary"
                  />
                </div>
              </div>
              
              <q-toggle
                v-model="quizForm.allow_review"
                label="Allow Review After Completion"
                color="primary"
              />
            </div>
          </q-card-section>

          <q-card-actions align="right" class="q-px-lg q-pb-lg">
            <q-btn flat label="Cancel" color="grey" v-close-popup rounded />
            <q-btn
              push
              glossy
              rounded
              :label="editingQuiz ? 'Update' : 'Create'"
              color="primary"
              @click="saveQuiz"
              :loading="saving"
              :disable="!quizForm.name"
            />
          </q-card-actions>
        </q-card>
      </q-dialog>

      <!-- Delete Confirmation Dialog -->
      <q-dialog v-model="showDeleteDialog">
        <q-card class="rounded-xl">
          <q-card-section class="row items-center q-pa-lg">
            <q-avatar icon="warning" color="negative" text-color="white" size="lg" />
            <div class="q-ml-md">
              <div class="text-h6">Delete Quiz?</div>
              <div class="text-grey-7">Are you sure you want to delete this quiz? This action cannot be undone.</div>
            </div>
          </q-card-section>

          <q-card-actions align="right" class="q-pa-md">
            <q-btn flat label="Cancel" color="grey" v-close-popup rounded />
            <q-btn
              flat
              label="Delete"
              color="negative"
              @click="deleteQuiz"
              :loading="deleting"
              rounded
            />
          </q-card-actions>
        </q-card>
      </q-dialog>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import { useQuasar } from 'quasar';
import { router, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import QuizCard from '@/Components/Quiz/QuizCard.vue';
import QuizStats from '@/Components/Quiz/QuizStats.vue';
import QuizNavigation from '@/Components/Quiz/QuizNavigation.vue';

const $q = useQuasar();
const page = usePage();

// Get school_id from authenticated user
const schoolId = computed(() => page.props.auth?.user?.school_id || null);

// State
const loading = ref(false);
const saving = ref(false);
const deleting = ref(false);
const quizzes = ref([]);
const grades = ref([]);
const subjects = ref([]);
const creators = ref([]);

// Filters
const searchQuery = ref('');
const filterGrade = ref(null);
const filterSubject = ref(null);
const filterStatus = ref(null);
const filterMinQuestions = ref(null);
const filterMaxQuestions = ref(null);
const filterCreatedBy = ref(null);
const showAdvancedFilters = ref(false);

// Dialogs
const showCreateDialog = ref(false);
const showDeleteDialog = ref(false);
const editingQuiz = ref(null);
const deletingQuiz = ref(null);

// Form
const quizForm = ref({
  name: '',
  description: '',
  grade_id: null,
  subject_id: null,
  time_limit_minutes: null,
  status: 'draft',
  shuffle_questions: false,
  shuffle_options: false,
  allow_review: true
});

// Statistics
const statistics = ref({
  totalQuizzes: 0,
  activeQuizzes: 0,
  totalQuestions: 0,
  avgCompletion: 0,
  quizzesTrend: 0,
  activeTrend: 0,
  completionTrend: 0
});

// Computed
const gradeOptions = computed(() => {
  const gradesArray = Array.isArray(grades.value) ? grades.value : [];
  return gradesArray.map(g => ({ label: g.name, value: g.id }));
});

const subjectOptions = computed(() => {
  const subjectsArray = Array.isArray(subjects.value) ? subjects.value : [];
  return subjectsArray.map(s => ({ label: s.name, value: s.id }));
});

const statusOptions = computed(() => [
  { label: 'Active', value: 'active' },
  { label: 'Draft', value: 'draft' },
  { label: 'Archived', value: 'archived' }
]);

const creatorOptions = computed(() => {
  const creatorsArray = Array.isArray(creators.value) ? creators.value : [];
  return creatorsArray.map(c => ({ label: c.name, value: c.id }));
});

const hasActiveFilters = computed(() =>
  searchQuery.value ||
  filterGrade.value ||
  filterSubject.value ||
  filterStatus.value ||
  filterMinQuestions.value ||
  filterMaxQuestions.value ||
  filterCreatedBy.value
);

const filteredQuizzes = computed(() => {
  // Ensure quizzes.value is always an array
  const quizzesArray = Array.isArray(quizzes.value) ? quizzes.value : [];
  let result = [...quizzesArray];
  
  // Search
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    result = result.filter(q =>
      q.name.toLowerCase().includes(query) ||
      (q.description && q.description.toLowerCase().includes(query))
    );
  }
  
  // Grade filter
  if (filterGrade.value) {
    result = result.filter(q => q.grade_id === filterGrade.value);
  }
  
  // Subject filter
  if (filterSubject.value) {
    result = result.filter(q => q.subject_id === filterSubject.value);
  }
  
  // Status filter
  if (filterStatus.value) {
    result = result.filter(q => q.status === filterStatus.value);
  }
  
  // Question count filters
  if (filterMinQuestions.value) {
    result = result.filter(q => (q.questions_count || 0) >= filterMinQuestions.value);
  }
  
  if (filterMaxQuestions.value) {
    result = result.filter(q => (q.questions_count || 0) <= filterMaxQuestions.value);
  }
  
  // Creator filter
  if (filterCreatedBy.value) {
    result = result.filter(q => q.created_by_id === filterCreatedBy.value);
  }
  
  return result;
});

// Methods
const fetchQuizzes = async () => {
  loading.value = true;
  try {
    const params = {};
    if (schoolId.value) {
      params.school_id = schoolId.value;
    }
    // Don't filter by status by default - show all quizzes
    // The user can filter using the status dropdown
    params.status = 'all';
    
    const response = await axios.get('/api/quizzes', { params });
    console.log('Quiz API Response:', response.data)
    // Handle new response structure
    if (response.data.quizzes) {
      quizzes.value = response.data.quizzes;
      // Update grades and subjects from filters if available
      if (response.data.filters) {
        grades.value = response.data.filters.grades || [];
        subjects.value = response.data.filters.subjects || [];
      }
    } else {
      // Fallback for old response format
      quizzes.value = Array.isArray(response.data) ? response.data : [];
    }
    
    calculateStatistics();
  } catch (error) {
    console.error('Failed to fetch quizzes:', error);
    $q.notify({
      type: 'negative',
      message: 'Failed to load quizzes',
      icon: 'error'
    });
  } finally {
    loading.value = false;
  }
};

const fetchMetadata = async () => {
  try {
    const [gradesRes, subjectsRes] = await Promise.all([
      axios.get('/api/grades'),
      axios.get('/api/subjects')
    ]);
    
    // Ensure we always set arrays, handle different response structures
    const gradesData = gradesRes.data;
    grades.value = Array.isArray(gradesData) ? gradesData : (gradesData?.data || []);
    if (!Array.isArray(grades.value)) grades.value = [];

    const subjectsData = subjectsRes.data;
    subjects.value = Array.isArray(subjectsData) ? subjectsData : (subjectsData?.data || []);
    if (!Array.isArray(subjects.value)) subjects.value = [];

    subjects.value = Array.isArray(subjectsRes.data) ? subjectsRes.data : (subjectsRes.data?.data || []);
  } catch (error) {
    console.error('Failed to fetch metadata:', error);
    // Set empty arrays on error
    grades.value = [];
    subjects.value = [];
  }
};

const calculateStatistics = () => {
  // Ensure quizzes.value is always an array
  const quizzesArray = Array.isArray(quizzes.value) ? quizzes.value : [];
  
  statistics.value.totalQuizzes = quizzesArray.length;
  statistics.value.activeQuizzes = quizzesArray.filter(q => q.status === 'active').length;
  statistics.value.totalQuestions = quizzesArray.reduce((sum, q) => sum + (q.questions_count || 0), 0);
  
  // Mock data for trends - replace with actual API data
  statistics.value.quizzesTrend = 12;
  statistics.value.activeTrend = 8;
  statistics.value.avgCompletion = 78;
  statistics.value.completionTrend = 5;
};

const saveQuiz = async () => {
  if (!quizForm.value.name) {
    $q.notify({
      type: 'warning',
      message: 'Please enter a quiz name',
      icon: 'warning'
    });
    return;
  }
  
  saving.value = true;
  try {
    const formData = { ...quizForm.value };
    // Add school_id if available
    if (schoolId.value) {
      formData.school_id = schoolId.value;
    }
    
    if (editingQuiz.value) {
      await axios.put(`/api/quizzes/${editingQuiz.value.id}`, formData);
      $q.notify({
        type: 'positive',
        message: 'Quiz updated successfully',
        icon: 'check_circle'
      });
    } else {
      await axios.post('/api/quizzes', formData);
      $q.notify({
        type: 'positive',
        message: 'Quiz created successfully',
        icon: 'check_circle'
      });
    }
    
    showCreateDialog.value = false;
    resetForm();
    await fetchQuizzes();
  } catch (error) {
    console.error('Failed to save quiz:', error);
    $q.notify({
      type: 'negative',
      message: 'Failed to save quiz',
      icon: 'error'
    });
  } finally {
    saving.value = false;
  }
};

const viewQuiz = (quiz) => {
  router.visit(`/quizzes/${quiz.id}/preview`);
};

const previewQuiz = (quiz) => {
  router.visit(`/quizzes/${quiz.id}/preview`);
};

const editQuiz = (quiz) => {
  router.visit(`/quizzes/${quiz.id}/edit`);
};

const viewAnalytics = (quiz) => {
  router.visit(`/quizzes/${quiz.id}/analytics`);
};

const duplicateQuiz = async (quiz) => {
  try {
    await axios.post(`/api/quizzes/${quiz.id}/duplicate`);
    $q.notify({
      type: 'positive',
      message: 'Quiz duplicated successfully',
      icon: 'content_copy'
    });
    await fetchQuizzes();
  } catch (error) {
    console.error('Failed to duplicate quiz:', error);
    $q.notify({
      type: 'negative',
      message: 'Failed to duplicate quiz',
      icon: 'error'
    });
  }
};

const exportQuiz = async (quiz) => {
  try {
    const response = await axios.get(`/api/quizzes/${quiz.id}/export`, {
      responseType: 'blob'
    });
    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', `${quiz.name}.json`);
    document.body.appendChild(link);
    link.click();
    link.remove();
    
    $q.notify({
      type: 'positive',
      message: 'Quiz exported successfully',
      icon: 'download'
    });
  } catch (error) {
    console.error('Failed to export quiz:', error);
    $q.notify({
      type: 'negative',
      message: 'Failed to export quiz',
      icon: 'error'
    });
  }
};

const confirmDelete = (quiz) => {
  deletingQuiz.value = quiz;
  showDeleteDialog.value = true;
};

const deleteQuiz = async () => {
  deleting.value = true;
  try {
    await axios.delete(`/api/quizzes/${deletingQuiz.value.id}`);
    $q.notify({
      type: 'positive',
      message: 'Quiz deleted successfully',
      icon: 'delete'
    });
    showDeleteDialog.value = false;
    await fetchQuizzes();
  } catch (error) {
    console.error('Failed to delete quiz:', error);
    $q.notify({
      type: 'negative',
      message: 'Failed to delete quiz',
      icon: 'error'
    });
  } finally {
    deleting.value = false;
  }
};

const resetForm = () => {
  editingQuiz.value = null;
  quizForm.value = {
    name: '',
    description: '',
    grade_id: null,
    subject_id: null,
    time_limit_minutes: null,
    status: 'draft',
    shuffle_questions: false,
    shuffle_options: false,
    allow_review: true
  };
};

// Lifecycle
onMounted(() => {
  fetchQuizzes();
  fetchMetadata();
});
</script>

<style scoped lang="scss">

.stats-card {
   height: 100px;
    
  
}
.quiz-dashboard {
  min-height: 100vh;
  
  &__container {
    max-width: 1400px;
    margin: 0 auto;
  }
}

.bg-gradient-primary {
  background: linear-gradient(135deg, #1976D2 0%, #64B5F6 100%);
}

.rounded-xl {
  border-radius: 24px;
}

.opacity-90 {
  opacity: 0.9;
}

.opacity-50 {
  opacity: 0.5;
}

// Transitions
.quiz-grid-enter-active,
.quiz-grid-leave-active {
  transition: all 0.3s ease;
}

.quiz-grid-enter-from {
  opacity: 0;
  transform: translateY(20px);
}

.quiz-grid-leave-to {
  opacity: 0;
  transform: scale(0.9);
}
</style>
