<template>
  <Head title="Available Exams" />
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
            <div class="row items-start q-gutter-md">
              <div class="col">
                <div class="text-subtitle2 q-mb-sm">Attempt History</div>
                
                <!-- Attempts Count -->
                <div class="q-mb-sm">
                  <q-chip dense color="primary" text-color="white" icon="assignment">
                    {{ exam.attempt_stats?.completed_attempts || 0 }} / {{ exam.max_attempts || '∞' }} Completed
                  </q-chip>
                  <q-chip 
                    v-if="exam.remaining_attempts !== null && exam.remaining_attempts > 0" 
                    dense 
                    color="green" 
                    text-color="white"
                    icon="check_circle"
                  >
                    {{ exam.remaining_attempts }} Remaining
                  </q-chip>
                  <q-chip 
                    v-else-if="exam.remaining_attempts === 0" 
                    dense 
                    color="orange" 
                    text-color="white"
                    icon="block"
                  >
                    No Attempts Left
                  </q-chip>
                </div>

                <!-- Performance Stats (Visual Redesign) -->
                <div v-if="exam.attempt_stats?.completed_attempts > 0" class="q-mt-md">
                   <div class="row q-col-gutter-xs">
                    <!-- Attempts -->
                    <div class="col-3">
                      <q-card flat class="bg-purple-1 text-center q-pa-xs rounded-borders h-full">
                         <div class="text-caption text-purple-9 text-weight-bold" style="font-size: 0.7rem">Runs</div>
                         <div class="text-subtitle1 text-purple-10 text-weight-bolder q-my-none" style="line-height:1.1">
                            {{ exam.attempt_stats.completed_attempts }}
                            <span v-if="exam.max_attempts" class="text-caption text-purple-5">/{{ exam.max_attempts }}</span>
                         </div>
                      </q-card>
                    </div>

                    <!-- Best Score -->
                    <div class="col-3">
                      <q-card flat class="bg-amber-1 text-center q-pa-xs rounded-borders h-full">
                         <div class="text-caption text-amber-10 text-weight-bold row justify-center items-center" style="font-size: 0.7rem">
                            <q-icon name="emoji_events" size="12px" class="q-mr-xs"/> Best
                         </div>
                         <div class="text-subtitle1 text-amber-10 text-weight-bolder q-my-none" style="line-height:1.1">
                            {{ exam.attempt_stats.best_score }} <span class="text-caption" style="font-size: 0.7rem">/{{ exam.total_marks }}</span>
                         </div>
                         <div class="text-amber-9 text-weight-bold" style="font-size: 9px">
                           {{ Math.round((exam.attempt_stats.best_score / exam.total_marks) * 100) }}%
                         </div>
                      </q-card>
                    </div>

                    <!-- Average -->
                    <div class="col-3">
                      <q-card flat class="bg-blue-1 text-center q-pa-xs rounded-borders h-full">
                         <div class="text-caption text-blue-9 text-weight-bold" style="font-size: 0.7rem">Avg</div>
                         <div class="text-subtitle1 text-blue-10 text-weight-bolder q-my-none" style="line-height:1.1">
                            {{ Math.round(exam.attempt_stats.average_score * 10) / 10 }} <span class="text-caption" style="font-size: 0.7rem">/{{ exam.total_marks }}</span>
                         </div>
                         <div class="text-blue-8 text-weight-bold" style="font-size: 9px">
                           {{ Math.round((exam.attempt_stats.average_score / exam.total_marks) * 100) }}%
                         </div>
                      </q-card>
                    </div>

                    <!-- Last -->
                    <div class="col-3">
                      <q-card flat class="bg-teal-1 text-center q-pa-xs rounded-borders h-full">
                         <div class="text-caption text-teal-9 text-weight-bold" style="font-size: 0.7rem">Last</div>
                         <div class="text-subtitle1 text-teal-10 text-weight-bold q-my-none" style="line-height:1.1">
                            {{ exam.attempt_stats.last_score }} <span class="text-caption" style="font-size: 0.7rem">/{{ exam.total_marks }}</span>
                         </div>
                         <div class="text-teal-8" style="font-size: 8px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                           {{ formatDate(exam.attempt_stats.last_attempt_date).split(',')[0] }}
                         </div>
                      </q-card>
                    </div>
                   </div>
                </div>

                <!-- No attempts yet message -->
                <div v-else class="text-caption text-grey-6">
                  <q-icon name="info" size="xs" class="q-mr-xs" />
                  No completed attempts yet
                </div>
              </div>

              <div class="col-auto">
                <!-- Resume In-Progress Exam -->
                <q-btn
                  v-if="exam.attempt_stats?.has_in_progress"
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
                  v-if="exam.attempt_stats?.completed_attempts > 0"
                  flat
                  color="info"
                  label="View Results"
                  icon="assessment"
                  @click="viewResults(exam)"
                />

                <!-- Print Exam (for practice) -->
                  <q-btn
                    v-if="exam.settings?.allow_print"
                    flat
                    color="secondary"
                    label="Print"
                    icon="print"
                    @click="printExam(exam)"
                  >
                    <q-tooltip>Open print view in new window</q-tooltip>
                  </q-btn>

                  <q-btn
                    v-if="exam.settings?.allow_print"
                    flat
                    color="indigo"
                    label="Preview"
                    icon="visibility"
                    @click="openPrintDialog(exam)"
                  >
                     <q-tooltip>Preview and print in dialog</q-tooltip>
                  </q-btn>

                <!-- Disabled State -->
                <q-btn
                  v-if="!canStartExam(exam) && !exam.attempt_stats?.has_in_progress"
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
         !exam.attempt_stats?.has_in_progress;
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
    quAttempt: exam.attempt_stats.in_progress_attempt_id
  }));
};

const viewResults = (exam) => {
  const lastAttemptId = exam.attempt_stats?.last_attempt_id;
  if (lastAttemptId) {
    router.visit(route('qu-student.exams.results', {
      quExam: exam.id,
      quAttempt: lastAttemptId
    }));
  }
};

const printExam = (exam) => {
  // Open print view in new window
  window.open(
    route('qu-student.exams.print', exam.id),
    '_blank',
    'width=800,height=600'
  );
};

// Print Dialog Logic
const showPrintDialogState = ref(false);
const selectedExamForPrint = ref(null);

const openPrintDialog = (exam) => {
  selectedExamForPrint.value = exam;
  showPrintDialogState.value = true;
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
