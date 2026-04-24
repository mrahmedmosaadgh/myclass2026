<template>
  <q-btn-dropdown
    dense
    flat
    round
    icon="more_vert"
    dropdown-icon=""
    @click.stop
  >
    <q-list dense style="min-width: 220px;">
      <q-item clickable v-close-popup @click="$emit('copy-questions')">
        <q-item-section avatar>
          <q-icon name="content_copy" />
        </q-item-section>
        <q-item-section>
          <q-item-label>Copy Questions (JSON)</q-item-label>
        </q-item-section>
      </q-item>

      <q-item clickable v-close-popup @click="$emit('copy-settings')">
        <q-item-section avatar>
          <q-icon name="settings" />
        </q-item-section>
        <q-item-section>
          <q-item-label>Copy Settings (JSON)</q-item-label>
        </q-item-section>
      </q-item>

      <q-item clickable v-close-popup @click="$emit('copy-full')">
        <q-item-section avatar>
          <q-icon name="inventory_2" />
        </q-item-section>
        <q-item-section>
          <q-item-label>Copy Full Exam (JSON)</q-item-label>
        </q-item-section>
      </q-item>
    </q-list>
  </q-btn-dropdown>
</template>

<script setup>
defineEmits(['copy-questions', 'copy-settings', 'copy-full'])
</script>
