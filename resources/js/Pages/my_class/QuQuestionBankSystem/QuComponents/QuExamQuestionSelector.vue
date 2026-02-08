<template>
  <q-dialog :model-value="modelValue" @update:model-value="$emit('update:modelValue', $event)" maximized>
    <q-card>
      <q-card-section class="row items-center q-pb-none">
        <div class="text-h6">Select Questions</div>
        <q-space />
        <q-btn icon="close" flat round dense @click="$emit('update:modelValue', false)" />
      </q-card-section>

      <!-- Filters -->
      <q-card-section>
        <div class="row q-gutter-md">
          <q-select
            v-model="filters.difficulty"
            :options="difficultyOptions"
            label="Difficulty"
            clearable
            style="min-width: 150px"
            @update:model-value="loadQuestions"
          />

          <q-select
            v-model="filters.bloom_level"
            :options="bloomLevels"
            label="Bloom Level"
            clearable
            style="min-width: 150px"
            @update:model-value="loadQuestions"
          />

          <q-select
            v-model="filters.topic_id"
            :options="topics"
            option-value="id"
            option-label="name"
            label="Topic"
            clearable
            emit-value
            map-options
            style="min-width: 200px"
            @update:model-value="loadQuestions"
          />

          <q-select
            v-model="filters.question_type"
            :options="questionTypes"
            option-value="value"
            option-label="label"
            label="Question Type"
            clearable
            emit-value
            map-options
            style="min-width: 150px"
            @update:model-value="loadQuestions"
          />

          <q-input
            v-model="filters.custom_group"
            label="Group"
            debounce="500"
            clearable
            style="min-width: 150px"
            @update:model-value="loadQuestions"
          />

          <q-input
            v-model="filters.search"
            label="Search"
            debounce="500"
            clearable
            style="min-width: 200px"
            @update:model-value="loadQuestions"
          >
            <template v-slot:prepend>
              <q-icon name="search" />
            </template>
          </q-input>
        </div>
      </q-card-section>

      <!-- Selection Controls -->
      <q-card-section class="q-pt-none">
        <div class="row items-center q-gutter-sm">
          <q-btn
            size="sm"
            outline
            color="primary"
            label="Select All"
            @click="selectAll"
          />
          <q-btn
            size="sm"
            outline
            color="primary"
            label="Select None"
            @click="selectNone"
          />
          <q-btn
            size="sm"
            outline
            color="primary"
            label="Inverse"
            @click="selectInverse"
          />
          <q-space />
          <div class="text-subtitle2">
            Selected: {{ selection.length }} questions | Total Marks: {{ totalMarks }}
          </div>
        </div>
      </q-card-section>

      <!-- Questions Table -->
      <q-card-section>
        <q-table
          :rows="questions"
          :columns="columns"
          row-key="id"
          selection="multiple"
          v-model:selected="selection"
          flat
          bordered
          :loading="loading"
          :pagination="pagination"
          @request="onTableRequest"
        >
          <template v-slot:body-cell-question_text="props">
            <q-td :props="props">
              <div class="text-subtitle2">
                {{ truncateText(props.row.question_text, 80) }}
              </div>
              <div class="text-caption text-grey-7">
                {{ props.row.topic?.name }}
              </div>
            </q-td>
          </template>

          <template v-slot:body-cell-custom_group="props">
            <q-td :props="props">
              <q-chip 
                v-if="props.row.custom_group"
                dense 
                outline
                color="primary"
                :label="props.row.custom_group"
              />
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

          <template v-slot:body-cell-preview="props">
            <q-td :props="props">
              <q-btn
                flat
                dense
                round
                color="primary"
                icon="visibility"
                @click="previewQuestion(props.row)"
              >
                <q-tooltip>Preview</q-tooltip>
              </q-btn>
            </q-td>
          </template>
        </q-table>
      </q-card-section>

      <!-- Bloom Distribution Summary -->
      <q-card-section v-if="selection.length > 0">
        <div class="text-subtitle2 q-mb-sm">Bloom Distribution of Selected Questions</div>
        <div class="row q-gutter-sm">
          <template v-for="(count, level) in bloomDistribution" :key="level">
            <q-chip
              v-if="count > 0"
              :icon="getBloomIcon(level)"
              color="purple-2"
            >
              {{ capitalizeFirst(level) }}: {{ count }}
            </q-chip>
          </template>
        </div>
      </q-card-section>

      <!-- Actions -->
      <q-card-actions align="right">
        <q-btn flat label="Cancel" color="negative" @click="$emit('update:modelValue', false)" />
        <q-btn
          unelevated
          :label="`Confirm (${selection.length} questions)`"
          color="primary"
          @click="confirmSelection"
          :disable="selection.length === 0"
        />
      </q-card-actions>
    </q-card>

    <!-- Preview Dialog -->
    <q-dialog v-model="previewDialog">
      <q-card style="min-width: 500px">
        <q-card-section>
          <div class="text-h6">Question Preview</div>
        </q-card-section>

        <q-card-section v-if="previewedQuestion">
          <QuQuestionDisplay :question="previewedQuestion" />
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Close" color="primary" v-close-popup />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-dialog>
</template>

<script setup>
import { ref, reactive, computed, watch, onMounted } from 'vue';
import { route } from 'ziggy-js';
import axios from 'axios';
import { useQuasar } from 'quasar';
import QuQuestionDisplay from './QuQuestionDisplay.vue';

const $q = useQuasar();

const props = defineProps({
  modelValue: Boolean,
  subjectId: [Number, String, null],
  selectedQuestions: {
    type: Array,
    default: () => []
  }
});

const emit = defineEmits(['update:modelValue', 'update:selectedQuestions']);

const questions = ref([]);
const selection = ref([...props.selectedQuestions]);
const loading = ref(false);
const previewDialog = ref(false);
const previewedQuestion = ref(null);
const topics = ref([]);

const filters = reactive({
  difficulty: null,
  bloom_level: null,
  topic_id: null,
  question_type: null,
  custom_group: '',
  search: ''
});

const pagination = ref({
  page: 1,
  rowsPerPage: 50,
  rowsNumber: 0
});

const difficultyOptions = ['easy', 'medium', 'hard'];
const bloomLevels = ['remember', 'understand', 'apply', 'analyze', 'evaluate', 'create'];
const questionTypes = [
  { value: 'mcq', label: 'Multiple Choice' },
  { value: 'true_false', label: 'True/False' },
  { value: 'short', label: 'Short Answer' },
  { value: 'long', label: 'Long Answer' }
];

const columns = [
  { name: 'id', label: 'ID', field: 'id', align: 'left', sortable: true },
  { name: 'question_text', label: 'Question', field: 'question_text', align: 'left' },
  { name: 'custom_group', label: 'Group', field: 'custom_group', align: 'left' },
  { name: 'question_type', label: 'Type', field: 'question_type', align: 'center' },
  { name: 'difficulty', label: 'Difficulty', field: 'difficulty', align: 'center' },
  { name: 'bloom_level', label: 'Bloom', field: 'bloom_level', align: 'center' },
  { name: 'marks', label: 'Marks', field: 'marks', align: 'center' },
  { name: 'preview', label: 'Preview', align: 'center' }
];

const totalMarks = computed(() => {
  return selection.value.reduce((sum, q) => sum + (q.marks || 0), 0);
});

const bloomDistribution = computed(() => {
  const dist = {
    remember: 0,
    understand: 0,
    apply: 0,
    analyze: 0,
    evaluate: 0,
    create: 0
  };
  
  selection.value.forEach(q => {
    if (q.bloom_level && dist.hasOwnProperty(q.bloom_level)) {
      dist[q.bloom_level]++;
    }
  });
  
  return dist;
});

watch(() => props.modelValue, (newVal) => {
  if (newVal) {
    // Reset filters and pagination when opening
    pagination.value.page = 1;
    loadQuestions();
    loadTopics();
    selection.value = [...props.selectedQuestions];
  }
});

watch(() => props.selectedQuestions, (newVal) => {
  selection.value = [...newVal];
});

watch(() => props.subjectId, () => {
  loadQuestions();
  loadTopics();
});

const loadQuestions = async () => {
  loading.value = true;
  try {
    const params = {
      ...filters,
      page: pagination.value.page
    };
    
    if (props.subjectId) {
      params.subject_id = props.subjectId;
    }
    
    // Use the direct API route for reliable access
    const response = await axios.get('/api/qu-questions', { params });
    
    // Handle standard pagination structure from Laravel Resource
    if (response.data.data && Array.isArray(response.data.data)) {
        questions.value = response.data.data;
        pagination.value.rowsNumber = response.data.meta?.total || response.data.total || 0;
        pagination.value.rowsPerPage = response.data.meta?.per_page || response.data.per_page || 50;
    } else if (response.data.success && response.data.data) {
        // Handle custom success wrapper if that's what backend returns
        const data = response.data.data;
        questions.value = data.data || [];
        pagination.value.rowsNumber = data.total || 0;
    } else {
        questions.value = [];
    }
  } catch (error) {
    console.error('Failed to load questions:', error);
    $q.notify({
      type: 'negative',
      message: 'Failed to load questions',
      icon: 'error'
    });
  } finally {
    loading.value = false;
  }
};

const loadTopics = async () => {
  try {
    const params = {};
    if (props.subjectId) {
      params.subject_id = props.subjectId;
    }
    
    // Fetch topics from API
    const response = await axios.get('/api/topics', { params });
    
    if (response.data.success) {
      topics.value = response.data.data;
    } else if (Array.isArray(response.data)) {
       topics.value = response.data;
    } else if (response.data.data && Array.isArray(response.data.data)) {
       topics.value = response.data.data;
    } else {
       topics.value = [];
    }
  } catch (error) {
    console.error('Failed to load topics:', error);
    // Silent fail for topics, don't block user
    topics.value = [];
  }
};

const onTableRequest = (props) => {
  pagination.value.page = props.pagination.page;
  loadQuestions();
};

const selectAll = () => {
  selection.value = [...questions.value];
};

const selectNone = () => {
  selection.value = [];
};

const selectInverse = () => {
  const currentIds = new Set(selection.value.map(q => q.id));
  selection.value = questions.value.filter(q => !currentIds.has(q.id));
};

const previewQuestion = (question) => {
  previewedQuestion.value = question;
  previewDialog.value = true;
};

const confirmSelection = () => {
  emit('update:selectedQuestions', selection.value);
  emit('update:modelValue', false);
};

function truncateText(text, length) {
  return text && text.length > length ? text.substring(0, length) + '...' : text;
}

function capitalizeFirst(str) {
  return str ? str.charAt(0).toUpperCase() + str.slice(1) : '';
}

function difficultyColor(difficulty) {
  const colors = {
    easy: 'green',
    medium: 'orange',
    hard: 'red'
  };
  return colors[difficulty] || 'grey';
}

function getBloomIcon(level) {
  const icons = {
    remember: 'psychology',
    understand: 'lightbulb',
    apply: 'build',
    analyze: 'analytics',
    evaluate: 'fact_check',
    create: 'auto_awesome'
  };
  return icons[level] || 'help';
}
</script>
