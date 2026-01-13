<template>
  <div class="q-pa-md">
    <q-card>
      <q-card-section>
        <div class="row items-center">
          <div class="text-h5">Question Bank</div>
          <q-space />
          <q-btn
            color="primary"
            label="Create Question"
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
            v-model="localFilters.difficulty"
            :options="difficultyOptions"
            label="Difficulty"
            clearable
            @update:model-value="applyFilters"
            style="min-width: 150px"
          />

          <q-select
            v-model="localFilters.bloom_level"
            :options="bloomLevels"
            label="Bloom Level"
            clearable
            @update:model-value="applyFilters"
            style="min-width: 150px"
          />

          <q-select
            v-model="localFilters.question_type"
            :options="questionTypes"
            option-value="value"
            option-label="label"
            label="Question Type"
            clearable
            emit-value
            map-options
            @update:model-value="applyFilters"
            style="min-width: 150px"
          />
        </div>
      </q-card-section>

      <!-- Questions Table -->
      <q-card-section>
        <q-table
          :rows="questions.data"
          :columns="columns"
          row-key="id"
          flat
          bordered
          :pagination="pagination"
          @request="onTableRequest"
        >
          <template v-slot:body-cell-question_text="props">
            <q-td :props="props">
              <div class="text-subtitle2">
                {{ truncateText(props.row.question_text, 60) }}
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

          <template v-slot:body-cell-difficulty="props">
            <q-td :props="props">
              <q-badge 
                :color="difficultyColor(props.row.difficulty)"
                :label="props.row.difficulty"
              />
            </q-td>
          </template>

          <template v-slot:body-cell-bloom_level="props">
            <q-td :props="props">
              <q-chip 
                v-if="props.row.bloom_level"
                dense 
                :icon="getBloomIcon(props.row.bloom_level)"
                color="purple-3"
              >
                {{ capitalizeFirst(props.row.bloom_level) }}
              </q-chip>
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
                @click="viewQuestion(props.row)"
              >
                <q-tooltip>View</q-tooltip>
              </q-btn>
              <q-btn 
                flat 
                dense 
                round
                color="primary" 
                icon="edit" 
                :to="route('qu-questions.edit', props.row.id)"
              >
                <q-tooltip>Edit</q-tooltip>
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
      <q-card-section v-if="questions.last_page > 1" class="q-pt-none">
        <div class="row justify-center">
          <q-pagination
            v-model="currentPage"
            :max="questions.last_page"
            :max-pages="7"
            direction-links
            boundary-links
            @update:model-value="changePage"
          />
        </div>
      </q-card-section>
    </q-card>

    <!-- View Question Dialog -->
    <q-dialog v-model="viewDialog">
      <q-card style="min-width: 500px">
        <q-card-section>
          <div class="text-h6">Question Details</div>
        </q-card-section>

        <q-card-section v-if="selectedQuestion">
          <QuQuestionDisplay :question="selectedQuestion" />
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Close" color="primary" v-close-popup />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- Delete Confirmation Dialog -->
    <q-dialog v-model="deleteDialog">
      <q-card>
        <q-card-section>
          <div class="text-h6">Confirm Delete</div>
        </q-card-section>

        <q-card-section>
          Are you sure you want to delete this question?
        </q-card-section>

       <q-card-actions align="right">
          <q-btn flat label="Cancel" color="primary" v-close-popup />
          <q-btn flat label="Delete" color="negative" @click="deleteQuestion" />
        </q-card-actions>
       </q-card>
    </q-dialog>

    <!-- Create Question Dialog -->
    <q-dialog v-model="createDialog" maximized>
      <q-card>
        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6">Create Question</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>

        <q-card-section>
          <QuQuestionForm 
            :subjects="subjects" 
            @success="onQuestionCreated"
          />
        </q-card-section>
      </q-card>
    </q-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import QuQuestionDisplay from './QuComponents/QuQuestionDisplay.vue';
import QuQuestionForm from './QuQuestionForm.vue';

const props = defineProps({
  questions: Object,
  subjects: Array,
  filters: Object
});

const localFilters = reactive({
  subject_id: props.filters?.subject_id || null,
  difficulty: props.filters?.difficulty || null,
  bloom_level: props.filters?.bloom_level || null,
  question_type: props.filters?.question_type || null
});

const currentPage = ref(props.questions.current_page);
const viewDialog = ref(false);
const deleteDialog = ref(false);
const createDialog = ref(false);
const selectedQuestion = ref(null);
const questionToDelete = ref(null);

const difficultyOptions = ['easy', 'medium', 'hard'];

const bloomLevels = [
  'remember', 'understand', 'apply', 'analyze', 'evaluate', 'create'
];

const questionTypes = [
  { value: 'mcq', label: 'Multiple Choice' },
  { value: 'true_false', label: 'True/False' },
  { value: 'short', label: 'Short Answer' },
  { value: 'long', label: 'Long Answer' }
];

const columns = [
  { name: 'id', label: 'ID', field: 'id', align: 'left', sortable: true },
  { name: 'question_text', label: 'Question', field: 'question_text', align: 'left' },
  { name: 'subject', label: 'Subject', field: 'subject', align: 'left' },
  { name: 'question_type', label: 'Type', field: 'question_type', align: 'center' },
  { name: 'difficulty', label: 'Difficulty', field: 'difficulty', align: 'center' },
  { name: 'bloom_level', label: 'Bloom Level', field: 'bloom_level', align: 'center' },
  { name: 'marks', label: 'Marks', field: 'marks', align: 'center' },
  { name: 'actions', label: 'Actions', align: 'center' }
];

const pagination = computed(() => ({
  page: props.questions.current_page,
  rowsPerPage: props.questions.per_page,
  rowsNumber: props.questions.total
}));

const applyFilters = () => {
  router.get(route('qu-questions.index'), localFilters, {
    preserveState: true,
    preserveScroll: true
  });
};

const changePage = (page) => {
  router.get(route('qu-questions.index', { ...localFilters, page }), {}, {
    preserveState: true,
    preserveScroll: true
  });
};

const onTableRequest = (props) => {
  changePage(props.pagination.page);
};

const viewQuestion = (question) => {
  selectedQuestion.value = question;
  viewDialog.value = true;
};

const confirmDelete = (question) => {
  questionToDelete.value = question;
  deleteDialog.value = true;
};

const deleteQuestion = () => {
  router.delete(route('qu-questions.destroy', questionToDelete.value.id), {
    onSuccess: () => {
      deleteDialog.value = false;
      questionToDelete.value = null;
    }
  });
};

const truncateText = (text, length) => {
  return text && text.length > length ? text.substring(0, length) + '...' : text;
};

const capitalizeFirst = (str) => {
  return str ? str.charAt(0).toUpperCase() + str.slice(1) : '';
};

const difficultyColor = (difficulty) => {
  const colors = {
    easy: 'green',
    medium: 'orange',
    hard: 'red'
  };
  return colors[difficulty] || 'grey';
};

const getBloomIcon = (level) => {
  const icons = {
    remember: 'psychology',
    understand: 'lightbulb',
    apply: 'build',
    analyze: 'analytics',
    evaluate: 'fact_check',
    create: 'auto_awesome'
  };
  return icons[level] || 'help';
};

const onQuestionCreated = () => {
  createDialog.value = false;
  router.reload({ only: ['questions'] });
};
</script>
