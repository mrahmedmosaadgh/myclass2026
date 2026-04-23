<template>
  <div class="exam-file-manager">
    <!-- Create New Exam Button -->
    <q-btn
      v-if="showSaveButton"
      flat
      round
      dense
      :color="buttonColor"
      icon="add_circle"
      @click="createNewExam"
    >
      <q-tooltip>Create New Exam</q-tooltip>
    </q-btn>

    <!-- Save Button -->
    <q-btn
      v-if="showSaveButton"
      flat
      round
      dense
      :color="buttonColor"
      icon="save"
      @click="saveExam"
    >
      <q-tooltip>Save Exam</q-tooltip>
    </q-btn>

    <!-- Auto-save Toggle -->
    <q-btn
      v-if="showSaveButton"
      flat
      round
      dense
      :color="autoSaveEnabled ? 'positive' : buttonColor"
      :icon="autoSaveEnabled ? 'autorenew' : 'save_off'"
      @click="toggleAutoSave"
    >
      <q-tooltip>{{ autoSaveEnabled ? 'Auto-save ON' : 'Auto-save OFF' }}</q-tooltip>
    </q-btn>

    <!-- Manage Files Button -->
    <q-btn
      v-if="showManageButton"
      flat
      round
      dense
      :color="buttonColor"
      icon="folder_open"
      @click="openDialog"
    >
      <q-tooltip>{{ manageLabel }}</q-tooltip>
    </q-btn>

    <!-- Saved Files Dialog -->
    <q-dialog v-model="dialogOpen">
      <q-card style="min-width: 600px; max-width: 95vw;">
        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6">{{ dialogTitle }}</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>
        <q-separator />

        <q-card-section>
          <div v-if="loading" class="text-center q-pa-lg">
            <q-spinner color="primary" size="3em" />
            <div class="q-mt-md">{{ loadingText }}</div>
          </div>

          <div v-else-if="savedFiles.length === 0" class="text-center q-pa-lg text-grey">
            <q-icon name="folder_open" size="64px" color="grey-4" />
            <div class="q-mt-md">{{ emptyText }}</div>
          </div>

          <q-list v-else separator bordered>
            <q-item v-for="file in savedFiles" :key="file.id" class="q-pa-md">
              <q-item-section avatar>
                <q-icon name="description" color="primary" size="32px" />
              </q-item-section>
              <q-item-section>
                <q-item-label>{{ file.name }}</q-item-label>
                <q-item-label caption>
                  {{ formatDate(file.created_at) }} • {{ file.questions_count }} questions
                </q-item-label>
              </q-item-section>
              <q-item-section side>
                <div class="row q-gutter-xs">
                  <q-btn
                    flat
                    round
                    dense
                    color="primary"
                    icon="folder_open"
                    @click="loadFile(file.id)"
                    title="Load this file"
                  />
                  <q-btn
                    flat
                    round
                    dense
                    color="negative"
                    icon="delete"
                    @click="deleteFile(file.id)"
                    title="Delete this file"
                  />
                </div>
              </q-item-section>
            </q-item>
          </q-list>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat color="primary" @click="refreshFiles" :loading="loading">
            <q-icon name="refresh" class="q-mr-xs" />
            Refresh
          </q-btn>
          <q-btn flat color="grey" v-close-popup>Close</q-btn>
        </q-card-actions>
      </q-card>
    </q-dialog>

  </div>
</template>

<script setup>
import { ref } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { useQuasar } from 'quasar'

const $q = useQuasar()

const props = defineProps({
  // Button appearance
  showSaveButton: {
    type: Boolean,
    default: true
  },
  showManageButton: {
    type: Boolean,
    default: true
  },
  buttonColor: {
    type: String,
    default: 'white'
  },
  saveLabel: {
    type: String,
    default: 'Save'
  },
  manageLabel: {
    type: String,
    default: 'Manage Files'
  },
  // Dialog appearance
  dialogTitle: {
    type: String,
    default: 'Manage Saved Files'
  },
  loadingText: {
    type: String,
    default: 'Loading saved files...'
  },
  emptyText: {
    type: String,
    default: 'No saved files found'
  },
  // API endpoints
  saveEndpoint: {
    type: String,
    default: '/api/exam/ready-to-print/save-exam'
  },
  listEndpoint: {
    type: String,
    default: '/api/exam/ready-to-print/list-saved-exams'
  },
  loadEndpoint: {
    type: String,
    default: '/api/exam/ready-to-print/load-saved-exam'
  },
  deleteEndpoint: {
    type: String,
    default: '/api/exam/ready-to-print/delete-saved-exam'
  },
  hasUnsavedChanges: {
    type: Boolean,
    default: false
  },
  autoSaveEnabled: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['save', 'load', 'delete', 'refresh', 'saveAs', 'createNew', 'toggleAutoSave'])

const page = usePage()
const dialogOpen = ref(false)
const savedFiles = ref([])
const loading = ref(false)

function openDialog() {
  dialogOpen.value = true
  refreshFiles()
}

function createNewExam() {
  emit('createNew')
}

function toggleAutoSave() {
  emit('toggleAutoSave')
}

function saveExam() {
  emit('save')
}

async function refreshFiles() {
  loading.value = true
  try {
    const response = await fetch(props.listEndpoint, {
      method: 'GET',
      headers: {
        'Accept': 'application/json',
        'X-CSRF-TOKEN': page.props.csrf_token || ''
      }
    })

    const result = await response.json()

    if (response.ok) {
      savedFiles.value = result.files || []
      emit('refresh', savedFiles.value)
    } else {
      $q.notify({ type: 'negative', message: 'Failed to load saved files: ' + (result.message || 'Unknown error'), position: 'top' })
      savedFiles.value = []
    }
  } catch (e) {
    console.error('Failed to load saved files', e)
    $q.notify({ type: 'negative', message: 'Failed to load saved files: ' + e.message, position: 'top' })
    savedFiles.value = []
  } finally {
    loading.value = false
  }
}

async function loadFile(fileId) {
  $q.dialog({
    title: 'Load Exam',
    message: 'Are you sure you want to load this file? Current unsaved changes will be lost.',
    cancel: true,
    persistent: true
  }).onOk(async () => {
    loading.value = true
    try {
      const response = await fetch(`${props.loadEndpoint}/${fileId}`, {
        method: 'GET',
        headers: {
          'Accept': 'application/json',
          'X-CSRF-TOKEN': page.props.csrf_token || ''
        }
      })

      const result = await response.json()

      if (response.ok) {
        emit('load', result.data)
        dialogOpen.value = false
        $q.notify({ type: 'positive', message: 'Exam loaded successfully!', position: 'top' })
      } else {
        $q.notify({ type: 'negative', message: 'Failed to load exam: ' + (result.message || 'Unknown error'), position: 'top' })
      }
    } catch (e) {
      console.error('Failed to load exam', e)
      $q.notify({ type: 'negative', message: 'Failed to load exam: ' + e.message, position: 'top' })
    } finally {
      loading.value = false
    }
  })
}

async function deleteFile(fileId) {
  $q.dialog({
    title: 'Delete Exam',
    message: 'Are you sure you want to delete this file? This action cannot be undone.',
    cancel: true,
    persistent: true
  }).onOk(async () => {
    loading.value = true
    try {
      const response = await fetch(`${props.deleteEndpoint}/${fileId}`, {
        method: 'DELETE',
        headers: {
          'Accept': 'application/json',
          'X-CSRF-TOKEN': page.props.csrf_token || ''
        }
      })

      const result = await response.json()

      if (response.ok) {
        $q.notify({ type: 'positive', message: 'File deleted successfully!', position: 'top' })
        emit('delete', fileId)
        refreshFiles()
      } else {
        $q.notify({ type: 'negative', message: 'Failed to delete file: ' + (result.message || 'Unknown error'), position: 'top' })
      }
    } catch (e) {
      console.error('Failed to delete file', e)
      $q.notify({ type: 'negative', message: 'Failed to delete file: ' + e.message, position: 'top' })
    } finally {
      loading.value = false
    }
  })
}

function formatDate(dateString) {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

// Expose methods for parent component
defineExpose({
  saveExam,
  refreshFiles,
  openDialog
})
</script>

<style scoped>
.exam-file-manager {
  display: inline-flex;
  gap: 8px;
}
</style>
