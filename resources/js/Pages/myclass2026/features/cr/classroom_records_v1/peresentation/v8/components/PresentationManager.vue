<script setup>
import { ref, onMounted } from 'vue'
import { useQuasar } from 'quasar'
import { usePresentationAPI } from '../composables/usePresentationAPI.js'
import PresentationStatistics from './PresentationStatistics.vue'

const props = defineProps({
  modelValue: Boolean,
})

const emit = defineEmits(['update:modelValue', 'load'])

const $q = useQuasar()
const { listPresentations, deletePresentation, loading, error } = usePresentationAPI()

const presentations = ref([])
const selectedPresentationId = ref(null)
const showStatistics = ref(false)
const selectedForStatistics = ref(null)

async function loadPresentations() {
  try {
    presentations.value = await listPresentations()
  } catch (err) {
    $q.notify({
      type: 'negative',
      message: error.value || 'Failed to load presentations',
      position: 'top',
      timeout: 3000
    })
  }
}

async function handleDelete(id) {
  $q.dialog({
    title: 'Delete Presentation',
    message: 'Are you sure you want to delete this presentation? This action cannot be undone.',
    cancel: true,
    persistent: true,
  }).onOk(async () => {
    try {
      await deletePresentation(id)
      $q.notify({
        type: 'positive',
        message: 'Presentation deleted successfully',
        position: 'top',
        timeout: 3000
      })
      await loadPresentations()
    } catch (err) {
      $q.notify({
        type: 'negative',
        message: error.value || 'Failed to delete presentation',
        position: 'top',
        timeout: 3000
      })
    }
  })
}

function handleLoad(presentation) {
  emit('load', presentation)
  emit('update:modelValue', false)
}

function copyShareLink(shareUrl) {
  navigator.clipboard.writeText(shareUrl)
    .then(() => {
      $q.notify({
        type: 'positive',
        message: 'Share link copied to clipboard',
        position: 'top',
        timeout: 3000
      })
    })
    .catch(() => {
      $q.notify({
        type: 'warning',
        message: 'Failed to copy link',
        position: 'top',
        timeout: 3000
      })
    })
}

function showStatisticsDialog(presentation) {
  selectedForStatistics.value = presentation
  showStatistics.value = true
}

onMounted(() => {
  loadPresentations()
})
</script>

<template>
  <q-dialog
    :model-value="modelValue"
    @update:model-value="emit('update:modelValue', $event)"
    maximized
  >
    <q-card class="presentation-manager">
      <q-card-section class="bg-primary text-white">
        <div class="text-h6">My Presentations</div>
      </q-card-section>

      <q-card-section class="q-pa-none">
        <q-list separator>
          <q-item v-if="loading">
            <q-item-section avatar>
              <q-spinner color="primary" />
            </q-item-section>
            <q-item-section>
              <q-item-label caption>Loading presentations...</q-item-label>
            </q-item-section>
          </q-item>

          <q-item v-else-if="presentations.length === 0">
            <q-item-section>
              <q-item-label caption class="text-center text-grey-6">
                No saved presentations yet
              </q-item-label>
            </q-item-section>
          </q-item>

          <q-item
            v-for="presentation in presentations"
            :key="presentation.id"
            clickable
            @click="selectedPresentationId = presentation.id"
            :class="{ 'bg-blue-1': selectedPresentationId === presentation.id }"
          >
            <q-item-section avatar>
              <q-icon name="presentation" color="primary" />
            </q-item-section>

            <q-item-section>
              <q-item-label>{{ presentation.title }}</q-item-label>
              <q-item-label caption>
                {{ presentation.description || 'No description' }}
              </q-item-label>
              <q-item-label caption class="text-grey-7">
                {{ new Date(presentation.created_at).toLocaleDateString() }} • 
                {{ presentation.attempt_count }} attempts
              </q-item-label>
            </q-item-section>

            <q-item-section side>
              <div class="row q-gutter-xs">
                <q-btn
                  flat
                  round
                  dense
                  icon="content_copy"
                  color="blue-7"
                  @click.stop="copyShareLink(presentation.share_url)"
                >
                  <q-tooltip>Copy Share Link</q-tooltip>
                </q-btn>
                <q-btn
                  flat
                  round
                  dense
                  icon="assessment"
                  color="green-7"
                  @click.stop="showStatisticsDialog(presentation)"
                >
                  <q-tooltip>Statistics</q-tooltip>
                </q-btn>
                <q-btn
                  flat
                  round
                  dense
                  icon="folder_open"
                  color="primary"
                  @click.stop="handleLoad(presentation)"
                >
                  <q-tooltip>Load</q-tooltip>
                </q-btn>
                <q-btn
                  flat
                  round
                  dense
                  icon="delete"
                  color="red"
                  @click.stop="handleDelete(presentation.id)"
                >
                  <q-tooltip>Delete</q-tooltip>
                </q-btn>
              </div>
            </q-item-section>
          </q-item>
        </q-list>
      </q-card-section>

      <q-card-actions align="right">
        <q-btn
          flat
          label="Close"
          color="grey-7"
          @click="emit('update:modelValue', false)"
        />
      </q-card-actions>
    </q-card>
  </q-dialog>

  <PresentationStatistics
    v-if="selectedForStatistics"
    v-model="showStatistics"
    :presentation-id="selectedForStatistics.id"
  />
</template>

<style scoped>
.presentation-manager {
  max-width: 800px;
}
</style>
