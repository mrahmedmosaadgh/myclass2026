<template>
  <Head title="Curriculum Management" />
  
  <!-- Shared Curriculum Index Component -->
  <Index
    :curricula="curricula"
    :title="`${schoolName} - Curricula`"
    subtitle="Manage your school's curricula"
    :can-create="true"
    :can-edit="true"
    :can-delete="true"
    :can-set-lock-dates="true"
    :loading="loading"
    @create="showCreateDialog = true"
    @edit="handleEdit"
    @delete="handleDelete"
    @manage-lock-dates="showLockDateDialog = true"
  >
    <!-- Additional admin-only actions -->
    <template #additional-actions>
      <slot name="additional-actions"></slot>
    </template>

    <!-- Custom info badge for admin -->
    <template #info-badge>
      <q-badge color="primary" class="q-mb-md">
        Managing {{ curricula.length }} curriculum/curricula
      </q-badge>
    </template>

    <!-- Custom alerts -->
    <template #alerts>
      <slot name="alerts"></slot>
    </template>

    <!-- Custom actions cell if needed -->
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
      
      <q-btn
        v-if="permissions.canDelete"
        flat
        round
        dense
        color="negative"
        icon="delete"
        @click="handleDelete(row)"
      >
        <q-tooltip>Delete</q-tooltip>
      </q-btn>
    </template>
  </Index>

  <!-- Create/Edit Dialog Placeholder -->
  <q-dialog v-model="showCreateDialog">
    <q-card style="min-width: 500px">
      <q-card-section>
        <div class="text-h6">Create Curriculum</div>
      </q-card-section>
      <q-card-section class="q-pt-none">
        <p>Curriculum creation form will be implemented here.</p>
      </q-card-section>
      <q-card-actions align="right">
        <q-btn flat label="Cancel" color="primary" v-close-popup />
        <q-btn flat label="Create" color="primary" />
      </q-card-actions>
    </q-card>
  </q-dialog>

  <!-- Lock Date Dialog Placeholder -->
  <q-dialog v-model="showLockDateDialog">
    <q-card style="min-width: 400px">
      <q-card-section>
        <div class="text-h6">Manage Lock Dates</div>
      </q-card-section>
      <q-card-section class="q-pt-none">
        <p>Lock date management will be implemented here.</p>
      </q-card-section>
      <q-card-actions align="right">
        <q-btn flat label="Close" color="primary" v-close-popup />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<script setup>
import { ref } from 'vue'
import Index from './Index.vue'

const props = defineProps({
  curricula: {
    type: Array,
    required: true
  },
  schoolName: {
    type: String,
    default: 'School'
  }
})

const loading = ref(false)
const showCreateDialog = ref(false)
const showLockDateDialog = ref(false)

const handleEdit = (curriculum) => {
  console.log('Admin editing curriculum:', curriculum)
  // TODO: Implement edit logic with CurriculumForm
}

const handleDelete = (curriculum) => {
  console.log('Admin deleting curriculum:', curriculum)
  // TODO: Implement delete logic
}
</script>
