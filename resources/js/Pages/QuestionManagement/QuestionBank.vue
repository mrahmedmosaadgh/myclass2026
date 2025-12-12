<template>
  <Head title="Question Bank" />
  <div class="question-bank-page">
    <div class="q-pa-md">
      <!-- Header -->
      <div class="row items-center justify-between q-mb-lg">
        <div>
          <h4 class="q-ma-none">Question Bank</h4>
          <p class="text-grey-7 q-mb-none">Manage your question library</p>
        </div>
        <div class="row q-gutter-sm">
          <q-btn
            color="grey-7"
            icon="download"
            label="Export"
            @click="showExportDialog = true"
          />
          <q-btn
            color="secondary"
            icon="upload"
            label="Import"
            @click="showImportDialog = true"
          />
          <q-btn
            color="primary"
            icon="add"
            label="New Question"
            @click="createQuestion"
          />
        </div>
      </div>

      <div class="row q-col-gutter-md">
        <!-- Filters Sidebar -->
        <div class="col-12 col-md-3">
          <question-filters v-model="filters" />
        </div>

        <!-- Questions List -->
        <div class="col-12 col-md-9">
          <!-- Search Bar -->
          <q-card flat bordered class="q-mb-md">
            <q-card-section class="q-pa-md">
              <q-input
                v-model="searchQuery"
                placeholder="Search questions..."
                outlined
                dense
                clearable
                @update:model-value="debouncedSearch"
              >
                <template v-slot:prepend>
                  <q-icon name="search" />
                </template>
              </q-input>
            </q-card-section>
          </q-card>

          <!-- Loading State -->
          <div v-if="loading" class="text-center q-py-xl">
            <q-spinner color="primary" size="50px" />
            <p class="text-grey-7 q-mt-md">Loading questions...</p>
          </div>

          <!-- Empty State -->
          <q-card v-else-if="questions.length === 0" flat bordered class="text-center q-py-xl">
            <q-icon name="quiz" size="64px" color="grey-5" />
            <p class="text-h6 q-mt-md">No questions found</p>
            <p class="text-grey-7">
              {{ hasActiveFilters ? 'Try adjusting your filters' : 'Create your first question to get started' }}
            </p>
            <q-btn
              v-if="!hasActiveFilters"
              color="primary"
              label="Create Question"
              icon="add"
              @click="createQuestion"
              class="q-mt-md"
            />
          </q-card>

          <!-- Questions Grid -->
          <div v-else class="questions-grid">
            <question-card
              v-for="question in questions"
              :key="question.id"
              :question="question"
              :show-analytics="true"
              @edit="editQuestion"
              @duplicate="duplicateQuestion"
              @delete="confirmDelete"
              @status-change="handleStatusChange"
              class="q-mb-md"
            />
          </div>

          <!-- Pagination -->
          <div v-if="pagination.last_page > 1" class="row justify-center q-mt-lg">
            <q-pagination
              v-model="pagination.current_page"
              :max="pagination.last_page"
              :max-pages="7"
              direction-links
              boundary-links
              @update:model-value="loadQuestions"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Dialog -->
    <q-dialog v-model="deleteDialog" persistent>
      <q-card style="min-width: 400px">
        <q-card-section class="row items-center">
          <q-icon name="warning" color="warning" size="32px" class="q-mr-md" />
          <span class="text-h6">Delete Question?</span>
        </q-card-section>

        <q-card-section>
          <p>Are you sure you want to delete this question?</p>
          <p class="text-grey-7 text-caption">This action cannot be undone.</p>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Cancel" color="grey-7" v-close-popup />
          <q-btn
            flat
            label="Delete"
            color="negative"
            @click="deleteQuestion"
            :loading="deleting"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- Import Dialog -->
    <q-dialog v-model="showImportDialog">
      <q-card style="min-width: 500px">
        <q-card-section>
          <div class="text-h6">Import Questions</div>
        </q-card-section>

        <q-card-section>
          <p class="text-grey-7">Upload a CSV or Excel file to import multiple questions at once.</p>
          <q-btn
            color="primary"
            label="Go to Import Page"
            icon="upload"
            @click="goToImport"
          />
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Close" color="grey-7" v-close-popup />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- Export Dialog -->
    <q-dialog v-model="showExportDialog">
      <q-card style="min-width: 500px">
        <q-card-section>
          <div class="text-h6">Export Questions</div>
        </q-card-section>

        <q-card-section>
          <p class="text-grey-7 q-mb-md">
            Export {{ hasActiveFilters ? 'filtered' : 'all' }} questions to Excel or CSV format.
          </p>
          
          <div class="q-mb-md">
            <div class="text-subtitle2 q-mb-sm">Export Format</div>
            <q-btn-toggle
              v-model="exportFormat"
              toggle-color="primary"
              :options="[
                { label: 'Excel (.xlsx)', value: 'xlsx' },
                { label: 'CSV', value: 'csv' }
              ]"
            />
          </div>

          <div class="text-caption text-grey-7">
            <q-icon name="info" size="sm" class="q-mr-xs" />
            {{ pagination.total }} questions will be exported
            {{ hasActiveFilters ? ' (filtered)' : '' }}
          </div>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Cancel" color="grey-7" v-close-popup />
          <q-btn
            label="Export"
            color="primary"
            icon="download"
            :loading="exporting"
            @click="exportQuestions"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { router, Head } from '@inertiajs/vue3';
import { useQuasar } from 'quasar';
import axios from 'axios';
import QuestionCard from '@/Components/QuestionBank/QuestionCard.vue';
import QuestionFilters from '@/Components/QuestionBank/QuestionFilters.vue';
const $q = useQuasar();

// State
const questions = ref([]);
const loading = ref(false);
const searchQuery = ref('');
const filters = ref({});
const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 20,
  total: 0
});

// Delete dialog
const deleteDialog = ref(false);
const questionToDelete = ref(null);
const deleting = ref(false);

// Import dialog
const showImportDialog = ref(false);

// Export dialog
const showExportDialog = ref(false);
const exportFormat = ref('xlsx');
const exporting = ref(false);

// Computed
const hasActiveFilters = computed(() => {
  return Object.values(filters.value).some(v => v !== null && v !== undefined && v !== '');
});

// Debounced search
let searchTimeout = null;
const debouncedSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    pagination.value.current_page = 1;
    loadQuestions();
  }, 300);
};

// Methods
const loadQuestions = async () => {
  loading.value = true;
  try {
    const params = {
      page: pagination.value.current_page,
      per_page: pagination.value.per_page,
      search: searchQuery.value || undefined,
      ...filters.value
    };

    // Remove null/undefined values
    Object.keys(params).forEach(key => {
      if (params[key] === null || params[key] === undefined) {
        delete params[key];
      }
    });

    const response = await axios.get('/api/questions', { params });
    
    if (response.data.success) {
      const data = response.data.data;
      questions.value = data.data || [];
      pagination.value = {
        current_page: data.current_page,
        last_page: data.last_page,
        per_page: data.per_page,
        total: data.total
      };
    }
  } catch (error) {
    console.error('Failed to load questions:', error);
    $q.notify({
      type: 'negative',
      message: 'Failed to load questions',
      caption: error.response?.data?.error?.message || error.message
    });
  } finally {
    loading.value = false;
  }
};

const createQuestion = () => {
  router.visit('/questions/create');
};

const editQuestion = (question) => {
  router.visit(`/questions/${question.id}/edit`);
};

const duplicateQuestion = async (question) => {
  try {
    const response = await axios.post(`/api/questions/${question.id}/duplicate`);
    
    if (response.data.success) {
      $q.notify({
        type: 'positive',
        message: 'Question duplicated successfully',
        caption: 'Opening editor...'
      });
      
      // Navigate to edit the duplicated question
      router.visit(`/questions/${response.data.data.id}/edit`);
    }
  } catch (error) {
    console.error('Failed to duplicate question:', error);
    $q.notify({
      type: 'negative',
      message: 'Failed to duplicate question',
      caption: error.response?.data?.error?.message || error.message
    });
  }
};

const confirmDelete = (question) => {
  questionToDelete.value = question;
  deleteDialog.value = true;
};

const deleteQuestion = async () => {
  if (!questionToDelete.value) return;
  
  deleting.value = true;
  try {
    const response = await axios.delete(`/api/questions/${questionToDelete.value.id}`);
    
    if (response.data.success) {
      $q.notify({
        type: 'positive',
        message: 'Question deleted successfully'
      });
      
      deleteDialog.value = false;
      questionToDelete.value = null;
      
      // Reload questions
      loadQuestions();
    }
  } catch (error) {
    console.error('Failed to delete question:', error);
    $q.notify({
      type: 'negative',
      message: 'Failed to delete question',
      caption: error.response?.data?.error?.message || error.message
    });
  } finally {
    deleting.value = false;
  }
};

const goToImport = () => {
  showImportDialog.value = false;
  router.visit('/questions/import');
};

const handleStatusChange = async (question, newStatus) => {
  try {
    const response = await axios.put(`/api/questions/${question.id}`, {
      ...question,
      status: newStatus
    });
    
    if (response.data.success) {
      $q.notify({
        type: 'positive',
        message: 'Status updated successfully',
        caption: `Question is now ${newStatus}`
      });
      
      // Update the question in the list
      const index = questions.value.findIndex(q => q.id === question.id);
      if (index !== -1) {
        questions.value[index].status = newStatus;
      }
    }
  } catch (error) {
    console.error('Failed to update status:', error);
    $q.notify({
      type: 'negative',
      message: 'Failed to update status',
      caption: error.response?.data?.error?.message || error.message
    });
  }
};

const exportQuestions = async () => {
  exporting.value = true;
  
  try {
    const params = {
      format: exportFormat.value,
      search: searchQuery.value || undefined,
      ...filters.value
    };

    // Remove null/undefined values
    Object.keys(params).forEach(key => {
      if (params[key] === null || params[key] === undefined) {
        delete params[key];
      }
    });

    const response = await axios.get('/api/questions/export', {
      params,
      responseType: 'blob'
    });

    // Create download link
    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', `questions_export.${exportFormat.value}`);
    document.body.appendChild(link);
    link.click();
    link.remove();
    window.URL.revokeObjectURL(url);

    $q.notify({
      type: 'positive',
      message: 'Export completed successfully',
      caption: `Downloaded questions_export.${exportFormat.value}`
    });

    showExportDialog.value = false;
  } catch (error) {
    console.error('Failed to export questions:', error);
    $q.notify({
      type: 'negative',
      message: 'Failed to export questions',
      caption: error.response?.data?.error?.message || error.message
    });
  } finally {
    exporting.value = false;
  }
};

// Watch filters
watch(filters, () => {
  pagination.value.current_page = 1;
  loadQuestions();
}, { deep: true });

// Load questions on mount
onMounted(() => {
  loadQuestions();
});
</script>

<style scoped lang="scss">
.question-bank-page {
  background: #f7fafc;
  min-height: 100vh;
}

.questions-grid {
  display: grid;
  gap: 16px;
}
</style>
