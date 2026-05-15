<script setup>
import { ref, onMounted } from 'vue'
import { useQuasar } from 'quasar'
import { usePresentationAPI } from '../composables/usePresentationAPI.js'

const props = defineProps({
  modelValue: Boolean,
  presentationId: Number,
})

const emit = defineEmits(['update:modelValue'])

const $q = useQuasar()
const { getStatistics, getAttemptHistory, loading, error } = usePresentationAPI()

const statistics = ref(null)
const attemptHistory = ref([])
const showHistory = ref(false)

async function loadStatistics() {
  try {
    statistics.value = await getStatistics(props.presentationId)
  } catch (err) {
    $q.notify({
      type: 'negative',
      message: error.value || 'Failed to load statistics',
      position: 'top',
      timeout: 3000
    })
  }
}

async function loadAttemptHistory() {
  try {
    attemptHistory.value = await getAttemptHistory(props.presentationId)
  } catch (err) {
    $q.notify({
      type: 'negative',
      message: error.value || 'Failed to load attempt history',
      position: 'top',
      timeout: 3000
    })
  }
}

function exportAttemptHistory() {
  const data = JSON.stringify(attemptHistory.value, null, 2)
  const blob = new Blob([data], { type: 'application/json' })
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = `attempt-history-${props.presentationId}.json`
  a.click()
  URL.revokeObjectURL(url)
}

onMounted(() => {
  loadStatistics()
})
</script>

<template>
  <q-dialog
    :model-value="modelValue"
    @update:model-value="emit('update:modelValue', $event)"
    maximized
  >
    <q-card class="statistics-dialog">
      <q-card-section class="bg-primary text-white">
        <div class="text-h6">Presentation Statistics</div>
      </q-card-section>

      <q-card-section class="q-pa-none">
        <q-list separator>
          <!-- Loading State -->
          <q-item v-if="loading">
            <q-item-section avatar>
              <q-spinner color="primary" />
            </q-item-section>
            <q-item-section>
              <q-item-label caption>Loading statistics...</q-item-label>
            </q-item-section>
          </q-item>

          <!-- Statistics -->
          <template v-else-if="statistics">
            <q-item>
              <q-item-section avatar>
                <q-icon name="people" color="blue-7" />
              </q-item-section>
              <q-item-section>
                <q-item-label>Total Attempts</q-item-label>
                <q-item-label caption>{{ statistics.total_attempts }}</q-item-label>
              </q-item-section>
            </q-item>

            <q-item>
              <q-item-section avatar>
                <q-icon name="person" color="green-7" />
              </q-item-section>
              <q-item-section>
                <q-item-label>Unique Students</q-item-label>
                <q-item-label caption>{{ statistics.unique_students }}</q-item-label>
              </q-item-section>
            </q-item>

            <q-item>
              <q-item-section avatar>
                <q-icon name="trending_up" color="orange-7" />
              </q-item-section>
              <q-item-section>
                <q-item-label>Average Score</q-item-label>
                <q-item-label caption>{{ statistics.average_score }}%</q-item-label>
              </q-item-section>
            </q-item>

            <q-item>
              <q-item-section avatar>
                <q-icon name="star" color="yellow-7" />
              </q-item-section>
              <q-item-section>
                <q-item-label>High Score</q-item-label>
                <q-item-label caption>{{ statistics.high_score }}%</q-item-label>
              </q-item-section>
            </q-item>

            <q-item>
              <q-item-section avatar>
                <q-icon name="arrow_downward" color="red-7" />
              </q-item-section>
              <q-item-section>
                <q-item-label>Low Score</q-item-label>
                <q-item-label caption>{{ statistics.low_score }}%</q-item-label>
              </q-item-section>
            </q-item>

            <!-- Score Distribution -->
            <q-item>
              <q-item-section>
                <q-item-label class="text-subtitle2">Score Distribution</q-item-label>
                <div class="q-mt-sm">
                  <div v-for="(count, range) in statistics.score_distribution" :key="range" class="row items-center q-mb-xs">
                    <div class="col-3 text-caption">{{ range }}:</div>
                    <div class="col-6">
                      <q-linear-progress
                        :value="statistics.total_attempts > 0 ? count / statistics.total_attempts : 0"
                        color="primary"
                        size="8px"
                      />
                    </div>
                    <div class="col-3 text-caption text-right">{{ count }}</div>
                  </div>
                </div>
              </q-item-section>
            </q-item>

            <!-- Attempt History Button -->
            <q-item clickable @click="loadAttemptHistory">
              <q-item-section avatar>
                <q-icon name="history" color="purple-7" />
              </q-item-section>
              <q-item-section>
                <q-item-label>View Attempt History</q-item-label>
                <q-item-label caption>View detailed student attempts</q-item-label>
              </q-item-section>
              <q-item-section side>
                <q-icon name="chevron_right" />
              </q-item-section>
            </q-item>
          </template>
        </q-list>

        <!-- Attempt History -->
        <q-separator v-if="showHistory" />
        <q-card-section v-if="showHistory" class="bg-grey-1">
          <div class="row items-center justify-between q-mb-md">
            <div class="text-subtitle2">Attempt History</div>
            <q-btn
              flat
              dense
              label="Export JSON"
              icon="download"
              color="primary"
              @click="exportAttemptHistory"
            />
          </div>

          <q-list separator bordered class="bg-white rounded-borders">
            <q-item v-if="attemptHistory.length === 0">
              <q-item-section>
                <q-item-label caption class="text-center text-grey-6">
                  No attempts recorded yet
                </q-item-label>
              </q-item-section>
            </q-item>

            <q-item
              v-for="attempt in attemptHistory"
              :key="attempt.id"
            >
              <q-item-section>
                <q-item-label>{{ attempt.student_identifier }}</q-item-label>
                <q-item-label caption>
                  Score: {{ attempt.total_score }} / {{ attempt.total_questions }} • 
                  {{ new Date(attempt.completed_at).toLocaleString() }}
                </q-item-label>
              </q-item-section>
              <q-item-section side>
                <q-badge
                  :color="attempt.total_score / attempt.total_questions >= 0.7 ? 'green' : attempt.total_score / attempt.total_questions >= 0.5 ? 'orange' : 'red'"
                >
                  {{ Math.round((attempt.total_score / attempt.total_questions) * 100) }}%
                </q-badge>
              </q-item-section>
            </q-item>
          </q-list>
        </q-card-section>
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
</template>

<style scoped>
.statistics-dialog {
  max-width: 600px;
}
</style>
