<template>
  <div class="q-pa-md">
    <q-card>
      <q-card-section>
        <div class="text-h5">{{ question ? 'Edit' : 'Create' }} Question</div>
      </q-card-section>

      <q-card-section>
        <q-form @submit.prevent="submitForm">
          <!-- Subject (only show when editing or no subject pre-selected) -->
          <div v-if="!selectedSubjectId" class="row q-gutter-md q-mb-md">
            <q-select
              v-model="form.subject_id"
              :options="subjects"
              option-value="id"
              option-label="name"
              label="Subject *"
              emit-value
              map-options
              style="flex: 1"
              :rules="[val => !!val || 'Subject is required']"
              @update:model-value="loadTopics"
            />
          </div>

          <!-- Topic (always show, filtered by subject) -->
          <div class="q-mb-md">
            <q-select
              v-model="form.topic_id"
              :options="topics"
              option-value="id"
              option-label="name"
              label="Topic (Optional)"
              clearable
              emit-value
              map-options
              :disable="!form.subject_id"
            />
          </div>

          <!-- Custom Group (Optional) -->
          <div class="q-mb-md">
            <q-input
              v-model="form.custom_group"
              label="Group (Optional)"
              outlined
              hint="e.g. Exam Title or Specific Chapter"
            />
          </div>

          <!-- Question Type -->
          <q-select
            v-model="form.question_type"
            :options="questionTypes"
            option-value="value"
            option-label="label"
            label="Question Type *"
            emit-value
            map-options
            class="q-mb-md"
            :rules="[val => !!val || 'Please select a question type']"
            @update:model-value="onQuestionTypeChange"
          >
            <template v-slot:hint>
              Choose how students will answer this question
            </template>
          </q-select>

          <!-- Question Text -->
          <q-input
            v-model="form.question_text"
            type="textarea"
            label="Question Text *"
            rows="4"
            outlined
            class="q-mb-md"
            counter
            maxlength="1000"
            :rules="[
              val => !!val || 'Question text is required',
              val => val.length >= 10 || 'Question must be at least 10 characters',
              val => val.length <= 1000 || 'Question must not exceed 1000 characters'
            ]"
          >
            <template v-slot:hint>
              Write a clear and concise question (10-1000 characters)
            </template>
          </q-input>

          <!-- MCQ Options -->
          <div v-if="form.question_type === 'mcq'" class="q-mb-md">
            <div class="text-subtitle2 q-mb-sm">Multiple Choice Options *</div>
            <div v-for="(option, key) in form.options" :key="key" class="row q-gutter-sm q-mb-sm items-center">
              <q-input
                v-model="form.options[key]"
                :label="`Option ${key}`"
                outlined
                style="flex: 1"
                :rules="[
                  val => !!val || `Option ${key} cannot be empty`,
                  val => val.length >= 1 || `Option ${key} must have at least 1 character`
                ]"
              />
              <q-checkbox
                :model-value="form.correct_answer.includes(key)"
                @update:model-value="toggleCorrectAnswer(key)"
                label="Correct"
                color="positive"
              />
            </div>
            <q-banner v-if="form.correct_answer.length === 0" class="bg-warning text-white q-mt-sm">
              <template v-slot:avatar>
                <q-icon name="warning" />
              </template>
              Please select at least one correct answer
            </q-banner>
            <div v-else class="text-caption text-positive">
              ✓ {{ form.correct_answer.length }} correct answer(s) selected
            </div>
          </div>

          <!-- True/False -->
          <div v-if="form.question_type === 'true_false'" class="q-mb-md">
            <div class="text-subtitle2 q-mb-sm">Correct Answer</div>
            <q-option-group 
              v-model="form.correct_answer[0]"
              :options="[
                { label: 'True', value: 'true' },
                { label: 'False', value: 'false' }
              ]"
              type="radio"
            />
          </div>

          <!-- Short/Long Answer (No options needed, teacher will grade manually) -->
          <div v-if="form.question_type === 'short' || form.question_type === 'long'" class="q-mb-md">
            <q-banner class="bg-info text-white">
              <template v-slot:avatar>
                <q-icon name="info" />
              </template>
              This question type requires manual grading. Students will provide text answers.
            </q-banner>
          </div>

          <!-- Difficulty and Bloom Level -->
          <div class="row q-gutter-md q-mb-md">
            <q-select
              v-model="form.difficulty"
              :options="difficultyOptions"
              label="Difficulty Level *"
              style="min-width: 150px"
              :rules="[val => !!val || 'Please select difficulty level']"
            >
              <template v-slot:hint>
                How challenging is this question?
              </template>
            </q-select>

            <q-select
              v-model="form.bloom_level"
              :options="bloomLevelOptions"
              option-value="value"
              option-label="label"
              label="Bloom's Taxonomy Level"
              clearable
              emit-value
              map-options
              style="min-width: 200px"
            >
              <template v-slot:prepend>
                <q-icon :name="form.bloom_level ? getBloomIcon(form.bloom_level) : 'help'" />
              </template>
              <template v-slot:hint>
                Cognitive skill level (optional)
              </template>
            </q-select>

            <q-input
              v-model.number="form.marks"
              type="number"
              label="Marks *"
              min="1"
              max="100"
              style="max-width: 120px"
              :rules="[
                val => val > 0 || 'Marks must be greater than 0',
                val => val <= 100 || 'Marks cannot exceed 100'
              ]"
            >
              <template v-slot:hint>
                Points (1-100)
              </template>
            </q-input>
          </div>

          <!-- Submit Buttons -->
          <div class="row q-gutter-sm">
            <q-btn 
              type="submit" 
              color="primary" 
              label="Save Question"
              :loading="form.processing"
            />
            <q-btn 
              flat 
              label="Cancel" 
              @click="emit('cancel')"
            />
          </div>
        </q-form>
      </q-card-section>
    </q-card>
  </div>
</template>

<script setup>
import { ref, reactive, watch, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { useQuasar } from 'quasar';

const $q = useQuasar();

const props = defineProps({
  subjects: Array,
  question: Object,
  selectedSubjectId: [Number, String]
});

const emit = defineEmits(['success', 'cancel']);

const topics = ref([]);

const questionTypes = [
  { value: 'mcq', label: 'Multiple Choice (MCQ)' },
  { value: 'true_false', label: 'True/False' },
  { value: 'short', label: 'Short Answer' },
  { value: 'long', label: 'Long Answer (Essay)' }
];

const difficultyOptions = ['easy', 'medium', 'hard'];

const bloomLevelOptions = [
  { value: 'remember', label: 'Remember - Recall facts and basic concepts' },
  { value: 'understand', label: 'Understand - Explain ideas or concepts' },
  { value: 'apply', label: 'Apply - Use information in new situations' },
  { value: 'analyze', label: 'Analyze - Draw connections among ideas' },
  { value: 'evaluate', label: 'Evaluate - Justify a decision or course of action' },
  { value: 'create', label: 'Create - Produce new or original work' }
];

const form = useForm({
  subject_id: props.question?.subject_id || props.selectedSubjectId || null,
  topic_id: props.question?.topic_id || null,
  custom_group: props.question?.custom_group || '',
  question_text: props.question?.question_text || '',
  question_type: props.question?.question_type || 'mcq',
  options: props.question?.options || { A: '', B: '', C: '', D: '' },
  correct_answer: props.question?.correct_answer || [],
  difficulty: props.question?.difficulty || 'medium',
  bloom_level: props.question?.bloom_level || null,
  marks: props.question?.marks || 1,
});

// Load topics when subject changes
const loadTopics = (subjectId) => {
  if (subjectId) {
    const subject = props.subjects.find(s => s.id === subjectId);
    if (subject && subject.curricula) {
      // Flatten all topics from all curricula
      topics.value = subject.curricula.flatMap(curriculum => 
        curriculum.topics || []
      );
    }
  } else {
    topics.value = [];
    form.topic_id = null;
  }
};

// Load topics on mount if question exists or selectedSubjectId is provided
if (props.question?.subject_id) {
  loadTopics(props.question.subject_id);
} else if (props.selectedSubjectId) {
  loadTopics(props.selectedSubjectId);
}

const onQuestionTypeChange = (type) => {
  if (type === 'mcq') {
    form.options = { A: '', B: '', C: '', D: '' };
    form.correct_answer = [];
  } else if (type === 'true_false') {
    form.options = { true: 'True', false: 'False' };
    form.correct_answer = ['true'];
  } else {
    form.options = {};
    form.correct_answer = ['N/A']; // For manual grading
  }
};

const toggleCorrectAnswer = (key) => {
  const index = form.correct_answer.indexOf(key);
  if (index > -1) {
    form.correct_answer.splice(index, 1);
  } else {
    form.correct_answer.push(key);
  }
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

const submitForm = () => {
  // Validate MCQ has at least one correct answer
  if (form.question_type === 'mcq' && form.correct_answer.length === 0) {
    $q.notify({
      type: 'warning',
      message: 'Please select at least one correct answer for MCQ',
      position: 'top'
    });
    return;
  }

  if (props.question) {
    form.put(route('qu.questions.update', props.question.id), {
      onSuccess: () => {
        $q.notify({
          type: 'positive',
          message: 'Question updated successfully!',
          icon: 'check_circle',
          position: 'top'
        });
        emit('success');
      },
      onError: (errors) => {
        $q.notify({
          type: 'negative',
          message: 'Failed to update question. Please check the form.',
          caption: Object.values(errors)[0],
          position: 'top'
        });
      }
    });
  } else {
    form.post(route('qu.questions.store'), {
      onSuccess: () => {
        $q.notify({
          type: 'positive',
          message: 'Question created successfully!',
          icon: 'check_circle',
          position: 'top'
        });
        // Reset form for next question
        form.reset();
        form.question_text = '';
        form.options = { A: '', B: '', C: '', D: '' };
        form.correct_answer = [];
        form.difficulty = 'medium';
        form.bloom_level = null;
        form.marks = 1;
        emit('success');
      },
      onError: (errors) => {
        $q.notify({
          type: 'negative',
          message: 'Failed to create question. Please check the form.',
          caption: Object.values(errors)[0],
          position: 'top'
        });
      }
    });
  }
};
</script>
