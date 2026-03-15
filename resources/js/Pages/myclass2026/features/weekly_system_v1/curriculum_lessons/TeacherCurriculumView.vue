<template>
  <Head title="My Curriculum Access" />
  
  <!-- Shared Curriculum Index Component -->
  <Index
    :curricula="curricula"
    title="My Assigned Curricula"
    subtitle="View curricula for your assigned classes"
    :can-create="false"
    :can-edit="true"
    :can-delete="false"
    :can-set-lock-dates="false"
    :loading="loading"
    @edit="handleEdit"
  >
    <!-- Teacher info badge -->
    <template #info-badge>
      <q-badge color="secondary" class="q-mb-md">
        Teacher: {{ teacherName }}
      </q-badge>
    </template>

    <!-- Warning alert when no curricula -->
    <template #alerts>
      <q-alert 
        v-if="!curricula || curricula.length === 0"
        color="warning"
        icon="warning"
        class="q-mb-md"
      >
        You are not assigned to any curricula yet. Please contact your administrator.
      </q-alert>
    </template>

    <!-- Custom actions cell - teachers can only edit unlocked curricula -->
    <template #actions-cell="{ row, permissions }">
      <q-btn
        v-if="permissions.canEdit && (row.is_editable ?? true)"
        flat
        round
        dense
        color="primary"
        icon="edit"
        @click="handleEdit(row)"
      >
        <q-tooltip>Edit</q-tooltip>
      </q-btn>
      
      <q-badge v-if="!(row.is_editable ?? true)" color="grey-6">
        Locked
      </q-badge>
    </template>
  </Index>
</template>

<script setup>
import { ref } from 'vue'
import Index from './Index.vue'

const props = defineProps({
  curricula: {
    type: Array,
    required: true
  },
  teacherName: {
    type: String,
    default: 'Teacher'
  }
})

const loading = ref(false)

const handleEdit = (curriculum) => {
  console.log('Teacher editing curriculum:', curriculum)
  // TODO: Implement edit logic for teacher
}
</script>
