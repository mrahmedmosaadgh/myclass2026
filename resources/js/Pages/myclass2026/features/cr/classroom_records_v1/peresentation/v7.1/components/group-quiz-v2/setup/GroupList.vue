<script setup>
import { ref } from 'vue';
import GroupCard from './GroupCard.vue';

const props = defineProps({
  groups: { type: Array, required: true }
});

const emit = defineEmits([
  'addGroup', 'removeGroup', 'updateName', 'updateScore', 'updateColor',
  'importJson', 'exportJson', 'resetScores'
]);

const newGroupName = ref('');

function handleAdd() {
  if (newGroupName.value.trim()) {
    emit('addGroup', newGroupName.value.trim());
    newGroupName.value = '';
  } else {
    emit('addGroup', '');
  }
}
</script>

<template>
  <div class="group-list">
    <!-- Add new group -->
    <div class="row q-gutter-sm q-mb-md items-center">
      <q-input
        v-model="newGroupName"
        placeholder="New group name..."
        dense
        outlined
        class="col"
        @keyup.enter="handleAdd"
      >
        <template #append>
          <q-btn
            flat
            round
            dense
            icon="add"
            color="primary"
            @click="handleAdd"
          />
        </template>
      </q-input>
    </div>

    <!-- Group count badge -->
    <div class="row items-center q-mb-sm">
      <q-badge color="primary" class="q-mr-sm">
        {{ groups.length }} Groups
      </q-badge>
      <q-space />
      <q-btn
        flat
        dense
        no-caps
        size="sm"
        color="grey-7"
        icon="download"
        label="Export JSON"
        @click="$emit('exportJson')"
        class="q-mr-xs"
      />
      <q-btn
        flat
        dense
        no-caps
        size="sm"
        color="grey-7"
        icon="upload"
        label="Import JSON"
        @click="$emit('importJson')"
        class="q-mr-xs"
      />
      <q-btn
        flat
        dense
        no-caps
        size="sm"
        color="negative"
        icon="restart_alt"
        label="Reset Scores"
        @click="$emit('resetScores')"
      />
    </div>

    <!-- Group cards -->
    <q-list padding class="rounded-borders">
      <GroupCard
        v-for="g in groups"
        :key="g.id"
        :group="g"
        @updateName="$emit('updateName', g.id, $event)"
        @updateScore="$emit('updateScore', g.id, $event)"
        @updateColor="$emit('updateColor', g.id, $event)"
        @remove="$emit('removeGroup', g.id)"
      />
    </q-list>

    <!-- Empty state -->
    <div v-if="groups.length === 0" class="empty-state text-center q-pa-lg">
      <q-icon name="groups" size="48px" color="grey-4" />
      <div class="text-grey-6 q-mt-sm">No groups yet. Add one above.</div>
    </div>
  </div>
</template>

<style scoped>
.group-list {
  max-height: 60vh;
  overflow-y: auto;
}
.empty-state {
  border: 2px dashed #e2e8f0;
  border-radius: 12px;
}
</style>
