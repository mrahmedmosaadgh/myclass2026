<template>
  <q-dialog v-model="model1" @update:model-value="val => $emit('update:modelValue', val)">
    <q-card style="min-width:400px">
      <q-card-section>
        <div class="text-h6">{{ question ? 'Edit' : 'Add' }} Question</div>
      </q-card-section>
      <q-form @submit.prevent="save">
        <q-card-section class="q-gutter-md">
          <q-input v-model="form.title" label="Title" required :error="!!errors.value.title" :error-message="errors.value.title && errors.value.title[0]" />
          <q-select v-model="form.type" :options="types" label="Type" required :error="!!errors.value.type" :error-message="errors.value.type && errors.value.type[0]" />
          <q-select v-model="form.category" :options="categories" label="Category" required :error="!!errors.value.category" :error-message="errors.value.category && errors.value.category[0]" />
          <q-select v-model="form.language" :options="languages" label="Language" required :error="!!errors.value.language" :error-message="errors.value.language && errors.value.language[0]" />
          <q-select v-model="form.tags" :options="tags" label="Tags" multiple use-chips :error="!!errors.value.tags" :error-message="errors.value.tags && errors.value.tags[0]" />
          <q-editor v-model="form.default_answer" label="Default Answer" :error="!!errors.value.default_answer" :error-message="errors.value.default_answer && errors.value.default_answer[0]" />
        </q-card-section>
        <q-card-actions align="right">
          <q-btn flat label="Cancel" v-close-popup />
          <q-btn color="primary" label="Save" type="submit" />
        </q-card-actions>
      </q-form>
    </q-card>
  </q-dialog>
</template>

<script setup>
import { ref, watch, computed } from 'vue';
import { useQuasar } from 'quasar';
import resumeApi from '../../../../modules/resumes/resumes_questions/resumeApi';

const model1 = defineModel();
const props = defineProps({
  modelValue: Boolean,
  question: Object
});
const emit = defineEmits(['update:modelValue', 'save']);
const $q = useQuasar();

const types = ['General', 'Technical', 'Behavioral'];
const categories = ['Frontend', 'Backend', 'Fullstack', 'Other'];
const languages = ['English', 'Spanish', 'French', 'Other'];
const tags = ['Vue', 'Laravel', 'API', 'Design', 'Other'];

const form = ref({
  title: '',
  type: '',
  category: '',
  language: '',
  tags: [],
  default_answer: ''
});

const errors = ref({});

watch(() => props.question, (q) => {
  if (q) {
    form.value = { ...q };
  } else {
    form.value = {
      title: '',
      type: '',
      category: '',
      language: '',
      tags: [],
      default_answer: ''
    };
    errors.value = {};
  }
}, { immediate: true });

function save() {
  const data = {
    title: form.value.title || '',
    type: form.value.type || '',
    category: form.value.category || '',
    language: form.value.language || '',
    tags: Array.isArray(form.value.tags) ? form.value.tags : [],
    default_answer: form.value.default_answer || ''
  };
  errors.value = {};
  (props.question ? resumeApi.updateQuestion(props.question.id, data) : resumeApi.createQuestion(data))
    .then(() => {
      emit('save');
      emit('update:modelValue', false);
      $q.notify({ type: 'positive', message: 'Saved!' });
    })
    .catch((err) => {
      if (err.response && err.response.status === 422) {
        errors.value = err.response.data.errors || {};
        $q.notify({ type: 'negative', message: 'Validation error' });
      } else {
        $q.notify({ type: 'negative', message: 'Save failed' });
      }
    });
}
</script>
