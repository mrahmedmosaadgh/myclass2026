<template>
  <Head title="Grading Dashboard" />
  <div class="q-pa-md">
    <q-card>
      <!-- Header -->
      <q-card-section>
        <div class="row items-center">
          <div class="text-h5">Grading Dashboard</div>
          <q-space />
        </div>
      </q-card-section>

      <!-- Filters -->
      <q-card-section>
        <div class="row q-gutter-md">
          <q-select
            v-model="localFilters.exam_id"
            :options="exams"
            option-value="id"
            option-label="title"
            label="Filter by Exam"
            clearable
            emit-value
            map-options
            @update:model-value="applyFilters"
            style="min-width: 250px"
          />

          <q-select
            v-model="localFilters.grading_status"
            :options="statusOptions"
            label="Grading Status"
            clearable
            @update:model-value="applyFilters"
            style="min-width: 200px"
          />
        </div>
      </q-card-section>

      <!-- Attempts Table -->
      <q-card-section>
        <q-table
          :rows="attempts.data"
          :columns="columns"
          row-key="id"
          flat
          bordered
        >
          <template v-slot:body-cell-student="props">
            <q-td :props="props">
              <div class="text-subtitle2">{{ props.row.user.name }}</div>
              <div class="text-caption text-grey-7">{{ props.row.user.email }}</div>
            </q-td>
          </template>

          <template v-slot:body-cell-exam="props">
            <q-td :props="props">
              {{ props.row.exam.title }}
            </q-td>
          </template>

          <template v-slot:body-cell-score="props">
            <q-td :props="props">
              <q-chip
                :color="getScoreColor(props.row)"
                text-color="white"
              >
                {{ props.row.score || 0 }} / {{ props.row.exam.total_marks }}
              </q-chip>
            </q-td>
          </template>

          <template v-slot:body-cell-grading_status="props">
            <q-td :props="props">
              <q-badge
                :color="getStatusColor(props.row.grading_status)"
                :label="capitalizeFirst(props.row.grading_status)"
              />
            </q-td>
          </template>

          <template v-slot:body-cell-submitted_at="props">
            <q-td :props="props">
              {{ formatDate(props.row.completed_at) }}
            </q-td>
          </template>

          <template v-slot:body-cell-actions="props">
            <q-td :props="props">
              <q-btn
                flat
                dense
                round
                color="primary"
                icon="grading"
                @click="gradeAttempt(props.row)"
              >
                <q-tooltip>Grade Attempt</q-tooltip>
              </q-btn>
            </q-td>
          </template>
        </q-table>
      </q-card-section>

      <!-- Pagination -->
      <q-card-section v-if="attempts.last_page > 1" class="q-pt-none">
        <div class="row justify-center">
          <q-pagination
            v-model="currentPage"
            :max="attempts.last_page"
            :max-pages="7"
            direction-links
            boundary-links
            @update:model-value="changePage"
          />
        </div>
      </q-card-section>
    </q-card>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { date } from 'quasar';

const props = defineProps({
  attempts: Object,
  exams: Array,
  filters: Object
});

const localFilters = reactive({
  exam_id: props.filters?.exam_id || null,
  grading_status: props.filters?.grading_status || null
});

const currentPage = ref(props.attempts.current_page);

const statusOptions = [
  { value: 'pending', label: 'Pending' },
  { value: 'partial', label: 'Partially Graded' },
  { value: 'completed', label: 'Completed' }
];

const columns = [
  { name: 'student', label: 'Student', field: 'user', align: 'left' },
  { name: 'exam', label: 'Exam', field: 'exam', align: 'left' },
  { name: 'score', label: 'Score', field: 'score', align: 'center' },
  { name: 'grading_status', label: 'Status', field: 'grading_status', align: 'center' },
  { name: 'submitted_at', label: 'Submitted', field: 'completed_at', align: 'center' },
  { name: 'actions', label: 'Actions', align: 'center' }
];

const applyFilters = () => {
  router.get(route('qu.grading.index'), localFilters, {
    preserveState: true,
    preserveScroll: true
  });
};

const changePage = (page) => {
  router.get(route('qu.grading.index', { ...localFilters, page }), {}, {
    preserveState: true,
    preserveScroll: true
  });
};

const gradeAttempt = (attempt) => {
  router.visit(route('qu.grading.show', attempt.id));
};

const formatDate = (dateString) => {
  return date.formatDate(dateString, 'YYYY-MM-DD HH:mm');
};

const capitalizeFirst = (str) => {
  return str ? str.charAt(0).toUpperCase() + str.slice(1) : '';
};

const getStatusColor = (status) => {
  const colors = {
    pending: 'grey',
    partial: 'orange',
    completed: 'positive'
  };
  return colors[status] || 'grey';
};

const getScoreColor = (attempt) => {
  if (!attempt.exam.passing_score) return 'primary';
  
  const percentage = (attempt.score / attempt.exam.total_marks) * 100;
  if (percentage >= attempt.exam.passing_score) return 'positive';
  return 'negative';
};
</script>
