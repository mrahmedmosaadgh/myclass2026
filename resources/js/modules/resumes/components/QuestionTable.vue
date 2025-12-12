<template>
  <q-card flat bordered>
    <q-table
      :rows="questions"
      :columns="columns"
      row-key="id"
      :filter="search"
      :pagination="{ rowsPerPage: 10 }"
      :loading="loading"
      class="q-mb-md"
    >
      <template #top>
        <div class="row items-center q-gutter-md full-width">
          <q-input v-model="search" label="Search questions..." dense filled clearable debounce="300" class="col" />
          <q-btn color="primary" icon="add" label="Add" @click="onAdd" v-if="isAdmin" />
        </div>
      </template>
      <template #body-cell-actions="props">
        <q-td>
          <q-btn dense flat icon="edit" color="primary" @click="onEdit(props.row)" />
          <q-btn dense flat icon="delete" color="negative" @click="onDelete(props.row)" />
          <q-btn dense flat icon="list" color="secondary" @click="onViewAnswers(props.row)" />
        </q-td>
      </template>
    </q-table>
  </q-card>
</template>

<script setup>
import { ref, defineProps, defineEmits, watch } from 'vue';

const props = defineProps({
  questions: Array,
  loading: Boolean,
  isAdmin: Boolean
});
const emit = defineEmits(['add', 'edit', 'delete', 'view-answers']);
const search = ref('');

const columns = [
  { name: 'title', label: 'Title', field: 'title', align: 'left', sortable: true },
  { name: 'type', label: 'Type', field: 'type', align: 'left', sortable: true },
  { name: 'category', label: 'Category', field: row => (row.category || []).join(', '), align: 'left' },
  { name: 'language', label: 'Language', field: 'language', align: 'left' },
  { name: 'is_required', label: 'Required', field: row => row.is_required ? 'Yes' : 'No', align: 'center' },
  { name: 'actions', label: 'Actions', field: 'actions', align: 'center' }
];

function onAdd() { emit('add'); }
function onEdit(row) { emit('edit', row); }
function onDelete(row) { emit('delete', row); }
function onViewAnswers(row) { emit('view-answers', row); }
</script>
