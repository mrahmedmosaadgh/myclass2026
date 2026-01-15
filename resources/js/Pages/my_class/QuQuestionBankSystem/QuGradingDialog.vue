<template>
  <q-dialog v-model="isOpen" maximized transition-show="slide-up" transition-hide="slide-down">
    <q-card class="column no-wrap">
      <!-- Header -->
      <q-bar class="bg-primary text-white">
        <q-icon name="edit_note" />
        <div class="text-subtitle1">
          Grading: {{ attempt?.user?.name }} - {{ attempt?.exam?.title }}
        </div>
        <q-space />
        <q-btn dense flat icon="close" v-close-popup>
          <q-tooltip>Close</q-tooltip>
        </q-btn>
      </q-bar>
      
      <div v-if="loading" class="row justify-center items-center full-height q-pa-xl">
        <q-spinner color="primary" size="3em" />
        <div class="q-ml-sm">Loading grading data...</div>
      </div>

      <q-scroll-area v-else class="col">
        <div class="q-pa-md">
          <!-- Attempt Info -->
          <div class="row q-gutter-md q-mb-md">
            <div class="col-12 col-sm-6 col-md-3">
              <q-card flat bordered>
                <q-card-section class="text-center q-pa-sm">
                  <div class="text-subtitle1">{{ attempt?.user?.name }}</div>
                  <div class="text-caption text-grey-7">Student</div>
                </q-card-section>
              </q-card>
            </div>

            <div class="col-12 col-sm-6 col-md-3">
              <q-card flat bordered>
                <q-card-section class="text-center q-pa-sm">
                  <div class="text-subtitle1">{{ formatDate(attempt?.completed_at) }}</div>
                  <div class="text-caption text-grey-7">Submitted</div>
                </q-card-section>
              </q-card>
            </div>

            <div class="col-12 col-sm-6 col-md-3">
              <q-card flat bordered>
                <q-card-section class="text-center q-pa-sm">
                  <div class="text-subtitle1">{{ currentScore }} / {{ totalMarks }}</div>
                  <div class="text-caption text-grey-7">Current Score</div>
                </q-card-section>
              </q-card>
            </div>

            <div class="col-12 col-sm-6 col-md-3">
              <q-card flat bordered>
                <q-card-section class="text-center q-pa-sm">
                  <q-chip 
                    :color="gradingStatusColor" 
                    text-color="white"
                    dense
                  >
                    {{ gradingStatusLabel }}
                  </q-chip>
                  <div class="text-caption text-grey-7">Status</div>
                </q-card-section>
              </q-card>
            </div>
          </div>

          <q-separator class="q-mb-md" />

          <!-- Auto-Graded Questions (Read-only) -->
          <q-card v-if="autoGraded.length > 0" flat bordered class="q-mb-md">
            <q-card-section>
              <div class="text-h6">Auto-Graded Questions</div>
            </q-card-section>
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
                          dense
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
                      >
                        <q-icon :name="item.is_correct ? 'check_circle' : 'cancel'" size="sm" class="q-mr-sm" />
                        {{ item.answer.marks_obtained }} / {{ item.question.marks }}
                      </q-chip>
                    </div>
                  </div>
                </q-item-section>
              </q-item>
            </q-list>
          </q-card>

          <!-- Manual Grading Questions -->
          <q-card v-if="manualQuestions.length > 0" flat bordered>
            <q-card-section>
              <div class="text-h6">Questions Requiring Manual Grading</div>
            </q-card-section>
            
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
                  v-close-popup
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
          </q-card>

          <!-- No Manual Grading Needed -->
          <q-banner v-else-if="autoGraded.length > 0" class="bg-positive text-white q-mt-md">
            <template v-slot:avatar>
              <q-icon name="check_circle" />
            </template>
            All questions have been auto-graded. No manual grading required.
          </q-banner>
        </div>
      </q-scroll-area>
    </q-card>
  </q-dialog>
</template>

<script setup>
import { ref, computed, reactive, watch } from 'vue';
import { route } from 'ziggy-js';
import { renderMath } from '@/Utils/katex';
import { date, useQuasar } from 'quasar';

const props = defineProps({
  modelValue: Boolean, // For v-model:open or similar
  attemptId: Number
});

const emit = defineEmits(['update:modelValue', 'graded']);
const $q = useQuasar();

const isOpen = computed({
  get: () => props.modelValue,
  set: (val) => emit('update:modelValue', val)
});

const loading = ref(false);
const saving = ref(false);
const attempt = ref(null);
const autoGraded = ref([]);
const manualQuestions = ref([]);
const totalMarks = ref(0);
const grades = reactive({});

// Watch for dialog opening to fetch data
watch(() => props.attemptId, (newId) => {
  if (newId && props.modelValue) {
    fetchData(newId);
  }
});

watch(() => props.modelValue, (isOpen) => {
  if (isOpen && props.attemptId) {
    fetchData(props.attemptId);
  }
});

const fetchData = async (id) => {
  loading.value = true;
  try {
    const response = await window.axios.get(route('qu-exams.grading-data', id));
    attempt.value = response.data.attempt;
    autoGraded.value = response.data.autoGraded;
    manualQuestions.value = response.data.manualQuestions;
    totalMarks.value = response.data.totalMarks;

    // Reset grades
    Object.keys(grades).forEach(key => delete grades[key]);
    
    // Initialize grades
    manualQuestions.value.forEach(item => {
      grades[item.answer.id] = {
        answer_id: item.answer.id,
        marks_obtained: item.answer.marks_obtained ?? 0,
        feedback: item.answer.feedback ?? ''
      };
    });
    
  } catch (error) {
    console.error('Failed to load grading data', error);
    $q.notify({
      type: 'negative',
      message: 'Failed to load grading data'
    });
  } finally {
    loading.value = false;
  }
};

const currentScore = computed(() => {
  let total = 0;
  
  if (autoGraded.value) {
    autoGraded.value.forEach(item => {
      total += parseFloat(item.answer.marks_obtained) || 0;
    });
  }
  
  Object.values(grades).forEach(grade => {
    total += parseFloat(grade.marks_obtained) || 0;
  });
  
  return Math.round(total);
});

const gradingStatusColor = computed(() => {
  const status = attempt.value?.grading_status;
  if (status === 'completed') return 'positive';
  if (status === 'partial') return 'orange';
  return 'grey';
});

const gradingStatusLabel = computed(() => {
  const status = attempt.value?.grading_status;
  return status ? status.charAt(0).toUpperCase() + status.slice(1) : '';
});

const formatDate = (dateString) => {
  return dateString ? date.formatDate(dateString, 'YYYY-MM-DD HH:mm') : '';
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

const saveGrades = async () => {
  saving.value = true;
  
  try {
    await window.axios.post(route('qu-grading.save', attempt.value.id), {
      grades: Object.values(grades)
    });
    
    $q.notify({
      type: 'positive',
      message: 'Grades saved successfully'
    });
    
    emit('graded');
    isOpen.value = false;
    
  } catch (error) {
    console.error('Failed to save grades', error);
    let msg = 'Failed to save grades';
    if (error.response?.data?.errors?.grades) {
      msg = error.response.data.errors.grades;
    }
    $q.notify({
      type: 'negative',
      message: msg
    });
  } finally {
    saving.value = false;
  }
};
</script>

<style scoped>
.q-card {
  /* max-width: 1200px; Remove max-width for fullscreen dialog */
}
</style>
