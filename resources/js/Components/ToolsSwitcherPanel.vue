<template>
  <!-- Trigger Button -->
  <q-btn
    round
    color="grey-7"
    icon="settings"
    size="sm"
    @click="showDialog = true"
  >
    <q-tooltip>Tools Switcher</q-tooltip>
  </q-btn>

  <!-- Dialog -->
  <q-dialog v-model="showDialog" persistent>
    <q-card class="tools-switcher-panel" style="min-width: 400px">
      <q-card-section>
        <div class="text-h6">🛠️ Tools Switcher</div>
        <div class="text-caption">Control system features for better performance</div>
      </q-card-section>

      <q-card-section>
        <!-- Firebase Controls -->
        <q-expansion-item label="Firebase" icon="local_fire_department">
          <q-toggle
            v-model="config.firebase.enabled"
            label="Enable Firebase"
            @update:model-value="saveConfig"
          />
          <q-toggle
            v-model="config.firebase.auth"
            label="Authentication"
            :disable="!config.firebase.enabled"
            @update:model-value="saveConfig"
          />
          <q-toggle
            v-model="config.firebase.database"
            label="Realtime Database"
            :disable="!config.firebase.enabled"
            @update:model-value="saveConfig"
          />
          <q-toggle
            v-model="config.firebase.emulators"
            label="Use Emulators (Dev)"
            :disable="!config.firebase.enabled"
            @update:model-value="saveConfig"
          />
          <q-toggle
            v-model="config.firebase.notifications"
            label="Firebase Notifications"
            :disable="!config.firebase.enabled"
            @update:model-value="saveConfig"
          />
        </q-expansion-item>

        <!-- Background Services -->
        <q-expansion-item label="Background Services" icon="settings_applications">
          <q-toggle
            v-model="config.backgroundServices.notifications"
            label="Background Notifications"
            @update:model-value="saveConfig"
          />
          <q-toggle
            v-model="config.backgroundServices.sync"
            label="Background Sync"
            @update:model-value="saveConfig"
          />
          <q-toggle
            v-model="config.backgroundServices.realtime"
            label="Realtime Updates"
            @update:model-value="saveConfig"
          />
        </q-expansion-item>
      </q-card-section>

      <q-card-actions align="right">
        <q-btn flat color="warning" @click="resetToDefaults">Reset</q-btn>
        <q-btn flat color="primary" @click="reloadPage">Apply & Reload</q-btn>
        <q-btn flat color="grey" @click="showDialog = false">Close</q-btn>
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { ToolsSwitcher } from '@/Utils/toolsSwitcher';
import { useQuasar } from 'quasar';

const $q = useQuasar();
const config = ref({
  firebase: {
    enabled: true,
    auth: true,
    database: true,
    emulators: true,
    notifications: true
  },
  backgroundServices: {
    notifications: true,
    sync: true,
    realtime: true
  }
});

const showDialog = ref(false);

onMounted(() => {
  config.value = ToolsSwitcher.getConfig();
});

const saveConfig = () => {
  ToolsSwitcher.saveConfig(config.value);
  $q.notify({
    message: 'Configuration saved',
    color: 'positive',
    timeout: 1000
  });
};

const resetToDefaults = () => {
  localStorage.removeItem('toolsSwitcher');
  config.value = ToolsSwitcher.getConfig();
  $q.notify({
    message: 'Reset to defaults',
    color: 'info'
  });
};

const reloadPage = () => {
  window.location.reload();
};
</script>




