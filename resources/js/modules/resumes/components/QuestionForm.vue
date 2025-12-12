<template>
  <q-card flat bordered>
    <q-form @submit.prevent="onSubmit" class="q-gutter-md">
      <q-input v-model="form.title" label="Question Title" :rules="[val => !!val || 'Required']" dense filled />
      <q-select v-model="form.type" :options="typeOptions" label="Type" dense filled :rules="[val => !!val || 'Required']" />
      <q-input v-if="form.type === 'select' || form.type === 'multi'" v-model="form.options" label="Options (comma separated)" dense filled />
      <q-editor v-if="form.type === 'textarea' || form.type === 'text'" v-model="form.default_answer" label="Default Answer (optional)" min-height="5rem" />
      <q-select v-model="form.category" :options="categoryOptions" label="Category" use-input use-chips multiple dense filled />
      <q-select v-model="form.language" :options="languageOptions" label="Language" dense filled />
      <q-select v-model="form.tags" :options="tagOptions" label="Tags" use-input use-chips multiple dense filled />
      <q-toggle v-model="form.is_required" label="Required?" />
      <div class="row items-center q-gutter-sm">
        <q-btn type="submit" color="primary" label="{{ form.id ? 'Update' : 'Add' }} Question" />
        <q-btn flat label="Reset" @click="resetForm" />
      </div>
    </q-form>
  </q-card>
</template>

<script setup>
import { ref, watch, defineProps, defineEmits } from 'vue';
import { useQuasar } from 'quasar';
import { submitQuestion } from '../api/resumeApi';

const props = defineProps({
  editQuestion: Object
});
const emit = defineEmits(['saved']);
const $q = useQuasar();

const typeOptions = [
  { label: 'Text', value: 'text' },
  { label: 'Textarea', value: 'textarea' },
  { label: 'Select', value: 'select' },
  { label: 'Multi-Select', value: 'multi' },
  { label: 'Media', value: 'media' }
];
const categoryOptions = ref(['General', 'Education', 'Experience']);
const languageOptions = ref(['en', 'ar', 'fr']);
const tagOptions = ref(['core', 'optional', 'profile']);

const form = ref({
  id: null,
  title: '',
  type: '',
  default_answer: '',
  category: [],
  language: '',
  tags: [],
  options: '',
  is_required: false
});

watch(() => props.editQuestion, (val) => {
  if (val) {
    form.value = { ...val };
  } else {
    resetForm();
  }
});

function resetForm() {
  form.value = {
    id: null,
    title: '',
    type: '',
    default_answer: '',
    category: [],
    language: '',
    tags: [],
    options: '',
    is_required: false
  };
}

async function onSubmit() {
  await submitQuestion(form.value);
  $q.notify({ type: 'positive', message: 'Question saved!' });
  emit('saved');
  resetForm();
}
</script>
