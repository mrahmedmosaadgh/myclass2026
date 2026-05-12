<script setup>
import { ref, nextTick } from 'vue';
import { useGameStore } from '../../../stores/gameStore';
import GroupList from './GroupList.vue';
import ScoringSettings from './ScoringSettings.vue';
import JsonImportDialog from './JsonImportDialog.vue';
import QrPrintSheet from '../components/QrPrintSheet.vue';

const gameStore = useGameStore();

const props = defineProps({
  modelValue: { type: Boolean, default: false }
});

const emit = defineEmits(['update:modelValue']);

const activeTab = ref('setup');
const showImportDialog = ref(false);
const printSheetRef = ref(null);

function close() {
  emit('update:modelValue', false);
  activeTab.value = 'setup';
}

function handleAddGroup(name) {
  gameStore.addGroup(name);
}

function handleRemoveGroup(id) {
  gameStore.removeGroup(id);
}

function handleUpdateName(id, newName) {
  gameStore.updateGroupName(id, newName);
}

function handleUpdateScore(id, val) {
  const group = gameStore.groups.find((g) => g.id === id);
  if (group) group.score = val;
}

function handleUpdateColor(id, newColor) {
  gameStore.updateGroupColor(id, newColor);
}

function handleResetScores() {
  gameStore.resetScores();
}

async function handleExportJson() {
  const payload = JSON.stringify(gameStore.groups, null, 2);
  try {
    if (navigator?.clipboard?.writeText) {
      await navigator.clipboard.writeText(payload);
    } else {
      const textarea = document.createElement('textarea');
      textarea.value = payload;
      textarea.style.cssText = 'position:fixed;left:-9999px;top:-9999px';
      document.body.appendChild(textarea);
      textarea.select();
      document.execCommand('copy');
      document.body.removeChild(textarea);
    }
    // Using Quasar notify would require $q, skipping for simplicity
  } catch {
    // ignore
  }
}

function handleImportJson(groups) {
  gameStore.groups = groups;
}

async function switchToQrTab() {
  activeTab.value = 'qrcodes';
  await nextTick();
  printSheetRef.value?.updateA4PreviewScale?.();
}
</script>

<template>
  <q-dialog :model-value="modelValue" @update:model-value="$emit('update:modelValue', $event)" maximized persistent>
    <q-card class="setup-dialog">
      <q-bar class="bg-primary text-white">
        <q-icon name="groups" />
        <div>Classroom Group Setup</div>
        <q-space />
        <q-btn dense flat icon="close" @click="close" />
      </q-bar>

      <q-tabs v-model="activeTab" dense class="text-grey-7" active-color="primary" indicator-color="primary" align="justify">
        <q-tab name="setup" icon="people" label="Groups" />
        <q-tab name="scoring" icon="sports_score" label="Scoring" />
        <q-tab name="qrcodes" icon="qr_code" label="QR Codes" />
      </q-tabs>

      <q-separator />

      <q-tab-panels v-model="activeTab" animated class="setup-panels">
        <!-- Groups Tab -->
        <q-tab-panel name="setup" class="q-pa-md">
          <GroupList
            :groups="gameStore.groups"
            @addGroup="handleAddGroup"
            @removeGroup="handleRemoveGroup"
            @updateName="handleUpdateName"
            @updateScore="handleUpdateScore"
            @updateColor="handleUpdateColor"
            @importJson="showImportDialog = true"
            @exportJson="handleExportJson"
            @resetScores="handleResetScores"
          />
        </q-tab-panel>

        <!-- Scoring Tab -->
        <q-tab-panel name="scoring" class="q-pa-md">
          <ScoringSettings
            :settings="gameStore.gameSettings"
            @update="Object.assign(gameStore.gameSettings, $event)"
          />
        </q-tab-panel>

        <!-- QR Codes Tab -->
        <q-tab-panel name="qrcodes" class="q-pa-none">
          <QrPrintSheet ref="printSheetRef" :groups="gameStore.groups" />
        </q-tab-panel>
      </q-tab-panels>

      <q-card-actions align="right" class="q-pa-md bg-grey-1">
        <q-btn flat label="Close" color="grey-7" @click="close" />
      </q-card-actions>
    </q-card>
  </q-dialog>

  <!-- Import Dialog -->
  <JsonImportDialog v-model="showImportDialog" @import="handleImportJson" />
</template>

<style scoped>
.setup-dialog {
  width: 100%;
  max-width: 700px;
  height: 90vh;
  max-height: 800px;
  display: flex;
  flex-direction: column;
}
.setup-panels {
  flex: 1;
  overflow: auto;
}
</style>
