<template>
  <q-btn-dropdown
    flat
     
    dense
    icon="more_vert"
    dropdown-icon=""
    aria-label="Menu"
  >
    <q-list dense style="min-width: 220px;">
      <q-item clickable v-close-popup @click="$emit('open-exam')">
        <q-item-section avatar>
          <q-icon name="folder_open" />
        </q-item-section>
        <q-item-section>
          <q-item-label>Open Exam</q-item-label>
        </q-item-section>
      </q-item>

      <q-item clickable v-close-popup @click="$emit('open-my-exams')">
        <q-item-section avatar>
          <q-icon name="list" />
        </q-item-section>
        <q-item-section>
          <q-item-label>Open My Exams</q-item-label>
        </q-item-section>
      </q-item>
    </q-list>
  </q-btn-dropdown>
</template>

<script setup>
defineEmits(['open-exam', 'open-my-exams'])
</script>
