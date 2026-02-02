<template>
  <div class="bulk-operations">
    <!-- Multi-select Mode Toggle -->
    <div class="bulk-operations__header q-mb-md">
      <q-btn
        :color="bulkState.multiSelectMode ? 'secondary' : 'grey-6'"
        :icon="bulkState.multiSelectMode ? 'check_box' : 'check_box_outline_blank'"
        :label="bulkState.multiSelectMode ? 'Exit Multi-Select' : 'Multi-Select'"
        rounded
        :outline="!bulkState.multiSelectMode"
        size="sm"
        class="text-weight-medium"
        @click="toggleMultiSelect"
      />
    </div>

    <!-- Multi-select Controls (shown when multi-select is active) -->
    <div v-if="bulkState.multiSelectMode" class="bulk-operations__controls q-mb-md">
      <div class="row q-gutter-sm">
        <!-- Select All Toggle -->
        <q-btn
          :color="bulkState.selectAllActive ? 'primary' : 'grey-6'"
          :icon="bulkState.selectAllActive ? 'select_all' : 'deselect'"
          :label="bulkState.selectAllActive ? 'Deselect All' : 'Select All'"
          size="sm"
          rounded
          outline
          class="text-weight-medium"
          @click="handleSelectAll"
        />

        <!-- Selection Count -->
        <q-chip
          v-if="selectionStats.selectedCount > 0"
          :label="`${selectionStats.selectedCount} selected`"
          color="primary"
          text-color="white"
          size="sm"
        />
      </div>

      <!-- Batch Actions (shown when questions are selected) -->
      <div v-if="selectionStats.selectedCount > 0" class="bulk-operations__batch-actions q-mt-sm">
        <q-btn
          color="positive"
          icon="add_circle"
          :label="`Add ${selectionStats.selectedCount} Selected`"
          rounded
          size="sm"
          class="text-weight-medium"
          @click="handleAddSelected"
        />
      </div>
    </div>

    <!-- Quick Actions (always visible) -->
    <div class="bulk-operations__quick-actions">
      <div class="row q-gutter-sm">
        <!-- Add All Filtered -->
        <q-btn
          color="secondary"
          icon="playlist_add"
          :label="`Add All (${filteredQuestions.length})`"
          rounded
          size="sm"
          class="text-weight-medium"
          :disable="filteredQuestions.length === 0"
          @click="handleAddAllFiltered"
        />

        <!-- Remove All (shown when there are selected questions in quiz) -->
        <q-btn
          v-if="selectedQuestions.length > 0"
          color="negative"
          icon="clear_all"
          :label="`Remove All (${selectedQuestions.length})`"
          rounded
          outline
          size="sm"
          class="text-weight-medium"
          @click="handleRemoveAll"
        />
      </div>
    </div>

    <!-- Statistics Display -->
    <div v-if="filteredQuestions.length > 0" class="bulk-operations__stats q-mt-md">
      <q-card flat bordered class="bg-grey-1">
        <q-card-section class="q-pa-sm">
          <div class="text-caption text-grey-7 text-weight-medium">
            Pool: {{ filteredQuestions.length }} questions
            <span v-if="bulkState.multiSelectMode && selectionStats.selectedCount > 0">
              • {{ selectionStats.selectedCount }} selected
            </span>
          </div>
        </q-card-section>
      </q-card>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useQuasar } from 'quasar'
import type { QuizQuestion, BulkOperationState } from '@/types/quiz-builder'

// Props
interface Props {
  filteredQuestions: QuizQuestion[]
  selectedQuestions: QuizQuestion[]
  bulkState: BulkOperationState
}

const props = defineProps<Props>()

// Emits
interface Emits {
  (e: 'add-all-filtered'): void
  (e: 'add-selected', questions: QuizQuestion[]): void
  (e: 'toggle-multi-select', enabled: boolean): void
  (e: 'clear-selection'): void
  (e: 'select-all-filtered', questions: QuizQuestion[]): void
  (e: 'remove-all'): void
}

const emit = defineEmits<Emits>()

// Composables
const $q = useQuasar()

// Computed
const selectionStats = computed(() => ({
  selectedCount: props.bulkState.selectedQuestionIds.length,
  multiSelectActive: props.bulkState.multiSelectMode,
  selectAllActive: props.bulkState.selectAllActive
}))

const selectedQuestionsFromPool = computed(() => {
  return props.filteredQuestions.filter(q => 
    props.bulkState.selectedQuestionIds.includes(q.id.toString())
  )
})

// Methods
const toggleMultiSelect = () => {
  emit('toggle-multi-select', !props.bulkState.multiSelectMode)
}

const handleSelectAll = () => {
  emit('select-all-filtered', props.filteredQuestions)
}

const handleAddSelected = () => {
  if (selectedQuestionsFromPool.value.length === 0) {
    $q.notify({
      type: 'warning',
      message: 'No questions selected',
      icon: 'warning'
    })
    return
  }

  // Show confirmation dialog
  $q.dialog({
    title: 'Add Selected Questions',
    message: `Add ${selectedQuestionsFromPool.value.length} selected questions to the quiz?`,
    cancel: true,
    persistent: true,
    class: 'rounded-xl'
  }).onOk(() => {
    emit('add-selected', selectedQuestionsFromPool.value)
    
    $q.notify({
      type: 'positive',
      message: `Added ${selectedQuestionsFromPool.value.length} questions to quiz`,
      icon: 'check_circle'
    })
  })
}

const handleAddAllFiltered = () => {
  if (props.filteredQuestions.length === 0) {
    $q.notify({
      type: 'warning',
      message: 'No questions available to add',
      icon: 'warning'
    })
    return
  }

  // Show confirmation dialog with details
  const message = `Add all ${props.filteredQuestions.length} filtered questions to the quiz?`
  const details = props.filteredQuestions.length > 10 
    ? 'This will add a large number of questions. You can review and remove individual questions later if needed.'
    : ''

  $q.dialog({
    title: 'Add All Filtered Questions',
    message,
    html: details ? `<p class="q-mt-sm text-grey-7">${details}</p>` : false,
    cancel: true,
    persistent: true,
    class: 'rounded-xl'
  }).onOk(() => {
    emit('add-all-filtered')
    
    $q.notify({
      type: 'positive',
      message: `Added ${props.filteredQuestions.length} questions to quiz`,
      icon: 'check_circle'
    })
  })
}

const handleRemoveAll = () => {
  if (props.selectedQuestions.length === 0) {
    return
  }

  // Enhanced confirmation dialog with warning
  $q.dialog({
    title: 'Remove All Questions',
    message: `Remove all ${props.selectedQuestions.length} questions from the quiz?`,
    html: '<p class="q-mt-sm text-negative"><strong>Warning:</strong> This action cannot be undone. All questions will be removed from the quiz.</p>',
    persistent: true,
    class: 'rounded-xl',
    ok: {
      label: 'Remove All',
      color: 'negative',
      push: true
    },
    cancel: {
      label: 'Cancel',
      color: 'grey-6',
      flat: true
    }
  }).onOk(() => {
    emit('remove-all')
    
    $q.notify({
      type: 'info',
      message: `Removed ${props.selectedQuestions.length} questions from quiz`,
      icon: 'info'
    })
  })
}
</script>

<style scoped lang="scss">
.bulk-operations {
  &__header {
    border-bottom: 1px solid rgba(0, 0, 0, 0.12);
    padding-bottom: 8px;
  }

  &__controls {
    background: rgba(25, 118, 210, 0.04);
    border-radius: 8px;
    padding: 12px;
    border: 1px solid rgba(25, 118, 210, 0.12);
  }

  &__batch-actions {
    padding-top: 8px;
    border-top: 1px solid rgba(25, 118, 210, 0.12);
  }

  &__quick-actions {
    .q-btn {
      transition: all 0.2s ease;
      
      &:hover:not(.disabled) {
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.12);
      }
    }
  }

  &__stats {
    .q-card {
      border-radius: 8px;
    }
  }
}

// Animation for smooth transitions
.bulk-operations__controls {
  animation: slideDown 0.3s ease-out;
}

@keyframes slideDown {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>