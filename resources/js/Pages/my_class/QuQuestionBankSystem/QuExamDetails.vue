<template>
  <Head :title="exam.title" />
  <div class="q-pa-md bg-grey-1"  >
    <div class="row justify-center">
      <div class=" ">
        <q-card class="rounded-xl shadow-2">
          <!-- Header -->
          <div class="bg-primary text-white q-pa-lg rounded-borders-top">
            <div class="text-h4 text-weight-bold q-mb-sm">{{ exam.title }}</div>
            <div class="text-subtitle1 opacity-80">
              {{ exam.subject?.name }} • {{ capitalizeFirst(exam.exam_type) }}
            </div>
            
            <div class="row q-mt-md q-gutter-md">
              <q-chip color="white" text-color="primary" icon="timer">
                {{ exam.duration_minutes }} Minutes
              </q-chip>
              <q-chip color="white" text-color="primary" icon="grade">
                {{ exam.total_marks }} Marks
              </q-chip>
              <q-chip color="white" text-color="primary" icon="format_list_numbered">
                {{ exam.questions_count }} Questions
              </q-chip>
            </div>
          </div>

          <q-card-section class="q-pa-lg">
            <!-- Description -->
            <div v-if="exam.description" class="q-mb-xl">
              <div class="text-h6 q-mb-sm text-grey-9">Instructions</div>
              <div class="text-body1 text-grey-8" style="white-space: pre-wrap;">{{ exam.description }}</div>
            </div>

            <q-separator class="q-my-lg" />

            <!-- Attempt Status -->
            <div class="row items-center justify-between q-mb-lg">
              <div>
                <div class="text-subtitle1 text-weight-bold">Attempts</div>
                <div class="text-caption text-grey-7">
                  {{ previousAttempts.length }} / {{ exam.max_attempts || 'Unlimited' }} used
                </div>
              </div>
              
              <div v-if="remaining_attempts === null || remaining_attempts > 0">
                <q-btn
                  v-if="in_progress_attempt"
                  color="positive"
                  icon="play_arrow"
                  label="Resume Exam"
                  size="lg"
                  rounded
                  class="q-px-xl"
                  :loading="loading"
                  @click="startExam"
                />
                <q-btn
                  v-else
                  color="primary"
                  icon="play_arrow"
                  label="Start Exam"
                  size="lg"
                  rounded
                  class="q-px-xl"
                  :loading="loading"
                  @click="startExam"
                />
              </div>
              <div v-else class="text-negative text-weight-bold">
                No attempts remaining
              </div>
            </div>

            <!-- Previous Attempts History -->
            <div v-if="previousAttempts.length > 0">
              <div class="text-h6 q-mb-md text-grey-9">History</div>
              <q-list bordered separator class="rounded-borders">
                <q-item v-for="(attempt, index) in previousAttempts" :key="attempt.id">
                  <q-item-section avatar>
                    <q-avatar color="grey-2" text-color="grey-8">
                      {{ index + 1 }}
                    </q-avatar>
                  </q-item-section>
                  
                  <q-item-section>
                    <q-item-label>Attempt {{ index + 1 }}</q-item-label>
                    <q-item-label caption>
                      {{ formatDate(attempt.completed_at) }}
                    </q-item-label>
                  </q-item-section>

                  <q-item-section side>
                    <div class="text-weight-bold text-primary">
                      {{ parseFloat(attempt.score) }} / {{ parseFloat(exam.total_marks) }}
                    </div>
                  </q-item-section>
                  
                  <q-item-section side>
                    <q-btn 
                      flat 
                      round 
                      color="grey-7" 
                      icon="visibility" 
                      @click="viewResults(attempt)"
                    />
                  </q-item-section>
                </q-item>
              </q-list>
            </div>

          </q-card-section>
        </q-card>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { date } from 'quasar';

const props = defineProps({
  exam: Object,
  remaining_attempts: [Number, null], // null means unlimited
  previous_attempts: Array,
  in_progress_attempt: Object
});

const loading = ref(false);
const previousAttempts = computed(() => props.previous_attempts || []);

const startExam = () => {
  loading.value = true;
  router.post(route('qu.student.exams.start', props.exam.id), {}, {
    onError: () => {
      loading.value = false;
    }
  });
};

const viewResults = (attempt) => {
  router.visit(route('qu.student.exams.results', { 
    quExam: props.exam.id, 
    quAttempt: attempt.id 
  }));
};

const capitalizeFirst = (str) => {
  return str ? str.charAt(0).toUpperCase() + str.slice(1) : '';
};

const formatDate = (dateString) => {
  return date.formatDate(dateString, 'MMM D, YYYY h:mm A');
};
</script>

<style scoped>
.rounded-borders-top {
  border-top-left-radius: 24px;
  border-top-right-radius: 24px;
}
.opacity-80 {
  opacity: 0.8;
}
</style>
