<template>
  <q-dialog v-model="uiStore.quickCapture.isOpen" position="bottom" full-width>
    <q-card class="fg-v2-capture-sheet" style="border-radius: 20px 20px 0 0">
      <!-- Handle bar -->
      <div class="row justify-center q-pt-md q-pb-sm">
        <div style="width: 40px; height: 4px; border-radius: 2px; background: #ddd" />
      </div>

      <q-card-section class="q-pb-sm">
        <div class="text-h6 text-weight-bold text-dark q-mb-md">Quick Capture</div>
        <q-input
          v-model="captureText"
          outlined
          autofocus
          placeholder="What's on your mind?"
          input-class="text-body1"
          @keyup.enter="saveCapture('task')"
          class="q-mb-md"
          style="font-size: 16px"
        />
      </q-card-section>

      <q-card-section class="q-pt-none q-pb-xl">
        <div class="column q-gutter-sm">
          <q-btn
            unelevated
            color="primary"
            label="Save as Task"
            icon="check_circle"
            size="lg"
            style="min-height: 56px; border-radius: 16px; font-size: 16px"
            :loading="saving"
            @click="saveCapture('task')"
          />
          <q-btn
            outline
            color="secondary"
            label="Save as Note"
            icon="edit_note"
            size="lg"
            style="min-height: 56px; border-radius: 16px; font-size: 16px"
            :loading="saving"
            @click="saveCapture('note')"
          />
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
import { Notify } from 'quasar'

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

    Notify.create({
      type: 'positive',
      message: `Saved ${type} to inbox.`,
      position: 'top-right',
      timeout: 1500
    })

    captureText.value = ''
    uiStore.closeQuickCapture()
  } catch {
    Notify.create({
      type: 'negative',
      message: 'Failed to capture.',
      position: 'top-right'
    })
  } finally {
    saving.value = false
  }
}
</script>
