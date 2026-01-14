<template>
  <Head title="Exam Management" />
  <div class="q-pa-md">
    <q-card>
      <q-card-section>
        <div class="row items-center">
          <div class="text-h5">Exam Management</div>
          <q-space />
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
            label="Subject"
            clearable
            emit-value
            map-options
            @update:model-value="applyFilters"
            style="min-width: 200px"
          />
          
          <q-select
            v-model="localFilters.exam_type"
            :options="examTypeOptions"
            label="Exam Type"
            clearable
            @update:model-value="applyFilters"
            style="min-width: 150px"
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
            <q-td :props="props">
              <div class="text-subtitle2">
                {{ props.row.title }}
              </div>
              <div v-if="props.row.description" class="text-caption text-grey-7">
                {{ truncateText(props.row.description, 50) }}
              </div>
            </q-td>
          </template>

          <template v-slot:body-cell-subject="props">
            <q-td :props="props">
              <q-chip dense color="primary" text-color="white">
                {{ props.row.subject?.name }}
              </q-chip>
            </q-td>
          </template>

          <template v-slot:body-cell-exam_type="props">
            <q-td :props="props">
              <q-chip 
                dense 
                :color="examTypeColor(props.row.exam_type)"
                text-color="white"
              >
                {{ capitalizeFirst(props.row.exam_type) }}
              </q-chip>
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
              <q-btn 
                flat 
                dense 
                round
                color="primary" 
                icon="visibility" 
                @click="viewExam(props.row)"
              >
                <q-tooltip>View</q-tooltip>
              </q-btn>
              <q-btn 
                flat 
                dense 
                round
                color="primary" 
                icon="edit" 
                @click="editExam(props.row)"
              >
                <q-tooltip>Edit</q-tooltip>
              </q-btn>
              <q-btn 
                flat 
                dense 
                round
                color="secondary" 
                icon="content_copy" 
                @click="duplicateExam(props.row)"
              >
                <q-tooltip>Duplicate</q-tooltip>
              </q-btn>
              <q-btn 
                flat 
                dense 
                round
                color="indigo" 
                icon="print" 
                @click="openPrintDialog(props.row)"
              >
                <q-tooltip>Print Preview</q-tooltip>
              </q-btn>
              <q-btn 
                flat 
                dense 
                round
                color="negative" 
                icon="delete" 
                @click="confirmDelete(props.row)"
              >
                <q-tooltip>Delete</q-tooltip>
              </q-btn>
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
            :src="route('qu-student.exams.print', selectedExamForPrint?.id)" 
            style="width: 100%; height: 100%; border: none;"
          ></iframe>
        </q-card-section>
      </q-card>
    </q-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import QuExamForm from './QuExamForm.vue';

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
  exam_type: props.filters?.exam_type || null,
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
  { name: 'subject', label: 'Subject', field: 'subject', align: 'left' },
  { name: 'exam_type', label: 'Type', field: 'exam_type', align: 'center' },
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

// Auto-apply filter if first subject was auto-selected
if (!props.filters?.subject_id && localFilters.subject_id) {
  applyFilters();
}

const onTableRequest = (props) => {
  changePage(props.pagination.page);
};

const viewExam = (exam) => {
  router.visit(route('qu-exams.show', exam.id));
};

const editExam = async (exam) => {
  try {
    const response = await window.axios.get(route('qu-exams.edit', exam.id));
    editingExam.value = response.data;
    createDialog.value = true;
  } catch (error) {
    console.error('Failed to load exam data', error);
  }
};

const duplicateExam = (exam) => {
  // Create a copy of the exam with modified title
  const duplicatedExam = {
    ...exam,
    title: `${exam.title} (Copy)`,
    is_published: false
  };
  editingExam.value = duplicatedExam;
  createDialog.value = true;
};

const confirmDelete = (exam) => {
  examToDelete.value = exam;
  deleteDialog.value = true;
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

const onExamSaved = () => {
  createDialog.value = false;
  editingExam.value = null;
  router.reload({ only: ['exams'] });
};
</script>
