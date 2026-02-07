<template>
  <Head title="Exam Management" />
  <div class="q-pa-md">
    <q-card>
      <q-card-section>
        <div class="row items-center">
          <div class="text-h5">Exam Management</div>
          <q-space />
          <q-btn
            color="secondary"
            label="Analytics"
            icon="analytics"
            class="q-mr-sm"
            @click="goToAnalytics"
          />
          <q-btn
            color="primary"
            label="Create Exam"
            icon="add"
            @click="createDialog = true"
          />
        </div>
      </q-card-section>

      <!-- Filters -->
      <q-card-section>
        <div class="row q-gutter-md">
          <q-select
            v-model="localFilters.subject_id"
            :options="subjects"
            option-value="id"
            option-label="name"
            label="Subject *"
            emit-value
            map-options
            @update:model-value="applyFilters"
            style="min-width: 200px"
            :rules="[val => !!val || 'Subject is required']"
          />
          
          <q-select
            v-model="localFilters.exam_type"
            :options="examTypeOptions"
            label="Exam Type *"
            @update:model-value="applyFilters"
            style="min-width: 150px"
            :rules="[val => !!val || 'Type is required']"
          />

          <q-select
            v-model="localFilters.custom_group"
            :options="customGroups"
            label="Custom Group"
            clearable
            @update:model-value="applyFilters"
            style="min-width: 150px"
          />

          <q-select
            v-model="localFilters.status"
            :options="statusOptions"
            label="Status"
            clearable
            @update:model-value="applyFilters"
            style="min-width: 150px"
          />
        </div>
      </q-card-section>

      <!-- Exams Table -->
      <q-card-section>
        <q-table
          :rows="exams.data"
          :columns="columns"
          row-key="id"
          flat
          bordered
          :pagination="pagination"
          @request="onTableRequest"
        >
          <template v-slot:body-cell-title="props">
            <q-td :props="props" style="max-width: 250px">
              <div class="text-subtitle2 ellipsis">
                {{ props.row.title }}
                <q-tooltip>{{ props.row.title }}</q-tooltip>
              </div>
              <div v-if="props.row.description" class="text-caption text-grey-7 ellipsis">
                {{ truncateText(props.row.description, 50) }}
              </div>
            </q-td>
          </template>



          <template v-slot:body-cell-custom_group="props">
            <q-td :props="props">
              <q-chip v-if="props.row.custom_group" dense outline color="purple">
                {{ props.row.custom_group }}
              </q-chip>
            </q-td>
          </template>

          <template v-slot:body-cell-status="props">
            <q-td :props="props">
              <q-badge 
                :color="statusColor(props.row.status)"
                :label="capitalizeFirst(props.row.status)"
              />
            </q-td>
          </template>

          <template v-slot:body-cell-bloom_distribution="props">
            <q-td :props="props">
              <div v-if="props.row.bloom_distribution" class="row q-gutter-xs">
                <template v-for="(count, level) in props.row.bloom_distribution" :key="level">
                  <q-chip 
                    v-if="count > 0"
                    dense 
                    size="sm"
                    color="purple-2"
                  >
                    {{ level }}: {{ count }}
                  </q-chip>
                </template>
              </div>
            </q-td>
          </template>

          <template v-slot:body-cell-actions="props">
            <q-td :props="props">
              <q-btn-dropdown flat dense rounded color="primary" icon="more_vert">
                <q-list>
                  <q-item clickable v-close-popup @click="viewExam(props.row)">
                    <q-item-section avatar>
                      <q-icon name="visibility" color="primary" />
                    </q-item-section>
                    <q-item-section>
                      <q-item-label>View</q-item-label>
                    </q-item-section>
                  </q-item>

                  <q-item clickable v-close-popup @click="copyStudentLink(props.row)">
                    <q-item-section avatar>
                      <q-icon name="link" color="teal" />
                    </q-item-section>
                    <q-item-section>
                      <q-item-label>Copy Student Link</q-item-label>
                    </q-item-section>
                  </q-item>

                  <q-item clickable v-close-popup @click="editExam(props.row)">
                    <q-item-section avatar>
                      <q-icon name="edit" color="primary" />
                    </q-item-section>
                    <q-item-section>
                      <q-item-label>Edit Settings</q-item-label>
                    </q-item-section>
                  </q-item>

                   <q-item clickable v-close-popup @click="goToBuilder(props.row)">
                    <q-item-section avatar>
                      <q-icon name="build" color="secondary" />
                    </q-item-section>
                    <q-item-section>
                      <q-item-label>Edit Questions</q-item-label>
                    </q-item-section>
                  </q-item>

                  <q-item clickable v-close-popup @click="duplicateExam(props.row)">
                    <q-item-section avatar>
                      <q-icon name="content_copy" color="secondary" />
                    </q-item-section>
                    <q-item-section>
                      <q-item-label>Duplicate</q-item-label>
                    </q-item-section>
                  </q-item>

                  <q-item clickable v-close-popup @click="openPrintDialog(props.row)">
                    <q-item-section avatar>
                      <q-icon name="print" color="indigo" />
                    </q-item-section>
                    <q-item-section>
                      <q-item-label>Print Preview</q-item-label>
                    </q-item-section>
                  </q-item>

                  <q-item clickable v-close-popup @click="viewGrading(props.row)">
                    <q-item-section avatar>
                      <q-icon name="grading" color="orange" />
                    </q-item-section>
                    <q-item-section>
                      <q-item-label>View Grading</q-item-label>
                    </q-item-section>
                  </q-item>

                  <q-separator />

                  <q-item clickable v-close-popup @click="confirmDelete(props.row)">
                    <q-item-section avatar>
                      <q-icon name="delete" color="negative" />
                    </q-item-section>
                    <q-item-section>
                      <q-item-label class="text-negative">Delete</q-item-label>
                    </q-item-section>
                  </q-item>
                </q-list>
              </q-btn-dropdown>
            </q-td>
          </template>
        </q-table>
      </q-card-section>

      <!-- Pagination -->
      <q-card-section v-if="exams.last_page > 1" class="q-pt-none">
        <div class="row justify-center">
          <q-pagination
            v-model="currentPage"
            :max="exams.last_page"
            :max-pages="7"
            direction-links
            boundary-links
            @update:model-value="changePage"
          />
        </div>
      </q-card-section>
    </q-card>

    <!-- Delete Confirmation Dialog -->
    <q-dialog v-model="deleteDialog">
      <q-card>
        <q-card-section>
          <div class="text-h6">Confirm Delete</div>
        </q-card-section>

        <q-card-section>
          Are you sure you want to delete this exam? This action cannot be undone.
        </q-card-section>

       <q-card-actions align="right">
          <q-btn flat label="Cancel" color="primary" v-close-popup />
          <q-btn flat label="Delete" color="negative" @click="deleteExam" />
        </q-card-actions>
       </q-card>
    </q-dialog>

    <!-- Create/Edit Exam Dialog -->
    <q-dialog v-model="createDialog" maximized>
      <q-card>
        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6">{{ editingExam ? 'Edit Exam' : 'Create Exam' }}</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>

        <q-card-section>
          <QuExamForm 
            :subjects="subjects" 
            :grades="grades"
            :classrooms="classrooms"
            :exam-types="examTypes"
            :mark-calculation-methods="markCalculationMethods"
            :publish-results-timings="publishResultsTimings"
            :custom-groups="customGroups"
            :selected-subject-id="localFilters.subject_id"
            :exam="editingExam"
            @success="onExamSaved"
            @cancel="createDialog = false"
          />
        </q-card-section>
      </q-card>
    </q-dialog>

    <!-- Print Preview Dialog -->
    <q-dialog v-model="showPrintDialogState" maximized transition-show="slide-up" transition-hide="slide-down">
      <q-card class="bg-white text-black">
        <q-bar class="bg-primary text-white">
          <q-icon name="print" />
          <div>Print Preview: {{ selectedExamForPrint?.title }}</div>
          <q-space />
          <q-btn dense flat icon="close" v-close-popup>
            <q-tooltip>Close</q-tooltip>
          </q-btn>
        </q-bar>
        
        <q-card-section class="q-pa-none" style="height: calc(100vh - 50px);">
          <iframe 
            v-if="selectedExamForPrint"
            id="printFrame"
            :src="route('qu.student.exams.print', selectedExamForPrint?.id)" 
            style="width: 100%; height: 100%; border: none;"
          ></iframe>
        </q-card-section>
      </q-card>
    </q-dialog>

    <!-- Grading List Dialog -->
    <q-dialog v-model="showGradingDialog" maximized>
      <q-card>
        <q-bar class="bg-primary text-white">
          <q-icon name="grading" />
          <div>Grading: {{ gradingExam?.title }}</div>
          <q-space />
          <q-btn dense flat icon="close" v-close-popup>
            <q-tooltip>Close</q-tooltip>
          </q-btn>
        </q-bar>

        <q-card-section>
          <div class="row q-col-gutter-md q-mb-md">
            <div class="col-12 col-md-3">
              <q-select
                v-model="gradingFilters.grading_status"
                :options="statusOptions"
                option-value="value"
                option-label="label"
                label="Grading Status"
                clearable
                emit-value
                map-options
                @update:model-value="fetchGradingAttempts"
                dense
                outlined
              />
            </div>
            
            <div class="col-12 col-md-3">
              <q-select
                v-model="gradingFilters.classroom_id"
                :options="classrooms"
                option-value="id"
                option-label="name"
                label="Classroom"
                clearable
                emit-value
                map-options
                @update:model-value="fetchGradingAttempts"
                dense
                outlined
              />
            </div>
          </div>

          <q-table
            :rows="gradingAttempts"
            :columns="gradingColumns"
            row-key="id"
            flat
            bordered
            :loading="gradingLoading"
          >
            <template v-slot:body-cell-score="props">
              <q-td :props="props">
                <q-chip
                  :color="getGradingScoreColor(props.row)"
                  text-color="white"
                  size="sm"
                >
                  {{ Math.round(props.row.score || 0) }} / {{ gradingExam?.total_marks }}
                </q-chip>
              </q-td>
            </template>

            <template v-slot:body-cell-status="props">
              <q-td :props="props">
                <q-badge
                  :color="getGradingStatusColor(props.row.status)"
                  :label="capitalizeFirst(props.row.status)"
                />
              </q-td>
            </template>

            <template v-slot:body-cell-actions="props">
              <q-td :props="props">
                <q-btn
                  color="primary"
                  icon="edit"
                  label="Grade"
                  size="sm"
                  @click="goToGrading(props.row)"
                />
              </q-td>
            </template>
          </q-table>
        </q-card-section>
      </q-card>
    </q-dialog>

    <!-- Nested Grading Interface -->
    <QuGradingDialog
      v-model="showGradingInterface"
      :attempt-id="currentAttemptId"
      @graded="onGradingComplete"
    />
  </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue';
import { useQuasar } from 'quasar';
import { router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import QuExamForm from './QuExamForm.vue';
import QuGradingDialog from './QuGradingDialog.vue';

const $q = useQuasar();

// Print Dialog Logic
const showPrintDialogState = ref(false);
const selectedExamForPrint = ref(null);

const openPrintDialog = (exam) => {
  selectedExamForPrint.value = exam;
  showPrintDialogState.value = true;
};

const props = defineProps({
  exams: Object,
  subjects: Array,
  grades: Array,
  classrooms: Array,
  customGroups: Array,
  examTypes: Array,
  markCalculationMethods: Array,
  publishResultsTimings: Array,
  filters: Object
});

const localFilters = reactive({
  subject_id: props.filters?.subject_id || (props.subjects.length > 0 ? props.subjects[0].id : null),
  exam_type: props.filters?.exam_type || 'quiz', // Default to quiz instead of null
  custom_group: props.filters?.custom_group || null,
  status: props.filters?.status || null
});

const currentPage = ref(props.exams.current_page);
const deleteDialog = ref(false);
const createDialog = ref(false);
const examToDelete = ref(null);
const editingExam = ref(null);

// Helper functions - defined before usage
const capitalizeFirst = (str) => {
  return str ? str.charAt(0).toUpperCase() + str.slice(1) : '';
};

const examTypeOptions = props.examTypes.map(type => ({
  value: type,
  label: capitalizeFirst(type)
}));

const statusOptions = [
  { value: 'draft', label: 'Draft' },
  { value: 'upcoming', label: 'Upcoming' },
  { value: 'active', label: 'Active' },
  { value: 'ended', label: 'Ended' }
];

const columns = [
  { name: 'id', label: 'ID', field: 'id', align: 'left', sortable: true },
  { name: 'title', label: 'Title', field: 'title', align: 'left' },
  // Subject and Exam Type removed as they are filtered
  { name: 'custom_group', label: 'Group', field: 'custom_group', align: 'center' },
  { name: 'questions_count', label: 'Questions', field: 'questions_count', align: 'center' },
  { name: 'total_marks', label: 'Marks', field: 'total_marks', align: 'center' },
  { name: 'duration_minutes', label: 'Duration (min)', field: 'duration_minutes', align: 'center' },
  { name: 'status', label: 'Status', field: 'status', align: 'center' },
  { name: 'actions', label: 'Actions', align: 'center' }
];

const pagination = computed(() => ({
  page: props.exams.current_page,
  rowsPerPage: props.exams.per_page,
  rowsNumber: props.exams.total
}));

const applyFilters = () => {
  router.get(route('qu-exams.index'), localFilters, {
    preserveState: true,
    preserveScroll: true
  });
};

const changePage = (page) => {
  router.get(route('qu-exams.index', { ...localFilters, page }), {}, {
    preserveState: true,
    preserveScroll: true
  });
};

// Auto-apply filter if first subject was auto-selected or type defaulted
if ((!props.filters?.subject_id && localFilters.subject_id) || (!props.filters?.exam_type && localFilters.exam_type)) {
  applyFilters();
}

const onTableRequest = (props) => {
  changePage(props.pagination.page);
};

const viewExam = (exam) => {
  router.visit(route('qu-exams.show', exam.id));
};

const copyStudentLink = (exam) => {
  const url = route('qu.student.exams.show', exam.id);
  navigator.clipboard.writeText(url)
    .then(() => {
      $q.notify({
        type: 'positive',
        message: 'Student exam link copied to clipboard',
        icon: 'content_copy',
        position: 'top'
      });
    })
    .catch((err) => {
      console.error('Failed to copy: ', err);
      $q.notify({
        type: 'negative',
        message: 'Failed to copy link',
        icon: 'error'
      });
    });
};


const editExam = async (exam) => {
  try {
    const response = await window.axios.get(route('qu-exams.data', exam.id));
    editingExam.value = response.data;
    createDialog.value = true;
  } catch (error) {
    console.error('Failed to load exam data', error);
  }
};

const duplicateExam = async (exam) => {
  try {
    // Fetch full data for duplication to ensure settings etc are copied
    const response = await window.axios.get(route('qu-exams.data', exam.id));
    const fullExamData = response.data;
    
    // Create a copy of the exam with modified title
    const duplicatedExam = {
      ...fullExamData,
      id: null, // Clear ID so it creates new
      title: `${fullExamData.title} (Copy)`,
      is_published: false
    };
    
    // If we want to duplicate questions, we need to ensure getExamData returns them.
    // user didn't explicitly ask for deep copy but "Duplicate" implies it.
    // However, the form separates question selection now. 
    // The form initializes question_ids from props.exam?.questions.
    
    editingExam.value = duplicatedExam;
    createDialog.value = true;
  } catch (error) {
    console.error('Failed to load exam data for duplication', error);
  }
};

const confirmDelete = (exam) => {
  examToDelete.value = exam;
  deleteDialog.value = true;
};

const goToAnalytics = () => {
  router.visit(route('qu-analytics.index'));
};

const deleteExam = () => {
  router.delete(route('qu-exams.destroy', examToDelete.value.id), {
    onSuccess: () => {
      deleteDialog.value = false;
      examToDelete.value = null;
    }
  });
};

const truncateText = (text, length) => {
  return text && text.length > length ? text.substring(0, length) + '...' : text;
};

const statusColor = (status) => {
  const colors = {
    draft: 'grey',
    upcoming: 'blue',
    active: 'green',
    ended: 'orange'
  };
  return colors[status] || 'grey';
};

const examTypeColor = (type) => {
  const colors = {
    practice: 'cyan',
    quiz: 'purple',
    midterm: 'orange',
    final: 'red',
    survey: 'teal'
  };
  return colors[type] || 'grey';
};

// Grading Dialog Logic
const showGradingDialog = ref(false);
const gradingAttempts = ref([]);
const gradingLoading = ref(false);
const gradingExam = ref(null);
const gradingFilters = reactive({
  grading_status: null,
  classroom_id: null
});

const openGradingDialog = async (exam) => {
  gradingExam.value = exam;
  showGradingDialog.value = true;
  gradingFilters.grading_status = null;
  gradingFilters.classroom_id = null;
  await fetchGradingAttempts();
};

const fetchGradingAttempts = async () => {
  if (!gradingExam.value) return;
  
  gradingLoading.value = true;
  try {
    const response = await window.axios.get(route('qu-exams.grading-attempts', gradingExam.value.id), {
      params: gradingFilters
    });
    gradingAttempts.value = response.data.attempts;
  } catch (error) {
    console.error('Failed to load grading attempts', error);
  } finally {
    gradingLoading.value = false;
  }
};

const goToGrading = (attempt) => {
  currentAttemptId.value = attempt.id;
  showGradingInterface.value = true;
};

const getGradingScoreColor = (attempt) => {
  if (!gradingExam.value?.passing_score) return 'primary';
  const percentage = (attempt.score / gradingExam.value.total_marks) * 100;
  return percentage >= gradingExam.value.passing_score ? 'positive' : 'negative';
};

const getGradingStatusColor = (status) => {
  const colors = {
    pending: 'grey',
    partial: 'orange',
    completed: 'positive'
  };
  return colors[status] || 'grey';
};

const gradingColumns = [
  { name: 'student', label: 'Student', field: row => row.user.name, align: 'left' },
  { name: 'classroom', label: 'Classroom', field: row => row.user.classroom, align: 'left' },
  { name: 'score', label: 'Score', field: 'score', align: 'center' },
  { name: 'status', label: 'Status', field: 'status', align: 'center' },
  { name: 'submitted', label: 'Submitted', field: 'submitted_at', align: 'center' },
  { name: 'actions', label: 'Actions', align: 'center' }
];

const viewGrading = (exam) => {
  openGradingDialog(exam);
};

const onExamSaved = () => {
  createDialog.value = false;
  editingExam.value = null;
  router.reload({ only: ['exams'] });
};

// Nested Grading Interface Logic
const showGradingInterface = ref(false);
const currentAttemptId = ref(null);

const goToBuilder = (exam) => {
  // Use the route for editing which opens the builder
  // We need to check if there is a specific route for builder or if 'qu-exams.edit' serves the builder page
  // Based on your previous request "Question Selection in Edit Exam dialog remove it", 
  // and "add icon btn to edit questions for the selected exam", 
  // I assume 'qu-exams.edit' route renders QuQuizBuilder as per previous context.
  
  // Let's verify the route alias in web.php
  // Route::get('/qu-exams/{id}/edit', ...)->name('qu-exams.edit'); -> renders QuQuizBuilder
  
  router.visit(route('qu-exams.edit', exam.id));
};

const onGradingComplete = () => {
  // Refresh the attempts list to show updated scores/status
  fetchGradingAttempts();
};
</script>
