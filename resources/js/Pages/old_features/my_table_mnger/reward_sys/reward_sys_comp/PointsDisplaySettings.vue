<template>
  <q-card class="points-display-settings">
    <q-card-section class="bg-primary text-white">
      <div class="text-h6 flex items-center gap-2">
        <q-icon name="tune" />
        Points Display Settings
      </div>
    </q-card-section>

    <q-card-section class="q-gutter-md">
      <!-- View Mode Selection -->
      <div>
        <div class="text-subtitle2 text-weight-bold q-mb-sm">View Mode</div>
        
        <q-option-group
          v-model="localMode"
          :options="modeOptions"
          color="primary"
          @update:model-value="$emit('update:mode', $event)"
        />

        <!-- Competition Mode Controls -->
        <div v-if="localMode === 'competition'" class="q-mt-md q-ml-lg">
          <div v-if="!competitionActive" class="q-gutter-sm">
            <q-btn
              color="positive"
              icon="flag"
              label="🏁 Start Competition"
              @click="startCompetition"
              unelevated
            />
          </div>
          
          <div v-else class="q-gutter-sm">
            <div class="text-caption text-grey-7">
              Started: {{ formatTime(competitionStartTime) }}
            </div>
            <div class="text-h6 text-positive">
              Timer: {{ competitionTimer }}
            </div>
            <q-btn
              color="negative"
              icon="stop"
              label="End Competition"
              @click="endCompetition"
              outline
              size="sm"
            />
          </div>
        </div>

        <!-- Custom Date Range -->
        <div v-if="localMode === 'custom'" class="q-mt-md q-ml-lg q-gutter-sm">
          <q-input
            v-model="localDateFrom"
            type="date"
            label="From Date *"
            dense
            outlined
            @update:model-value="$emit('update:dateFrom', $event)"
          >
            <template v-slot:prepend>
              <q-icon name="event" />
            </template>
          </q-input>
          
          <q-input
            v-model="localDateTo"
            type="date"
            label="To Date (optional - leave empty for 'till now')"
            dense
            outlined
            @update:model-value="$emit('update:dateTo', $event)"
          >
            <template v-slot:prepend>
              <q-icon name="event" />
            </template>
          </q-input>
        </div>
      </div>

      <q-separator />

      <!-- Leaderboard Display -->
      <div>
        <div class="text-subtitle2 text-weight-bold q-mb-sm">Leaderboard Display</div>
        
        <q-option-group
          v-model="localLeaderboardMode"
          :options="leaderboardOptions"
          color="secondary"
          @update:model-value="$emit('update:leaderboardMode', $event)"
        />
      </div>
    </q-card-section>

    <q-card-actions align="right">
      <q-btn flat label="Reset to Normal" color="grey" @click="resetToNormal" />
      <q-btn unelevated label="Apply" color="primary" @click="apply" />
    </q-card-actions>
  </q-card>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'

const props = defineProps({
  mode: { type: String, default: 'overall' },
  competitionStartTime: { type: String, default: null },
  dateFrom: { type: String, default: null },
  dateTo: { type: String, default: null },
  leaderboardMode: { type: String, default: 'top5' }
})

const emit = defineEmits([
  'update:mode',
  'update:competitionStartTime',
  'update:dateFrom',
  'update:dateTo',
  'update:leaderboardMode',
  'apply',
  'reset'
])

const localMode = ref(props.mode)
const localDateFrom = ref(props.dateFrom)
const localDateTo = ref(props.dateTo)
const localLeaderboardMode = ref(props.leaderboardMode)
const competitionTimer = ref('00:00')
let timerInterval = null

const modeOptions = [
  { label: 'Overall Total (All Time)', value: 'overall' },
  { label: 'Current Session (Today)', value: 'session' },
  { label: 'Competition Mode', value: 'competition' },
  { label: 'Custom Date Range', value: 'custom' }
]

const leaderboardOptions = [
  { label: 'Top 5 Students', value: 'top5' },
  { label: 'Top 10 Students', value: 'top10' },
  { label: 'Winner Groups (by total points)', value: 'groups' }
]

const competitionActive = computed(() => {
  return localMode.value === 'competition' && props.competitionStartTime
})

const formatTime = (isoString) => {
  if (!isoString) return ''
  const date = new Date(isoString)
  return date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })
}

const updateTimer = () => {
  if (!props.competitionStartTime) return
  
  const start = new Date(props.competitionStartTime)
  const now = new Date()
  const diff = Math.floor((now - start) / 1000) // seconds
  
  const minutes = Math.floor(diff / 60)
  const seconds = diff % 60
  
  competitionTimer.value = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`
}

const startCompetition = () => {
  const now = new Date().toISOString()
  emit('update:competitionStartTime', now)
  
  // Start timer
  timerInterval = setInterval(updateTimer, 1000)
}

const endCompetition = () => {
  emit('update:competitionStartTime', null)
  emit('update:mode', 'overall')
  localMode.value = 'overall'
  
  // Stop timer
  if (timerInterval) {
    clearInterval(timerInterval)
    timerInterval = null
  }
  competitionTimer.value = '00:00'
}

const resetToNormal = () => {
  emit('reset')
}

const apply = () => {
  emit('apply')
}

// Watch for competition mode changes
watch(() => props.competitionStartTime, (newVal) => {
  if (newVal && localMode.value === 'competition') {
    updateTimer()
    if (!timerInterval) {
      timerInterval = setInterval(updateTimer, 1000)
    }
  } else {
    if (timerInterval) {
      clearInterval(timerInterval)
      timerInterval = null
    }
  }
}, { immediate: true })

onMounted(() => {
  if (competitionActive.value) {
    updateTimer()
    timerInterval = setInterval(updateTimer, 1000)
  }
})

onUnmounted(() => {
  if (timerInterval) {
    clearInterval(timerInterval)
  }
})
</script>

<style scoped>
.points-display-settings {
  min-width: 400px;
  max-width: 500px;
}
</style>
