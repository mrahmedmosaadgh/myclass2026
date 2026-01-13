<template>
  <div class="q-pa-md">
    <q-card>
      <q-card-section>
        <div class="row items-center">
          <div class="text-h5">Available Exams</div>
          <q-space />
          <q-select
            v-model="localFilters.subject_id"
            :options="subjects"
            option-value="id"
            option-label="name"
            label="Filter by Subject"
            clearable
            emit-value
            map-options
            @update:model-value="applyFilters"
            style="min-width: 200px"
          />
        </div>
      </q-card-section>

      <q-card-section v-if="exams.length === 0">
        <q-banner class="bg-grey-2">
          <template v-slot:avatar>
            <q-icon name="info" color="primary" />
          </template>
          No exams available at this time.
        </q-banner>
      </q-card-section>

      <q-card-section v-else class="q-gutter-md">
        <q-card
          v-for="exam in exams"
          :key="exam.id"
          bordered
          class="exam-card"
        >
          <q-card-section>
            <div class="row items-start q-gutter-sm">
              <div class="col">
                <div class="text-h6">{{ exam.title }}</div>
                <div v-if="exam.description" class="text-caption text-grey-7 q-mt-xs">
                  {{ exam.description }}
                </div>
              </div>
              <q-chip :color="statusColor(exam.status)" text-color="white">
                {{ capitalizeFirst(exam.status) }}
              </q-chip>
            </div>
          </q-card-section>

          <q-separator />

          <q-card-section>
            <div class="row q-gutter-md">
              <div class="col-12 col-sm-6 col-md-3">
                <div class="text-caption text-grey-7">Subject</div>
                <q-chip dense color="primary" text-color="white">
                  {{ exam.subject?.name }}
                </q-chip>
              </div>

              <div class="col-12 col-sm-6 col-md-3">
                <div class="text-caption text-grey-7">Type</div>
                <q-chip dense :color="examTypeColor(exam.exam_type)" text-color="white">
                  {{ capitalizeFirst(exam.exam_type) }}
                </q-chip>
              </div>

              <div class="col-12 col-sm-6 col-md-2">
                <div class="text-caption text-grey-7">Duration</div>
                <div class="text-body2">{{ exam.duration_minutes }} min</div>
              </div>

              <div class="col-12 col-sm-6 col-md-2">
                <div class="text-caption text-grey-7">Total Marks</div>
                <div class="text-body2">{{ exam.total_marks }}</div>
              </div>

              <div class="col-12 col-sm-6 col-md-2" v-if="exam.passing_score">
                <div class="text-caption text-grey-7">Passing Score</div>
                <div class="text-body2">{{ exam.passing_score }}</div>
              </div>
            </div>
          </q-card-section>

          <q-card-section v-if="exam.start_date || exam.end_date">
            <div class="row q-gutter-md">
              <div v-if="exam.start_date" class="col">
                <div class="text-caption text-grey-7">Available From</div>
                <div class="text-body2">{{ formatDate(exam.start_date) }}</div>
              </div>
              <div v-if="exam.end_date" class="col">
                <div class="text-caption text-grey-7">Available Until</div>
                <div class="text-body2">{{ formatDate(exam.end_date) }}</div>
              </div>
            </div>
          </q-card-section>

          <q-separator />

          <q-card-section>
            <div class="row items-center q-gutter-md">
              <div class="col">
                <div class="text-caption text-grey-7">Attempts</div>
                <div class="text-body2">
                  {{ exam.attempt_count }} / {{ exam.max_attempts || '∞' }}
                  <span v-if="exam.remaining_attempts !== null" class="text-grey-7">
                    ({{ exam.remaining_attempts }} remaining)
                  </span>
                </div>
                <div v-if="exam.best_score !== null" class="text-caption text-positive q-mt-xs">
                  Best Score: {{ exam.best_score }}
                </div>
              </div>

              <div class="col-auto">
                <!-- Resume In-Progress Exam -->
                <q-btn
                  v-if="exam.has_in_progress"
                  color="orange"
                  label="Resume Exam"
                  icon="play_arrow"
                  @click="resumeExam(exam)"
                />

                <!-- Start New Exam -->
                <q-btn
                  v-else-if="canStartExam(exam)"
                  color="primary"
                  label="Start Exam"
                  icon="play_arrow"
                  @click="confirmStartExam(exam)"
                />

                <!-- View Results -->
                <q-btn
                  v-if="exam.completed_attempts_count > 0"
                  flat
                  color="secondary"
                  label="View Results"
                  icon="assessment"
                  @click="viewResults(exam)"
                  class="q-ml-sm"
                />

                <!-- Disabled State -->
                <q-btn
                  v-if="!canStartExam(exam) && !exam.has_in_progress"
                  disable
                  color="grey"
                  :label="getDisabledReason(exam)"
                />
              </div>
            </div>
          </q-card-section>
        </q-card>
      </q-card-section>
    </q-card>

    <!-- Start Exam Confirmation Dialog -->
    <q-dialog v-model="startDialog">
      <q-card style="min-width: 400px">
        <q-card-section>
          <div class="text-h6">Start Exam</div>
        </q-card-section>

        <q-card-section>
          <p>You are about to start: <strong>{{ selectedExam?.title }}</strong></p>
          <p class="text-caption text-grey-7">
            Duration: {{ selectedExam?.duration_minutes }} minutes<br>
            Total Marks: {{ selectedExam?.total_marks }}<br>
            <span v-if="selectedExam?.max_attempts">
              Remaining Attempts: {{ selectedExam?.remaining_attempts }}
            </span>
          </p>
          <q-banner class="bg-orange-1 text-orange-9 q-mt-md">
            <template v-slot:avatar>
              <q-icon name="warning" color="orange" />
            </template>
            Once started, the timer will begin immediately. Make sure you have enough time to complete the exam.
          </q-banner>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Cancel" color="primary" v-close-popup />
          <q-btn label="Start Exam" color="primary" @click="startExam" />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { useQuasar } from 'quasar';

const $q = useQuasar();

const props = defineProps({
  exams: Array,
  subjects: Array,
  filters: Object
});

const localFilters = reactive({
  subject_id: props.filters?.subject_id || null
});

const startDialog = ref(false);
const selectedExam = ref(null);

const applyFilters = () => {
  router.get(route('qu-student.exams.index'), localFilters, {
    preserveState: true,
    preserveScroll: true
  });
};

const canStartExam = (exam) => {
  return exam.is_available && 
         (exam.remaining_attempts === null || exam.remaining_attempts > 0) &&
         !exam.has_in_progress;
};

const getDisabledReason = (exam) => {
  if (!exam.is_available) {
    if (exam.status === 'upcoming') return 'Not Yet Available';
    if (exam.status === 'ended') return 'Exam Ended';
    return 'Not Available';
  }
  if (exam.remaining_attempts !== null && exam.remaining_attempts <= 0) {
    return 'No Attempts Remaining';
  }
  return 'Not Available';
};

const confirmStartExam = (exam) => {
  selectedExam.value = exam;
  startDialog.value = true;
};

const startExam = () => {
  router.post(route('qu-student.exams.start', selectedExam.value.id), {}, {
    onSuccess: () => {
      startDialog.value = false;
    },
    onError: (errors) => {
      $q.notify({
        type: 'negative',
        message: errors.message || 'Failed to start exam',
        icon: 'error'
      });
    }
  });
};

const resumeExam = (exam) => {
  router.visit(route('qu-student.exams.take', {
    quExam: exam.id,
    quAttempt: exam.in_progress_attempt_id
  }));
};

const viewResults = (exam) => {
  // Navigate to a results list page (to be created) or directly to last attempt
  $q.notify({
    type: 'info',
    message: 'Results viewing coming soon',
    icon: 'info'
  });
};

const capitalizeFirst = (str) => {
  return str ? str.charAt(0).toUpperCase() + str.slice(1) : '';
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

const formatDate = (dateString) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  return date.toLocaleString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};
</script>

<style scoped>
.exam-card {
  transition: box-shadow 0.3s ease;
}

.exam-card:hover {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}
</style>
