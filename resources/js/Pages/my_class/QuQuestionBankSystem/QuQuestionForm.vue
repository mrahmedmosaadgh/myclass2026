<template>
  <div class="q-pa-md">
    <q-card>
      <q-card-section>
        <div class="text-h5">{{ question ? 'Edit' : 'Create' }} Question</div>
      </q-card-section>

      <q-card-section>
        <q-form @submit.prevent="submitForm">
          <!-- Subject and Topic -->
          <div class="row q-gutter-md q-mb-md">
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

            <q-select
              v-model="form.topic_id"
              :options="topics"
              option-value="id"
              option-label="name"
              label="Topic"
              clearable
              emit-value
              map-options
              style="flex: 1"
              :disable="!form.subject_id"
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
            :rules="[val => !!val || 'Question type is required']"
            @update:model-value="onQuestionTypeChange"
          />

          <!-- Question Text -->
          <q-input
            v-model="form.question_text"
            type="textarea"
            label="Question Text *"
            rows="4"
            outlined
            class="q-mb-md"
            :rules="[val => !!val || 'Question text is required']"
          />

          <!-- MCQ Options -->
          <div v-if="form.question_type === 'mcq'" class="q-mb-md">
            <div class="text-subtitle2 q-mb-sm">Options</div>
            <div v-for="(option, key) in form.options" :key="key" class="row q-gutter-sm q-mb-sm items-center">
              <q-input
                v-model="form.options[key]"
                :label="`Option ${key}`"
                outlined
                style="flex: 1"
                :rules="[val => !!val || `Option ${key} is required`]"
              />
              <q-checkbox
                :model-value="form.correct_answer.includes(key)"
                @update:model-value="toggleCorrectAnswer(key)"
                label="Correct"
              />
            </div>
            <div class="text-caption text-grey-7">Select one or more correct answers</div>
          </div>

          <!-- True/False -->
          <div v-if="form.question_type === 'true_false'" class="q-mb-md">
            <div class="text-subtitle2 q-mb-sm">Correct Answer</div>
            <q-radio-group v-model="form.correct_answer[0]">
              <q-radio val="true" label="True" />
              <q-radio val="false" label="False" />
            </q-radio-group>
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
              label="Difficulty *"
              style="min-width: 150px"
              :rules="[val => !!val || 'Difficulty is required']"
            />

            <q-select
              v-model="form.bloom_level"
              :options="bloomLevels"
              label="Bloom Level"
              clearable
              style="min-width: 150px"
            >
              <template v-slot:prepend>
                <q-icon :name="form.bloom_level ? getBloomIcon(form.bloom_level) : 'help'" />
              </template>
            </q-select>

            <q-input
              v-model.number="form.marks"
              type="number"
              label="Marks *"
              min="1"
              style="max-width: 120px"
              :rules="[val => val > 0 || 'Marks must be greater than 0']"
            />
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
              @click="$inertia.visit(route('qu-questions.index'))"
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

const props = defineProps({
  subjects: Array,
  question: Object
});

const topics = ref([]);

const questionTypes = [
  { value: 'mcq', label: 'Multiple Choice (MCQ)' },
  { value: 'true_false', label: 'True/False' },
  { value: 'short', label: 'Short Answer' },
  { value: 'long', label: 'Long Answer (Essay)' }
];

const difficultyOptions = ['easy', 'medium', 'hard'];

const bloomLevels = [
  'remember', 'understand', 'apply', 'analyze', 'evaluate', 'create'
];

const form = useForm({
  subject_id: props.question?.subject_id || null,
  topic_id: props.question?.topic_id || null,
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
        curriculum.curriculum_topics || []
      );
    }
  } else {
    topics.value = [];
    form.topic_id = null;
  }
};

// Load topics on mount if question exists
if (props.question?.subject_id) {
  loadTopics(props.question.subject_id);
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
  if (props.question) {
    form.put(route('qu-questions.update', props.question.id));
  } else {
    form.post(route('qu-questions.store'));
  }
};
</script>
