<template>
  <div class="exam-file-manager">
    <!-- Save Button -->
    <q-btn
      v-if="showSaveButton"
      flat
      :color="buttonColor"
      icon="save"
      :label="saveLabel"
      @click="saveExam"
    />

    <!-- Manage Files Button -->
    <q-btn
      v-if="showManageButton"
      flat
      :color="buttonColor"
      icon="folder_open"
      :label="manageLabel"
      @click="openDialog"
    />

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
  }
})

const emit = defineEmits(['save', 'load', 'delete', 'refresh'])

const page = usePage()
const dialogOpen = ref(false)
const savedFiles = ref([])
const loading = ref(false)

function openDialog() {
  dialogOpen.value = true
  refreshFiles()
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
      alert('Failed to load saved files: ' + (result.message || 'Unknown error'))
      savedFiles.value = []
    }
  } catch (e) {
    console.error('Failed to load saved files', e)
    alert('Failed to load saved files: ' + e.message)
    savedFiles.value = []
  } finally {
    loading.value = false
  }
}

async function saveExam(data) {
  emit('save', data)
}

async function loadFile(fileId) {
  if (!confirm('Are you sure you want to load this file? Current unsaved changes will be lost.')) {
    return
  }

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
      alert('Exam loaded successfully!')
    } else {
      alert('Failed to load exam: ' + (result.message || 'Unknown error'))
    }
  } catch (e) {
    console.error('Failed to load exam', e)
    alert('Failed to load exam: ' + e.message)
  } finally {
    loading.value = false
  }
}

async function deleteFile(fileId) {
  if (!confirm('Are you sure you want to delete this file? This action cannot be undone.')) {
    return
  }

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
      alert('File deleted successfully!')
      emit('delete', fileId)
      refreshFiles()
    } else {
      alert('Failed to delete file: ' + (result.message || 'Unknown error'))
    }
  } catch (e) {
    console.error('Failed to delete file', e)
    alert('Failed to delete file: ' + e.message)
  } finally {
    loading.value = false
  }
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
