<template>
  <Head title="Grade Exam Attempt" />
  <div class="q-pa-md">
    <q-card>
      <!-- Header -->
      <q-card-section class="bg-primary text-white">
        <div class="row items-center">
          <div class="col">
            <div class="text-h5">{{ attempt.exam.title }}</div>
            <div class="text-subtitle2">Grading: {{ attempt.user.name }}</div>
          </div>
          <q-space />
          <q-btn
            flat
            dense
            round
            icon="close"
            @click="backToList"
          />
        </div>
      </q-card-section>

      <!-- Attempt Info -->
      <q-card-section>
        <div class="row q-gutter-md">
          <div class="col-12 col-sm-6 col-md-3">
            <q-card flat bordered>
              <q-card-section class="text-center">
                <div class="text-h6">{{ attempt.user.name }}</div>
                <div class="text-caption text-grey-7">Student</div>
              </q-card-section>
            </q-card>
          </div>

          <div class="col-12 col-sm-6 col-md-3">
            <q-card flat bordered>
              <q-card-section class="text-center">
                <div class="text-h6">{{ formatDate(attempt.completed_at) }}</div>
                <div class="text-caption text-grey-7">Submitted</div>
              </q-card-section>
            </q-card>
          </div>

          <div class="col-12 col-sm-6 col-md-3">
            <q-card flat bordered>
              <q-card-section class="text-center">
                <div class="text-h6">{{ currentScore }} / {{ totalMarks }}</div>
                <div class="text-caption text-grey-7">Current Score</div>
              </q-card-section>
            </q-card>
          </div>

          <div class="col-12 col-sm-6 col-md-3">
            <q-card flat bordered>
              <q-card-section class="text-center">
                <q-chip 
                  :color="gradingStatusColor" 
                  text-color="white"
                >
                  {{ gradingStatusLabel }}
                </q-chip>
                <div class="text-caption text-grey-7">Status</div>
              </q-card-section>
            </q-card>
          </div>
        </div>
      </q-card-section>

      <q-separator />

      <!-- Auto-Graded Questions (Read-only) -->
      <q-card-section v-if="autoGraded.length > 0">
        <div class="text-h6 q-mb-md">Auto-Graded Questions</div>
        <q-list separator>
          <q-item
            v-for="(item, index) in autoGraded"
            :key="'auto-' + item.question.id"
            class="q-pa-md"
          >
            <q-item-section>
              <div class="row items-start">
                <div class="col">
                  <div class="text-subtitle1 text-weight-bold q-mb-sm">
                    Question {{ index + 1 }}
                  </div>
                  <div class="text-body1 q-mb-md" v-html="renderMath(item.question.question_text)"></div>
                  
                  <!-- Student Answer -->
                  <div class="q-mb-sm">
                    <div class="text-caption text-grey-7">Student Answer:</div>
                    <q-chip 
                      :color="item.is_correct ? 'positive' : 'negative'"
                      text-color="white"
                    >
                      {{ getStudentAnswer(item) }}
                    </q-chip>
                  </div>
                </div>

                <!-- Score Badge -->
                <div class="col-auto text-center">
                  <q-chip
                    :color="item.is_correct ? 'positive' : 'negative'"
                    text-color="white"
                    size="lg"
                  >
                    <q-icon :name="item.is_correct ? 'check_circle' : 'cancel'" size="sm" class="q-mr-sm" />
                    {{ item.answer.marks_obtained }} / {{ item.question.marks }}
                  </q-chip>
                </div>
              </div>
            </q-item-section>
          </q-item>
        </q-list>
      </q-card-section>

      <q-separator v-if="autoGraded.length > 0 && manualQuestions.length > 0" />

      <!-- Manual Grading Questions -->
      <q-card-section v-if="manualQuestions.length > 0">
        <div class="text-h6 q-mb-md">Questions Requiring Manual Grading</div>
        
        <q-form @submit="saveGrades">
          <q-list separator>
            <q-item
              v-for="(item, index) in manualQuestions"
              :key="'manual-' + item.question.id"
              class="q-pa-md"
            >
              <q-item-section>
                <div class="text-subtitle1 text-weight-bold q-mb-sm">
                  Question {{ autoGraded.length + index + 1 }}
                </div>
                <div class="text-body1 q-mb-md" v-html="renderMath(item.question.question_text)"></div>

                <!-- Model Answer -->
                <div v-if="item.question.correct_answer" class="q-mb-md">
                  <div class="text-caption text-grey-7 q-mb-xs">Model Answer:</div>
                  <q-card flat bordered class="q-pa-sm bg-green-1">
                    <div v-html="renderMath(item.question.correct_answer)"></div>
                  </q-card>
                </div>

                <!-- Student Answer -->
                <div class="q-mb-md">
                  <div class="text-caption text-grey-7 q-mb-xs">Student Answer:</div>
                  <q-card flat bordered class="q-pa-sm bg-grey-1">
                    <div v-html="renderMath(item.answer.answer_text || 'No answer provided')"></div>
                  </q-card>
                </div>

                <!-- Grading Inputs -->
                <div class="row q-gutter-md">
                  <div class="col-12 col-sm-3">
                    <q-input
                      v-model.number="grades[item.answer.id].marks_obtained"
                      type="number"
                      label="Marks Awarded"
                      :hint="`Out of ${item.question.marks}`"
                      :rules="[
                        val => val !== null && val !== '' || 'Required',
                        val => val >= 0 || 'Must be >= 0',
                        val => val <= item.question.marks || `Max ${item.question.marks}`
                      ]"
                      outlined
                      dense
                    />
                  </div>

                  <div class="col">
                    <q-input
                      v-model="grades[item.answer.id].feedback"
                      type="textarea"
                      label="Feedback (Optional)"
                      hint="Provide feedback to help the student improve"
                      outlined
                      dense
                      rows="3"
                    />
                  </div>
                </div>
              </q-item-section>
            </q-item>
          </q-list>

          <!-- Actions -->
          <q-card-actions align="right" class="q-pa-md">
            <q-btn
              flat
              label="Cancel"
              color="grey"
              @click="backToList"
            />
            <q-btn
              type="submit"
              label="Save Grades"
              color="primary"
              icon="save"
              :loading="saving"
            />
          </q-card-actions>
        </q-form>
      </q-card-section>

      <!-- No Manual Grading Needed -->
      <q-card-section v-else-if="autoGraded.length > 0">
        <q-banner class="bg-positive text-white">
          <template v-slot:avatar>
            <q-icon name="check_circle" />
          </template>
          All questions have been auto-graded. No manual grading required.
        </q-banner>

        <q-card-actions align="center" class="q-pa-md">
          <q-btn
            label="Back to Grading List"
            color="primary"
            icon="arrow_back"
            @click="backToList"
          />
        </q-card-actions>
      </q-card-section>
    </q-card>
  </div>
</template>

<script setup>
import { ref, computed, reactive } from 'vue';
import { router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { renderMath } from '@/Utils/katex';
import { date } from 'quasar';

const props = defineProps({
  attempt: Object,
  autoGraded: Array,
  manualQuestions: Array,
  totalMarks: Number
});

const saving = ref(false);

// Initialize grades object for manual questions
const grades = reactive({});
props.manualQuestions.forEach(item => {
  grades[item.answer.id] = {
    answer_id: item.answer.id,
    marks_obtained: item.answer.marks_obtained ?? 0,
    feedback: item.answer.feedback ?? ''
  };
});

const currentScore = computed(() => {
  let total = 0;
  
  // Add auto-graded scores
  props.autoGraded.forEach(item => {
    total += item.answer.marks_obtained || 0;
  });
  
  // Add manual grades
  Object.values(grades).forEach(grade => {
    total += parseFloat(grade.marks_obtained) || 0;
  });
  
  return total.toFixed(2);
});

const gradingStatusColor = computed(() => {
  const status = props.attempt.grading_status;
  if (status === 'completed') return 'positive';
  if (status === 'partial') return 'orange';
  return 'grey';
});

const gradingStatusLabel = computed(() => {
  const status = props.attempt.grading_status;
  return status.charAt(0).toUpperCase() + status.slice(1);
});

const formatDate = (dateString) => {
  return date.formatDate(dateString, 'YYYY-MM-DD HH:mm');
};

const getStudentAnswer = (item) => {
  if (item.question.question_type === 'mcq') {
    return item.answer.selected_options || 'No answer';
  } else if (item.question.question_type === 'true_false') {
    const answer = item.answer.selected_options?.[0];
    return answer ? answer.charAt(0).toUpperCase() + answer.slice(1) : 'No answer';
  }
  return 'No answer';
};

const saveGrades = () => {
  saving.value = true;
  
  router.post(route('qu.grading.save', props.attempt.id), {
    grades: Object.values(grades)
  }, {
    onSuccess: () => {
      saving.value = false;
    },
    onError: () => {
      saving.value = false;
    }
  });
};

const backToList = () => {
  router.visit(route('qu.grading.index'));
};
</script>

<style scoped>
.q-card {
  max-width: 1200px;
  margin: 0 auto;
}
</style>
