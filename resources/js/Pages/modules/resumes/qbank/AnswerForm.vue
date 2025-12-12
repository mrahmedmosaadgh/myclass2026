<template>
  <q-dialog :model-value="modelValue" @update:model-value="val => $emit('update:modelValue', val)">
    <q-card style="min-width:400px">
      <q-card-section>
        <div class="text-h6">{{ answer ? 'Edit' : 'Add' }} Answer</div>
      </q-card-section>
      <q-form @submit.prevent="save">
        <q-card-section class="q-gutter-md">
          <q-editor v-model="form.answer_text" label="Answer" required />
          <q-input v-model="form.media_links" label="Media Links (comma separated)" />
          <q-uploader v-model="form.attachments" label="Attachments" multiple />
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
import { ref, watch } from 'vue';
import { useQuasar } from 'quasar';
import resumeApi from '@/modules/resumes/resumes_questions/resumeApi.js';

const props = defineProps({
  modelValue: Boolean,
  answer: Object,
  questionId: Number
});
const emit = defineEmits(['update:modelValue', 'save']);
const $q = useQuasar();

const form = ref({
  answer_text: '',
  media_links: '',
  attachments: []
});

watch(() => props.answer, (a) => {
  if (a) form.value = { ...a, media_links: (a.media_links || []).join(', ') };
  else form.value = { answer_text: '', media_links: '', attachments: [] };
}, { immediate: true });

function save() {
  const data = {
    ...form.value,
    media_links: form.value.media_links.split(',').map(s => s.trim()).filter(Boolean)
  };
  (props.answer ? resumeApi.updateAnswer(props.answer.id, data) : resumeApi.createAnswer(props.questionId, data))
    .then(() => {
      emit('save');
      emit('update:modelValue', false);
      $q.notify({ type: 'positive', message: 'Saved!' });
    })
    .catch(() => $q.notify({ type: 'negative', message: 'Save failed' }));
}
</script>
