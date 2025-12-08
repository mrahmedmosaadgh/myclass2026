<template>
  <q-dialog v-model="show" persistent>
    <q-card style="min-width:400px;max-width:900px;width:100%">
      <q-card-section class="row items-center q-pb-none">
        <div class="text-h6">Answers for: {{ question.title }}</div>
        <q-space />
        <q-btn icon="close" flat round dense v-close-popup @click="close" />
      </q-card-section>
      <q-separator />
      <q-card-section>
        <q-table
          :rows="answers"
          :columns="columns"
          row-key="id"
          :pagination="{ rowsPerPage: 10 }"
          :loading="loading"
        >
          <template #top>
            <q-btn color="primary" icon="add" label="Add Answer" @click="onAdd" />
          </template>
          <template #body-cell-actions="props">
            <q-td>
              <q-btn dense flat icon="edit" color="primary" @click="onEdit(props.row)" />
              <q-btn dense flat icon="delete" color="negative" @click="onDelete(props.row)" />
            </q-td>
          </template>
        </q-table>
        <q-dialog v-model="answerDialog">
          <q-card style="min-width:300px;max-width:600px;width:100%">
            <q-card-section class="row items-center q-pb-none">
              <div class="text-h6">{{ editAnswer ? 'Edit' : 'Add' }} Answer</div>
              <q-space />
              <q-btn icon="close" flat round dense v-close-popup @click="closeAnswerDialog" />
            </q-card-section>
            <q-separator />
            <q-card-section>
              <AnswerInput :question="question" v-model="answerForm" />
            </q-card-section>
            <q-card-actions align="right">
              <q-btn color="primary" label="Save" @click="saveAnswer" />
            </q-card-actions>
          </q-card>
        </q-dialog>
      </q-card-section>
    </q-card>
  </q-dialog>
</template>

<script setup>
import { ref, defineProps, defineEmits, watch } from 'vue';
import AnswerInput from './AnswerInput.vue';

const props = defineProps({
  show: Boolean,
  question: Object,
  answers: Array,
  loading: Boolean
});
const emit = defineEmits(['close', 'add', 'edit', 'delete', 'save']);
const answerDialog = ref(false);
const editAnswer = ref(null);
const answerForm = ref({ text: '', media: [], externalLinks: '' });

const columns = [
  { name: 'id', label: 'ID', field: 'id', align: 'left' },
  { name: 'text', label: 'Answer', field: 'text', align: 'left' },
  { name: 'media', label: 'Media', field: row => (row.media || []).length, align: 'center' },
  { name: 'externalLinks', label: 'Links', field: 'externalLinks', align: 'left' },
  { name: 'actions', label: 'Actions', field: 'actions', align: 'center' }
];

function close() { emit('close'); }
function onAdd() {
  editAnswer.value = null;
  answerForm.value = { text: '', media: [], externalLinks: '' };
  answerDialog.value = true;
}
function onEdit(row) {
  editAnswer.value = row;
  answerForm.value = { ...row };
  answerDialog.value = true;
}
function onDelete(row) { emit('delete', row); }
function closeAnswerDialog() { answerDialog.value = false; }
function saveAnswer() {
  emit('save', { ...answerForm.value, id: editAnswer.value ? editAnswer.value.id : undefined });
  answerDialog.value = false;
}
</script>
