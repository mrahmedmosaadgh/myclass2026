<template>
  <q-card flat bordered>
    <q-card-section>
      <div class="row items-center q-gutter-md">
        <q-select v-model="filters.category" :options="categoryOptions" label="Category" dense filled clearable />
        <q-select v-model="filters.language" :options="languageOptions" label="Language" dense filled clearable />
        <q-select v-model="filters.answered" :options="answeredOptions" label="Answered?" dense filled clearable />
        <q-btn flat icon="refresh" @click="$emit('refresh')" />
      </div>
    </q-card-section>
    <q-separator />
    <q-list bordered separator>
      <q-expansion-item v-for="q in filteredQuestions" :key="q.id" :label="q.title" expand-separator>
        <template #header>
          <div class="row items-center justify-between full-width">
            <div>
              <span class="text-bold">{{ q.title }}</span>
              <q-badge v-if="q.is_required" color="red" label="Required" class="q-ml-sm" />
              <q-badge v-for="tag in q.tags" :key="tag" color="primary" class="q-ml-xs">{{ tag }}</q-badge>
            </div>
            <q-btn flat icon="edit" size="sm" @click.stop="$emit('edit', q)" v-if="q.canEdit" />
          </div>
        </template>
        <div class="q-mt-md">
          <AnswerInput :question="q" v-model="answers[q.id]" />
          <div class="row q-gutter-sm q-mt-sm">
            <q-btn color="primary" label="Save Answer" @click="saveAnswer(q)" />
            <q-btn flat label="Save Draft" @click="saveDraft(q)" />
          </div>
        </div>
      </q-expansion-item>
    </q-list>
  </q-card>
</template>

<script setup>
import { ref, computed, defineProps, defineEmits, watch } from 'vue';
import AnswerInput from './AnswerInput.vue';
import { submitAnswer } from '../api/resumeApi';

const props = defineProps({
  questions: Array,
  resumeId: [String, Number]
});
const emit = defineEmits(['edit', 'refresh']);

const filters = ref({ category: null, language: null, answered: null });
const categoryOptions = computed(() => {
  const cats = new Set();
  props.questions.forEach(q => (q.category || []).forEach(c => cats.add(c)));
  return Array.from(cats);
});
const languageOptions = computed(() => {
  const langs = new Set();
  props.questions.forEach(q => langs.add(q.language));
  return Array.from(langs);
});
const answeredOptions = [
  { label: 'Answered', value: true },
  { label: 'Unanswered', value: false }
];

const answers = ref({});

const filteredQuestions = computed(() => {
  return props.questions.filter(q => {
    if (filters.value.category && !(q.category || []).includes(filters.value.category)) return false;
    if (filters.value.language && q.language !== filters.value.language) return false;
    if (filters.value.answered !== null) {
      const hasAnswer = !!answers.value[q.id]?.text || !!answers.value[q.id]?.media?.length;
      if (filters.value.answered && !hasAnswer) return false;
      if (!filters.value.answered && hasAnswer) return false;
    }
    return true;
  });
});

function saveAnswer(q) {
  submitAnswer(q.id, answers.value[q.id]);
  emit('refresh');
}
function saveDraft(q) {
  // Placeholder: save to localStorage or similar
}

watch(() => props.questions, (qs) => {
  qs.forEach(q => {
    if (!answers.value[q.id]) answers.value[q.id] = { text: '', media: [], externalLinks: '' };
  });
}, { immediate: true });
</script>
