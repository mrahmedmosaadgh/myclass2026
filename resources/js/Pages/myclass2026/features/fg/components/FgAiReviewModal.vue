<template>
  <q-dialog v-model="uiStore.aiModal.isOpen" maximized transition-show="slide-up" transition-hide="slide-down">
    <q-card class="column bg-white">
      <q-toolbar class="bg-primary text-white">
        <q-toolbar-title>Review Suggestions</q-toolbar-title>
        <q-btn flat round dense icon="close" v-close-popup @click="uiStore.closeAiModal()" />
      </q-toolbar>

      <q-card-section class="col q-pt-none scroll" v-if="parsedData">
        
        <!-- Tasks Section -->
        <div v-if="parsedData.tasks && parsedData.tasks.length" class="q-mt-md">
          <div class="text-h6 q-mb-sm text-primary">Extracted Tasks</div>
          <q-list bordered separator class="rounded-borders">
            <q-item v-for="(task, index) in parsedData.tasks" :key="`task-${index}`">
              <q-item-section>
                <q-input v-model="task.title" dense outlined label="Title" class="q-mb-xs" />
                <q-input v-model="task.notes" dense outlined label="Notes/Context" type="textarea" autogrow />
              </q-item-section>
              <q-item-section side>
                <q-btn flat round color="negative" icon="delete" @click="parsedData.tasks.splice(index, 1)" />
              </q-item-section>
            </q-item>
          </q-list>
        </div>

        <!-- Notes Section -->
        <div v-if="parsedData.notes && parsedData.notes.length" class="q-mt-lg">
          <div class="text-h6 q-mb-sm text-secondary">Saved Thoughts & Notes</div>
          <q-list bordered separator class="rounded-borders">
            <q-item v-for="(note, index) in parsedData.notes" :key="`note-${index}`">
              <q-item-section>
                <q-input v-model="note.body" dense outlined label="Note Body" type="textarea" autogrow />
              </q-item-section>
              <q-item-section side>
                <q-btn flat round color="negative" icon="delete" @click="parsedData.notes.splice(index, 1)" />
              </q-item-section>
            </q-item>
          </q-list>
        </div>
        
        <div v-if="isEmpty" class="text-center q-mt-xl text-grey-6">
          <q-icon name="sentiment_dissatisfied" size="4rem" />
          <p>No valid tasks or notes found. You might have deleted them all or AI missed something.</p>
        </div>
        
      </q-card-section>

      <q-card-actions align="right" class="bg-grey-1" v-if="!isEmpty">
        <q-btn flat label="Cancel" color="grey-7" v-close-popup @click="uiStore.closeAiModal()" />
        <q-btn unelevated label="Save All" color="primary" @click="saveAll" :loading="saving" />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useFgUiStore } from '../stores/fg-ui.store'
import { useFgTasksStore } from '../stores/fg-tasks.store'
import { useFgNotesStore } from '../stores/fg-notes.store'
import { useQuasar } from 'quasar'

const $q = useQuasar()
const uiStore = useFgUiStore()
const tasksStore = useFgTasksStore()
const notesStore = useFgNotesStore()

const parsedData = computed(() => uiStore.aiModal.parsedData)
const saving = ref(false)

const isEmpty = computed(() => {
  if (!parsedData.value) return true;
  return (!parsedData.value.tasks || parsedData.value.tasks.length === 0) &&
         (!parsedData.value.notes || parsedData.value.notes.length === 0)
})

const saveAll = async () => {
  saving.value = true
  try {
    const promises = []
    
    // Save tasks
    if (parsedData.value.tasks) {
      for (const t of parsedData.value.tasks) {
        if (t.title) {
          promises.push(tasksStore.createTask({
            title: t.title,
            notes: t.notes,
            source: 'ai_vent',
            status: 'inbox',
            importance: 0,
            tags: t.tags || []
          }))
        }
      }
    }
    
    // Save notes
    if (parsedData.value.notes) {
      for (const n of parsedData.value.notes) {
        if (n.body) {
          promises.push(notesStore.createNote({
            body: n.body,
            source: 'ai_vent',
            tags: n.tags || []
          }))
        }
      }
    }

    await Promise.all(promises)
    
    $q.notify({
      type: 'positive',
      message: 'Successfully saved to your inbox and notes.',
      position: 'top'
    })
    
    uiStore.closeAiModal()
  } catch (err) {
    $q.notify({
      type: 'negative',
      message: 'Error saving items. Please try again.',
      position: 'top'
    })
  } finally {
    saving.value = false
  }
}
</script>
