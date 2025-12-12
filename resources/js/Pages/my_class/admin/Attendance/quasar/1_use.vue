<template>
  <div>
    <array-editor
      v-model:rows="items"
      :columns="columns"
      title="Student Records"
    />
  </div>
</template>

<script setup>
import { ref } from 'vue'
import ArrayEditor from './1.vue'

const columns = ref([
  {
    name: 'id',
    label: 'ID',
    field: 'id',
    required: true
  },
  {
    name: 'name',
    label: 'Name',
    field: 'name',
    editable: true,
    required: true
  },
  {
    name: 'age',
    label: 'Age',
    field: 'age',
    editable: true,
    required: true
  },
  {
    name: 'city',
    label: 'City',
    field: 'city',
    editable: true,
    required: true
  }
])

const items = ref([
  { id: '1', name: 'John Doe', age: '30', city: 'New York' },
  { id: '2', name: 'Jane Smith', age: '25', city: 'London' }
])
</script>
