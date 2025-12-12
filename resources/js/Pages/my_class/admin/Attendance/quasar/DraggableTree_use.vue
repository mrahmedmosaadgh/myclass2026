<template>
    <div>
      <array-editor
        :rows="items"
        :columns="columns"
        @update:rows="updateItems"
      />
    </div>
  </template>

  <script setup>
  import { ref } from 'vue'
  import ArrayEditor from './ArrayEditor.vue'

  // Define the columns for the table
  const columns = ref([
    { name: 'id', label: 'ID', field: 'id', required: true },
    { name: 'label', label: 'Label', field: 'label', required: true },
  ])

  // Define the items data
  const items = ref([
    { id: '1', label: 'Item 1' },
    { id: '2', label: 'Item 2' },
  ])

  // Handle the updated rows in the parent component
  function updateItems(updatedRows) {
    items.value = updatedRows
  }
  </script>
