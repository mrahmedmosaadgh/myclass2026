<template>
  <!-- Floating Button -->
  <div class="fixed-bottom-right q-pa-lg" style="z-index: 2000">
    <q-btn fab icon="add" color="accent" @click="uiStore.openQuickCapture" />
  </div>

  <!-- Capture Dialog -->
  <q-dialog v-model="uiStore.quickCapture.isOpen" position="bottom">
    <q-card style="width: 100%; max-width: 500px">
      <q-card-section>
        <div class="text-subtitle1 text-weight-bold q-mb-sm">Quick Capture</div>
        <q-input
          v-model="captureText"
          outlined
          dense
          autofocus
          placeholder="New task or thought..."
          @keyup.enter="saveCapture('task')"
          class="q-mb-md"
        />
        
        <div class="row q-gutter-sm">
          <q-btn unelevated color="primary" label="Save Task" class="col" @click="saveCapture('task')" :loading="saving" />
          <q-btn outline color="secondary" label="Save Note" class="col" @click="saveCapture('note')" :loading="saving" />
        </div>
      </q-card-section>
    </q-card>
  </q-dialog>
</template>

<script setup>
import { ref } from 'vue'
import { useFgUiStore } from '../stores/fg-ui.store'
import { useFgTasksStore } from '../stores/fg-tasks.store'
import { useFgNotesStore } from '../stores/fg-notes.store'
import { useQuasar } from 'quasar'

const $q = useQuasar()
const uiStore = useFgUiStore()
const tasksStore = useFgTasksStore()
const notesStore = useFgNotesStore()

const captureText = ref('')
const saving = ref(false)

const saveCapture = async (type = 'task') => {
  if (!captureText.value.trim()) return
  
  saving.value = true
  try {
    if (type === 'task') {
      await tasksStore.createTask({
        title: captureText.value,
        source: 'quick_capture',
        status: 'inbox'
      })
    } else {
      await notesStore.createNote({
        body: captureText.value,
        source: 'quick_capture'
      })
    }
    
    $q.notify({
      type: 'positive',
      message: `Saved ${type} to inbox.`,
      position: 'top-right',
      timeout: 1500
    })
    
    captureText.value = ''
    uiStore.closeQuickCapture()
  } catch (err) {
    $q.notify({
      type: 'negative',
      message: 'Failed to capture.',
      position: 'top-right'
    })
  } finally {
    saving.value = false
  }
}
</script>
