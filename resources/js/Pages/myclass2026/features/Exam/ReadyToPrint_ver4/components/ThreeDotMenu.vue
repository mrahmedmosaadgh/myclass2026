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

      <q-separator />

      <q-item clickable>
        <q-item-section avatar>
          <q-icon name="settings" />
        </q-item-section>
        <q-item-section>
          <q-item-label>Settings</q-item-label>
        </q-item-section>
        <q-item-section side>
          <q-icon name="chevron_right" />
        </q-item-section>

        <q-menu anchor="top end" self="top start">
          <q-list dense style="min-width: 200px;">
            <q-item clickable v-close-popup @click="$emit('open-settings', 'generate')">
              <q-item-section avatar>
                <q-icon name="auto_awesome" />
              </q-item-section>
              <q-item-section>
                <q-item-label>Generate</q-item-label>
              </q-item-section>
            </q-item>

            <q-item clickable v-close-popup @click="$emit('open-settings', 'questions')">
              <q-item-section avatar>
                <q-icon name="quiz" />
              </q-item-section>
              <q-item-section>
                <q-item-label>Questions</q-item-label>
              </q-item-section>
            </q-item>
          </q-list>
        </q-menu>
      </q-item>
    </q-list>
  </q-btn-dropdown>
</template>

<script setup>
defineEmits(['open-exam', 'open-my-exams', 'open-settings'])
</script>
