<template>
    <div class="q-pa-md">
      <h2 class="text-h5">Editable Tree with Drag & Drop</h2>
      <tree-editor v-model="treeData" />
      <pre>{{ treeData }}</pre> <!-- For testing -->
    </div>
  </template>

  <script setup>
  import { ref } from 'vue'
  import TreeEditor from './TreeEditor.vue'

  const treeData = ref([
    {
      id: 1,
      label: 'Parent Node',
      children: [
        { id: 2, label: 'Child Node 1' },
        { id: 3, label: 'Child Node 2' }
      ]
    },
    {
      id: 4,
      label: 'Another Parent'
    }
  ])
  </script>
