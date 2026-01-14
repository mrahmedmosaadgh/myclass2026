<template>
  <Head :title="exam.title ? exam.title + ' - Results' : 'Exam Results'" />
  <div class="q-pa-md">
    <q-card>
      <!-- Header -->
      <q-card-section class="bg-primary text-white">
        <div class="text-h5">{{ exam.title }}</div>
        <div class="text-subtitle2">{{ exam.subject?.name }}</div>
      </q-card-section>

      <!-- Score Display -->
      <q-card-section class="text-center q-py-xl" v-if="show_results">
        <div class="text-h2 text-weight-bold" :class="scoreColor">
          {{ attempt.score }} / {{ exam.total_marks }}
        </div>
        <div class="text-h6 text-grey-7 q-mt-sm">
          {{ statistics.percentage }}%
        </div>
        
        <q-chip
          v-if="statistics.passed !== null"
          :color="statistics.passed ? 'positive' : 'negative'"
          text-color="white"
          size="lg"
          class="q-mt-md"
        >
          <q-icon :name="statistics.passed ? 'check_circle' : 'cancel'" size="sm" class="q-mr-sm" />
          {{ statistics.passed ? 'PASSED' : 'FAILED' }}
        </q-chip>
      </q-card-section>

      <!-- Results Not Available -->
      <q-card-section v-else class="text-center q-py-xl">
        <q-icon name="lock" size="64px" color="grey-5" />
        <div class="text-h6 text-grey-7 q-mt-md">
          Results Not Yet Available
        </div>
        <div class="text-caption text-grey-6 q-mt-sm">
          Your teacher will publish the results soon.
        </div>
      </q-card-section>

      <q-separator />

      <!-- Statistics -->
      <q-card-section v-if="show_results">
        <div class="row q-gutter-md">
          <div class="col-12 col-sm-6 col-md-3">
            <q-card flat bordered>
              <q-card-section class="text-center">
                <div class="text-h6">{{ statistics.answered_questions }}</div>
                <div class="text-caption text-grey-7">Questions Answered</div>
              </q-card-section>
            </q-card>
          </div>

          <div class="col-12 col-sm-6 col-md-3">
            <q-card flat bordered>
              <q-card-section class="text-center">
                <div class="text-h6">{{ statistics.total_questions }}</div>
                <div class="text-caption text-grey-7">Total Questions</div>
              </q-card-section>
            </q-card>
          </div>

          <div class="col-12 col-sm-6 col-md-3">
            <q-card flat bordered>
              <q-card-section class="text-center">
                <div class="text-h6">{{ Math.round(attempt.time_taken_minutes) }} min</div>
                <div class="text-caption text-grey-7">Time Taken</div>
              </q-card-section>
            </q-card>
          </div>

          <div class="col-12 col-sm-6 col-md-3" v-if="exam.passing_score">
            <q-card flat bordered>
              <q-card-section class="text-center">
                <div class="text-h6">{{ exam.passing_score }}</div>
                <div class="text-caption text-grey-7">Passing Score</div>
              </q-card-section>
            </q-card>
          </div>
        </div>
      </q-card-section>

      <q-separator v-if="show_results && questions_with_answers.length > 0" />

      <!-- Question Review -->
      <q-card-section v-if="show_results && questions_with_answers.length > 0">
        <div class="text-h6 q-mb-md">Question Review</div>
        
        <q-list separator>
          <q-item
            v-for="(question, index) in questions_with_answers"
            :key="question.id"
            class="q-pa-md"
          >
            <q-item-section>
              <div class="row items-start">
                <div class="col">
                  <!-- Question Number and Text -->
                  <div class="text-subtitle1 text-weight-bold q-mb-sm">
                    Question {{ index + 1 }}
                  </div>
                  <div class="text-body1 q-mb-md" v-html="renderMath(question.question_text)"></div>

                  <!-- MCQ/True-False Options -->
                  <div v-if="question.question_type === 'mcq' || question.question_type === 'true_false'" class="q-mb-md">
                    <div v-if="question.question_type === 'mcq'">
                      <div
                        v-for="(text, key) in question.options"
                        :key="key"
                        class="q-py-xs"
                        :class="getOptionClass(question, key)"
                      >
                        <q-icon
                          v-if="show_correct_answers && (Array.isArray(question.correct_answer) ? question.correct_answer[0] === key : question.correct_answer === key)"
                          name="check_circle"
                          color="positive"
                          size="sm"
                          class="q-mr-sm"
                        />
                        <q-icon
                          v-else-if="question.student_answer?.selected_options === key && (Array.isArray(question.correct_answer) ? question.correct_answer[0] !== key : question.correct_answer !== key)"
                          name="cancel"
                          color="negative"
                          size="sm"
                          class="q-mr-sm"
                        />
                        <span v-html="`${key}. ${renderMath(text)}`"></span>
                      </div>
                    </div>

                    <div v-else>
                      <div
                        v-for="option in ['true', 'false']"
                        :key="option"
                        class="q-py-xs"
                        :class="getTrueFalseOptionClass(question, option)"
                      >
                        <q-icon
                          v-if="show_correct_answers && question.correct_answer === option"
                          name="check_circle"
                          color="positive"
                          size="sm"
                          class="q-mr-sm"
                        />
                        <q-icon
                          v-else-if="question.student_answer?.selected_options?.[0] === option && question.correct_answer !== option"
                          name="cancel"
                          color="negative"
                          size="sm"
                          class="q-mr-sm"
                        />
                        {{ capitalizeFirst(option) }}
                      </div>
                    </div>
                  </div>

                  <!-- Short/Long Answer -->
                  <div v-else-if="question.question_type === 'short' || question.question_type === 'long'">
                    <div class="text-caption text-grey-7 q-mb-xs">Your Answer:</div>
                    <q-card flat bordered class="q-pa-sm bg-grey-1">
                      <div v-html="renderMath(question.student_answer?.answer_text || 'No answer provided')"></div>
                    </q-card>
                    
                    <div v-if="show_correct_answers && question.correct_answer" class="q-mt-sm">
                      <div class="text-caption text-grey-7 q-mb-xs">Model Answer:</div>
                      <q-card flat bordered class="q-pa-sm bg-green-1">
                        <div v-html="renderMath(question.correct_answer)"></div>
                      </q-card>
                    </div>
                  </div>
                </div>

                <!-- Score Badge -->
                <div class="col-auto text-center">
                  <q-chip
                    :color="getScoreColor(question)"
                    text-color="white"
                    size="lg"
                  >
                    {{ question.student_answer?.marks_obtained ?? 0 }} / {{ question.marks }}
                  </q-chip>
                </div>
              </div>
            </q-item-section>
          </q-item>
        </q-list>
      </q-card-section>

      <q-separator />

      <!-- Actions -->
      <q-card-actions align="center" class="q-pa-md">
        <q-btn
          color="primary"
          label="Back to Exams"
          icon="arrow_back"
          @click="backToExams"
        />
      </q-card-actions>
    </q-card>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { renderMath } from '@/Utils/katex';

const props = defineProps({
  exam: Object,
  attempt: Object,
  statistics: Object,
  show_results: Boolean,
  show_correct_answers: Boolean,
  questions_with_answers: Array
});

const scoreColor = computed(() => {
  if (!props.statistics.passed) return 'text-grey-7';
  return props.statistics.passed ? 'text-positive' : 'text-negative';
});

const getOptionClass = (question, optionKey) => {
  if (!props.show_correct_answers) return '';
  
  const correctAnswer = Array.isArray(question.correct_answer) ? question.correct_answer[0] : question.correct_answer;
  const isCorrect = correctAnswer === optionKey;
  const isSelected = question.student_answer?.selected_options === optionKey;
  
  if (isCorrect) return 'text-positive text-weight-bold';
  if (isSelected && !isCorrect) return 'text-negative';
  return '';
};

const getTrueFalseOptionClass = (question, option) => {
  if (!props.show_correct_answers) return '';
  
  const isCorrect = question.correct_answer === option;
  const isSelected = question.student_answer?.selected_options?.[0] === option;
  
  if (isCorrect) return 'text-positive text-weight-bold';
  if (isSelected && !isCorrect) return 'text-negative';
  return '';
};

const getScoreColor = (question) => {
  const obtained = question.student_answer?.marks_obtained ?? 0;
  const total = question.marks;
  
  if (obtained === total) return 'positive';
  if (obtained === 0) return 'negative';
  return 'orange';
};

const capitalizeFirst = (str) => {
  return str ? str.charAt(0).toUpperCase() + str.slice(1) : '';
};

const backToExams = () => {
  router.visit(route('qu-student.exams.index'));
};
</script>

<style scoped>
.score-display {
  font-size: 4rem;
  font-weight: bold;
}
</style>
